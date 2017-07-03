<?php

//hmmmmmmm
//guard this file with utmost extreme care
//disable this file, and only enable it wen someone says that he made a mistake and want to delete a route
//maybe delete the route urself and disable the file after the deletion has been made
//exit('');
/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */
$routeId = filter_input(INPUT_POST, 'i', FILTER_SANITIZE_STRING);
if ($routeId) {
    require_once '../php/mongodb_delete.php';
    require_once '../php/delete_route.php';

    $response = ['err' => null, 'result' => ''];

    $res = delete_route($routeId);
    switch ($res[0]) {
        case 0:
            //success
            //delete 
            //the outcome of the delete operation shouldnt affect the overall results of the script operation
            mongoDB_delete($routeId, 'routes');
            $res[1]->commit();
            $response['result'] = true;
            break;
        case 1:
            //route nt found
            $response['err'] = ['error' => 'NOTFOUND', 'message' => 'Route not found'];
            break;
        case 2:
            //db error
            $response['err'] = ['error' => 'DB', 'message' => 'Problem deleting the route, please try again'];
            break;
    }

    echo json_encode($response);
}