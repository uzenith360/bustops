<?php

$locationId = filter_input(INPUT_POST, 'i', FILTER_SANITIZE_STRING);
$changes = is_array($_POST['c']) ? $_POST['c'] : null;

if ($locationId && $changes) {
    require_once '../mongodb_update.php';

    $response = ['err' => null, 'result' => null];
    
    //if a bustop, edit it in neo4j too
    //neo4j only contains d bustop_id and that is readonly

    if(!mongoDB_update($locationId, 'locations', $changes)){
        $response['err'] = ['error'=>'DB', 'message'=>'Problem updating location, please try again'];
    }
    
    echo json_encode($response);
}

