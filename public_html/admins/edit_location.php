<?php

/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */
//get admin_id from session
$admin_id = 2;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../php/form_validate.php';
    require_once '../php/form_validate_multiple.php';

    $response = ['err' => null, 'result' => null];
    $thrsUpload = isset($_FILES['pictures']['tmp_name'][0]);

    if ($thrsUpload) {
        $validationResult = $form_validate_multiple([
            'pictures[]' => 'filemaxmegabytes:2|filemimetypes:image/jpeg,image/png,image/jpg'
                ], ['pictures[]' => $_FILES['pictures']], ['pictures[]']);
    }

    if (!$thrsUpload || empty($validationResult)) {
        //FIRST UPLOAD FILE BEFORE SUBMITTING TO DATA MONGO
        $cleanedUserInputMap = array_filter(array_map(function($value) {
                    return htmlspecialchars(strip_tags(trim(isset($_POST[$value]) ? $_POST[$value] : '')));
                }, ['type' => 'type', 'description' => 'description']));
        isset($_POST['names']) && is_array($_POST['names']) && $cleanedUserInputMap['names'] = array_filter(array_map(function($name) {
                    return ucwords(htmlspecialchars(strip_tags(trim($name))));
                }, $_POST['names']));
        isset($_POST['addresses']) && is_array($_POST['addresses']) && $cleanedUserInputMap['addresses'] = array_filter(array_map(function($name) {
                    return ucwords(htmlspecialchars(strip_tags(trim($name))));
                }, $_POST['addresses']));

        $validationResult = $form_validate([
            'names' => 'array|arrayminlength:1', //array now
            // 'type' => 'required',
            'addresses' => 'array|arrayminlength:1', //array now
                //'description' => ''
                ], $cleanedUserInputMap);

        if (empty($validationResult)) {
            require_once '../php/mime_content_type.php';

            isset($cleanedUserInputMap['type']) && $cleanedUserInputMap['type'] = strtoupper($cleanedUserInputMap['type']);
            isset($cleanedUserInputMap['description']) && $cleanedUserInputMap['description'] = ucwords($cleanedUserInputMap['description']);

            $fileError = false;
            $pictures = [];
            if ($thrsUpload) {
                //try to save files
                $fileBatch = dechex(mt_rand(0, 1000));
                $dir = '../img/l/';
                $fullDir = $dir . $fileBatch . '/';

                if (file_exists($fullDir) || mkdir($fullDir)) {
                    $fileName = $admin_id . date('YmdHis');
                    $mimeTransTbl = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/jpg' => 'jpg'];
                    for ($idx = 0, $location_picturesTMP = $_FILES['pictures']['tmp_name'], $location_pictureTMP, $location_picturesLength = count($location_picturesTMP); $idx < $location_picturesLength; ++$idx) {
                        $location_pictureTMP = $location_picturesTMP[$idx];
                        if (!move_uploaded_file($location_pictureTMP, $dir . ($pictures[] = $fileBatch . '/' . $fileName . $idx . '.' . $mimeTransTbl[mime_content_type($location_pictureTMP)]))) {
                            $fileError = true;
                            break;
                        }
                    }
                    
                    count($pictures) && $cleanedUserInputMap['pictures'] = $pictures;
                }
            }

            if (!$fileError) {
                if (count($cleanedUserInputMap)) {
                    require_once '../php/mongodb_update.php';
                    //edit from elastic search too
                    $changes = $cleanedUserInputMap;
                    $locationId = filter_input(INPUT_POST, 'i', FILTER_SANITIZE_STRING);
                    $isBustop = +$_POST['b'];

                    //if a bustop, edit it in neo4j too
                    //neo4j only contains d bustop_id and that is readonly

                    if (mongoDB_update($locationId, 'locations', $changes)) {
                        //dnt forget to edit the bustops collection o
                        $ntErr = true;
                        if (isset($changes['names']) || isset($changes['addresses'])) {
                            require_once '../php/elasticsearch_client.php';

                            $doc = [];
                            isset($changes['names']) && $doc['names'] = $changes['names'];
                            isset($changes['addresses']) && $doc['addresses'] = $changes['addresses'];

                            $elasticresult = $elasticsearchClient->update([
                                'index' => 'bustops',
                                'type' => 'locations',
                                'id' => $locationId,
                                'body' => [
                                    'doc' => $doc
                                ]
                            ]);

                            if (isset($elasticresult['error'])) {
                                //Roll back mongo insert
                                $response['err'] = ['error' => 'DB', 'message' => 'Problem updating location, please try again'];
                                $ntErr = false;
                            }
                        }

                        if ($isBustop && isset($changes['latlng']) && $ntErr) {
                            if (!mongoDB_update($locationId, 'bustops', ['loc.coordinates' => [$changes['latlng']['lng'], $changes['latlng']['lat']]])) {
                                $response['err'] = ['error' => 'DB', 'message' => 'Problem updating location, please try again'];
                            }
                        }
                    } else {
                        $response['err'] = ['error' => 'DB', 'message' => 'Problem updating location, please try again'];
                    }
                } else {
                    $response ['err'] = ['error' => 'NOCHANGES', 'msg' => ['message' => 'No changes']];
                }
            } else {
                $response ['err'] = ['error' => 'FILE', 'msg' => ['message' => 'Problem saving file(s)', 'index' => $idx]];
            }
        } else {
            $response ['err'] = ['error' => 'VALIDATION', 'msg' => $validationResult];
        }
    } else {
        $response ['err'] = ['error' => 'VALIDATION', 'msg' => $validationResult];
    }

    echo json_encode($response);
}