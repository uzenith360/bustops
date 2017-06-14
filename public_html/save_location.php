<?php

/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'php/form_validate.php';
    require_once 'php/form_validate_multiple.php';

    $response = ['err' => null, 'result' => null];

    $validationResult = $form_validate_multiple([
        'pictures[]' => 'filerequired|filemaxmegabytes:2|filemimetypes:image/jpeg,image/png,image/jpg'
            ], ['pictures[]' => $_FILES['pictures']], ['pictures[]']);



    if (empty($validationResult)) {
        //FIRST UPLOAD FILE BEFORE SUBMITTING TO DATA MONGO
        $cleanedUserInputMap = array_map(function($value) {
            return htmlspecialchars(strip_tags(trim(isset($_POST[$value]) ? $_POST[$value] : '')));
        }, ['type' => 'type', 'description' => 'description', 'admin_id' => 'admin_id', 'lat' => 'lat', 'lng' => 'lng']);
        $cleanedUserInputMap['names'] = is_array($_POST['names']) ? $_POST['names'] : [];
        $cleanedUserInputMap['addresses'] = is_array($_POST['addresses']) ? $_POST['addresses'] : [];

        $validationResult = $form_validate([
            'admin_id' => 'required',
            'names' => 'required|array|arrayminlength:1', //array now
            'type' => 'required',
            'addresses' => 'required|array|arrayminlength:1', //array now
            'lat' => 'required|double',
            'lng' => 'required|double'
                //'description' => ''
                ], $cleanedUserInputMap);

        if (empty($validationResult)) {
            require_once 'php/mime_content_type.php';

            $cleanedUserInputMap['latlng'] = ['lat' => doubleval($cleanedUserInputMap['lat']), 'lng' => doubleval($cleanedUserInputMap['lng'])];
            unset($cleanedUserInputMap['lat']);
            unset($cleanedUserInputMap['lng']);
            $cleanedUserInputMap['type'] = strtoupper($cleanedUserInputMap['type']);
            $cleanedUserInputMap['names'] = array_map(function($name) {
                return ucwords($name);
            }, $cleanedUserInputMap['names']);
            $cleanedUserInputMap['addresses'] = array_map(function($address) {
                return ucwords($address);
            }, $cleanedUserInputMap['addresses']);
            isset($cleanedUserInputMap['description']) && $cleanedUserInputMap['description'] = ucwords($cleanedUserInputMap['description']);

            //try to save files
            $dir = 'img/l/' . dechex(mt_rand(0, 1000)) . '/';

            if (file_exists($dir) || mkdir($dir)) {
                $fileName = $cleanedUserInputMap['admin_id'] . date('YmdHis');
                $mimeTransTbl = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/jpg' => 'jpg'];
                $fileError = false;
                $pictures = [];
                for ($idx = 0, $location_picturesTMP = $_FILES['pictures']['tmp_name'], $location_pictureTMP, $location_picturesLength = count($location_picturesTMP); $idx < $location_picturesLength; ++$idx) {
                    $location_pictureTMP = $location_picturesTMP[$idx];
                    if (!move_uploaded_file($location_pictureTMP, $dir . ($pictures[] = $fileName . $idx . '.' . $mimeTransTbl[mime_content_type($location_pictureTMP)]))) {
                        $fileError = true;
                        break;
                    }
                }
            }

            if (!$fileError) {
                require_once 'php/save_data.php';
                if (($response['result'] = saveData(array_merge(['pictures' => $pictures], $cleanedUserInputMap), ['names' => $cleanedUserInputMap['names'], 'addresses' => $cleanedUserInputMap['addresses']], 'locations'))) {
                    if ($cleanedUserInputMap['type'] === 'BUSTOP') {
                        require_once 'php/mongodb_insert.php';
                        //we dnt need names in the route collection
                        if (!mongoDB_insert(['_id' => new MongoDB\BSON\ObjectID($response['result'])/*, 'names' => $cleanedUserInputMap['names']*/, 'loc' => ['type' => 'Point', 'coordinates' => [$cleanedUserInputMap['latlng']['lng'], $cleanedUserInputMap['latlng']['lat']]]], 'bustops')) {
                            require_once 'mongodb_delete.php';
                            mongoDB_delete($response['result'], 'bustops');

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