<?php

/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo print_r($_FILES);
    require_once 'php/form_validate.php';
    require_once 'php/form_validate_multiple.php';

    $response = ['err' => null, 'result' => null];

    $form_validate_multiple([
        'location_pictures' => 'filerequired|filemaxmegabytes:2|filemimetypes:image/jpeg,image/png,image/jpg'
            ], ['location_pictures' => $_FILES['location_pictures']], ['location_pictures']);

    //FIRST UPLOAD FILE BEFORE SUBMITTING TO DATA MONGO
    /*$validationResult = $form_validate([
        'location_pictures[]' => 'filemaxmegabytes:2|filemimetypes:image/jpeg,image/png,image/jpg'
            ], ['location_pictures[]' => $_FILES['location_pictures']]);
*/

    $cleanedUserInputMap = array_map(function($value) {
        return htmlspecialchars(strip_tags(trim(isset($_POST[$value]) ? $_POST[$value] : '')));
    }, ['name' => 'name', 'type' => 'type', 'description' => 'description', 'extra_info' => 'extra_info']);
}
?>