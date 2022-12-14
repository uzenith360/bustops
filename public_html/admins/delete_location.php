<?php

//secure this file with the utmost security
//if its a bustop, detach/delete it in neo4j too
//detach delete the node and delete from locations collection
//exit('');
/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */
$locationId = filter_input(INPUT_POST, 'i', FILTER_SANITIZE_STRING);
$isBustop = +$_POST['b'];
//delete from elastic search too
if ($locationId) {
    require_once '../php/mongodb_delete.php';

    $response = ['err' => null, 'result' => null];
    $ntErr = true;
    $tx;
    if ($isBustop) {
        require_once '../php/neo4j_client.php';

        try {
            $tx = $neo4jClient->transaction();
            ini_set('max_execution_time', 180);
            if (!$tx->run('MATCH (n:BUSTOP{i:"' . $locationId . '"}) DETACH DELETE n')->summarize()->updateStatistics()->containsUpdates()) {
                throw new GraphAware\Neo4j\Client\Exception\Neo4jException('No updates');
            }
        } catch (GraphAware\Neo4j\Client\Exception\Neo4jException $e) {
            $ntErr = false;
        }
    }

    if ($ntErr) {
        if (mongoDB_delete($locationId, 'locations')) {
            require_once '../php/elasticsearch_client.php';

            try {
                $elasticresult = $elasticsearchClient->delete([
                    'index' => 'bustops',
                    'type' => 'locations',
                    'id' => $locationId
                ]);

                if (isset($elasticresult['error'])) {
                    throw new Elasticsearch\Common\Exceptions($elasticresult['error']);
                }
            } catch (Elasticsearch\Common\Exceptions\Missing404Exception $e) {
                //Roll back mongo insert
                $response['err'] = ['error' => 'DB', 'message' => 'Problem deleting location, please try again'];
                $ntErr = false;
            } catch (Elasticsearch\Common\Exceptions $e) {
                $response['err'] = ['error' => 'DB', 'message' => 'Problem deleting location, please try again'];
                $ntErr = false;
            }
            if ($isBustop && $ntErr) {
                require_once '../php/delete_route_stop.php';
                if (mongoDB_delete($locationId, 'bustops') && delete_route_stop($locationId)) {
                    $tx->commit();
                } else {
                    $ntErr = false;
                    $response['err'] = ['error' => 'DB', 'message' => 'Problem deleting location, please try again'];
                }
            }
        } else {
            $response['err'] = ['error' => 'DB', 'message' => 'Problem deleting location, please try again'];
        }
    } else {
        $response['err'] = ['error' => 'DB', 'message' => 'Problem deleting location, please try again'];
    }

    echo json_encode($response);
}