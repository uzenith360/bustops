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
            ], ['pictures[]' => $_FILES['pictures']], ['pictures']);



    if (empty($validationResult)) {
        //FIRST UPLOAD FILE BEFORE SUBMITTING TO DATA MONGO
        $cleanedUserInputMap = array_map(function($value) {
            return htmlspecialchars(strip_tags(trim(isset($_POST[$value]) ? $_POST[$value] : '')));
        }, ['name' => 'name', 'type' => 'type', 'description' => 'description', 'extra_info' => 'extra_info', 'admin_id' => 'admin_id', 'lat' => 'lat', 'lng' => 'lng']);
        $validationResult = $form_validate([
            'admin_id' => 'required',
            'name' => 'required',
            'type' => 'required',
            'description' => 'required',
            'lat' => 'required|double',
            'lng' => 'required|double'
                //'extra_info' => ''
                ], $cleanedUserInputMap);

        if (empty($validationResult)) {
            require_once 'php/mime_content_type.php';

            $cleanedUserInputMap['latlng'] = ['lat' => $cleanedUserInputMap['lat'], 'lng' => $cleanedUserInputMap['lng']];
            unset($cleanedUserInputMap['lat']);
            unset($cleanedUserInputMap['lng']);

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
                require_once 'php/mongodb_collection-locations.php';
                //submit to db
                try {
                    if (!($collection->insert(array_merge(['pictures' => $pictures], $cleanedUserInputMap))) . ok) {
                        $response ['err'] = ['error' => 'DB', 'msg' => ['message' => 'An error occurred, please retry']];
                    }
                } catch (Exception $e) {
                    //Catch all exceptions
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