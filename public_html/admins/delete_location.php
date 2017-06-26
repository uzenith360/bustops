<?php

//secure this file with the utmost security
//if its a bustop, detach/delete it in neo4j too
//detach delete the node and delete from locations collection
exit('');
/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */
$locationId = filter_input(INPUT_POST, 'i', FILTER_SANITIZE_STRING);
$isBustop = boolval($_POST['b']);

if ($locationId) {
    require_once '../mongodb_delete.php';

    $response = ['err'=>null, 'result'=>null];
    $neoNtErr = true;
    if ($isBustop) {
        require_once '../neo4j_client.php';

        try {
            if (!$neo4jClient->run('DELETE ({i:' . $locationId . '})')->summarize()->updateStatistics()->containsUpdates()) {
                $neoNtErr = false;
            }
        } catch (GraphAware\Neo4j\Client\Exception\Neo4jException $e) {
            $neoNtErr = false;
        }
    }
    
    if($neoNtErr){
        if(!mongoDB_delete($routeId, 'location')){
            $response['err'] = ['error'=>'DB', 'Problem deleting location, please try again'];
        }
    }else{
        $response['err'] = ['error'=>'DB', 'Problem deleting location, please try again'];
    }
    
    echo json_encode($response);
}