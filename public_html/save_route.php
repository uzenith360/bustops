<?php

/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'php/form_validate.php';
    require_once 'php/form_validate_multiple.php';

    $response = ['err' => null, 'result' => null];

    $cleanedUserInputMap = array_map(function($value) {
        return htmlspecialchars(strip_tags(trim(isset($_POST[$value]) ? $_POST[$value] : '')));
    }, ['type' => 'type', 'description' => 'description', 'admin_id' => 'admin_id', 'lat' => 'lat', 'lng' => 'lng']);
    
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
        require_once 'mongodb_insert.php';
        require_once 'map_routes.php';

        map_routes($routeInfo);
        mongoDB_Insert($routeInfo, 'routes');
    } else {
        $response ['err'] = ['error' => 'VALIDATION', 'msg' => $validationResult];
    }
    echo json_encode($response);
}
?>