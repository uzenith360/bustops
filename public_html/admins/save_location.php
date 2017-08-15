<?php

/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */
//get admin_id from session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../php/form_validate.php';
    require_once '../php/form_validate_multiple.php';
    ini_set('precision', '17');

    $response = ['err' => null, 'result' => null];
    $thrsUpload = isset($_FILES['pictures']['tmp_name'][0]) && $_FILES['pictures']['tmp_name'][0];

    if ($thrsUpload) {
        $validationResult = $form_validate_multiple([
            'pictures[]' => 'filemaxmegabytes:2|filemimetypes:image/jpeg,image/png,image/jpg'
                ], ['pictures[]' => $_FILES['pictures']], ['pictures[]']);
    }

    if (!$thrsUpload || empty($validationResult)) {
        //FIRST UPLOAD FILE BEFORE SUBMITTING TO DATA MONGO
        $cleanedUserInputMap = array_map(function($value) {
            return htmlspecialchars(strip_tags(trim(isset($_POST[$value]) ? $_POST[$value] : '')));
        }, ['type' => 'type', 'description' => 'description', 'admin_id' => 'admin_id', 'lat' => 'lat', 'lng' => 'lng']);
        $cleanedUserInputMap['names'] = array_filter(array_map(function($name) {
                    return ucwords(htmlspecialchars(strip_tags(trim($name))));
                }, isset($_POST['names']) && is_array($_POST['names']) ? $_POST['names'] : []));
        $cleanedUserInputMap['addresses'] = array_filter(array_map(function($name) {
                    return ucwords(htmlspecialchars(strip_tags(trim($name))));
                }, isset($_POST['addresses']) && is_array($_POST['addresses']) ? $_POST['addresses'] : []));

        $validationResult = $form_validate([
            'admin_id' => 'required',
            'names' => 'required|array|arrayminlength:1', //array now
            'type' => 'required',
            //'addresses' => 'required|array|arrayminlength:1', //array now
            'lat' => 'required|double',
            'lng' => 'required|double'
                //'description' => ''
                ], $cleanedUserInputMap);

        if (empty($validationResult)) {
            require_once '../php/mime_content_type.php';

            $cleanedUserInputMap['latlng'] = ['lat' => doubleval($cleanedUserInputMap['lat']), 'lng' => doubleval($cleanedUserInputMap['lng'])];
            unset($cleanedUserInputMap['lat']);
            unset($cleanedUserInputMap['lng']);
            $cleanedUserInputMap['type'] = strtoupper($cleanedUserInputMap['type']);

            isset($cleanedUserInputMap['description']) && $cleanedUserInputMap['description'] = ucwords($cleanedUserInputMap['description']);

            $fileError = false;
            $pictures = [];
            if ($thrsUpload) {
                //try to save files
                $fileBatch = dechex(mt_rand(0, 1000));
                $dir = '../img/l/';
                $fullDir = $dir . $fileBatch . '/';

                if (file_exists($fullDir) || mkdir($fullDir)) {
                    $fileName = $cleanedUserInputMap['admin_id'] . date('YmdHis');
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
                require_once '../php/save_data.php';
                if (($response['result'] = saveData($cleanedUserInputMap, ['names' => $cleanedUserInputMap['names'], 'addresses' => $cleanedUserInputMap['addresses']], 'locations'))) {
                    if ($cleanedUserInputMap['type'] === 'BUSTOP') {
                        require_once '../php/mongodb_insert.php';
                        require_once '../php/neo4j_client.php';

                        //create it in neo4j
                        try {
                            ini_set('max_execution_time', 180);
                            $tx = $neo4jClient->transaction();

                            if ($tx->run('CREATE (n:BUSTOP{i: "' . $response['result'] . '"}) SET n.c="' . (new DateTime())->format(DateTime::ISO8601).'"')->summarize()->updateStatistics()->containsUpdates()) {
                                //we dnt need names in the route collection
                                if (mongoDB_insert(['_id' => new MongoDB\BSON\ObjectID($response['result'])/* , 'names' => $cleanedUserInputMap['names'] */, 'loc' => ['type' => 'Point', 'coordinates' => [$cleanedUserInputMap['latlng']['lng'], $cleanedUserInputMap['latlng']['lat']]]], 'bustops')) {
                                    $tx->commit();
                                } else {
                                    require_once '../php/mongodb_delete.php';
                                    mongoDB_delete($response['result'], 'bustops');

                                    throw new GraphAware\Neo4j\Client\Exception\Neo4jException('DB error');
                                }
                            } else {
                                throw new GraphAware\Neo4j\Client\Exception\Neo4jException('No updates');
                            }
                        } catch (GraphAware\Neo4j\Client\Exception\Neo4jException $e) {
                            $response['result'] = null;
                            $response ['err'] = ['error' => 'DB', 'msg' => ['message' => 'An error occurred, please retry']];
                        }
                    }
                } else {
                    $response ['err'] = ['error' => 'DB', 'msg' => ['message' => 'An error occurred, please retry']];
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
?>