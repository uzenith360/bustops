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

    $response = ['err' => null, 'result' => null];
    $neoNtErr = true;
    $tx;
    if ($isBustop) {
        require_once '../neo4j_client.php';

        try {
            $tx = $neo4jClient->transaction();

            if (!$tx->run('MATCH (n:{i:' . $locationId . '}) DETACH DELETE n')->summarize()->updateStatistics()->containsUpdates()) {
                $neoNtErr = false;
            }
        } catch (GraphAware\Neo4j\Client\Exception\Neo4jException $e) {
            $neoNtErr = false;
        }
    }

    if ($neoNtErr) {
        if (mongoDB_delete($locationId, 'location')) {
            if ($isBustop) {
                if(mongoDB_delete($locationId, 'bustops')){
                    $tx->commit();
                }else{
                    $response['err'] = ['error' => 'DB', 'Problem deleting location, please try again'];
                }                
            }
        } else {
            $response['err'] = ['error' => 'DB', 'Problem deleting location, please try again'];
        }
    } else {
        $response['err'] = ['error' => 'DB', 'Problem deleting location, please try again'];
    }

    echo json_encode($response);
}