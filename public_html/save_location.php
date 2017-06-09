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
        $cleanedUserInputMap['names'] = isset($_POST['names']) ? $_POST['names'] : [];
        $cleanedUserInputMap['addresses'] = isset($_POST['addresses']) ? $_POST['addresses'] : [];

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
            $cleanedUserInputMap['names'] = array_map(function($name){return strtoupper($name);}, $cleanedUserInputMap['names']);
            $cleanedUserInputMap['addresses'] = array_map(function($address){return strtoupper($address);}, $cleanedUserInputMap['addresses']);

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
               if (!($response['result'] = saveData(array_merge(['pictures' => $pictures], $cleanedUserInputMap), ['names' => $cleanedUserInputMap['names'], 'addresses' => $cleanedUserInputMap['addresses']], 'locations'))) {
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