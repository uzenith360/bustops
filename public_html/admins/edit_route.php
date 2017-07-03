<?php

//we'll put edited time for the routes any info that was edited
//wht can be edit in a route
// - the type of route i'e danfo route
// - destinations
// - stops and fares
// - route hub
// - route start
// - close times

$routeId = filter_input(INPUT_POST, 'i', FILTER_SANITIZE_STRING);
$changes = isset($_POST['c']) && is_array($_POST['c']) && count($_POST['c']) ? $_POST['c'] : null;

if ($routeId && $changes && (isset($changes['stops']) ? isset($changes['type']) && isset($changes['hub']) : true)) {
    require_once '../php/mongodb_update.php';

    $response = ['err' => null, 'result' => ''];

    $nterr = true;
    $res;
    $res0;
    if (isset($changes['stops'])) {
        require_once '../php/delete_route.php';
        require_once '../php/map_routes.php';

        $res = delete_route($routeId);
        switch ($res[0]) {
            case 0:
                //success
                //delete 
                //the outcome of the delete operation shouldnt affect the overall results of the script operation
                $res[1]->commit();
                $res0 = map_routes($routeId, $changes);
                if ($res0[0]) {
                    
                } else {
                    //db error
                    $nterr = false;
                    $response['err'] = ['error' => 'DB', 'message' => 'Problem editing route, please try again'];
                }
                break;
            case 1:
                //route nt found
                $nterr = false;
                $response['err'] = ['error' => 'NOTFOUND', 'message' => 'Route not found'];
                break;
            case 2:
            default:
                //db error
                $nterr = false;
                $response['err'] = ['error' => 'DB', 'message' => 'Problem editing route, please try again'];
                break;
        }
    }

    if ($nterr) {
        if (mongoDB_update($routeId, 'routes', $changes)) {
            if (isset($changes['stops'])) {
                $res0[1]->commit();
            }
            $response['result'] = true;
        } else {
            //db error
            $response['err'] = ['error' => 'DB', 'message' => 'Problem editing route, please try again'];
        }
    }

    echo json_encode($response);
}