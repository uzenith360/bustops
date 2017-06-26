<?php

//hmmmmmmm
//guard this file with utmost extreme care
//disable this file, and only enable it wen someone says that he made a mistake and want to delete a route
//maybe delete the route urself and disable the file after the deletion has been made
exit('');
/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */
$routeId = filter_input(INPUT_POST, 'i', FILTER_SANITIZE_STRING);
if ($routeId) {
    require_once '../mongodb.php';
    require_once '../neo4j_client.php';
    require_once '../mongodb_delete.php';

    $response = ['err' => null, 'result' => ''];

    $result = (array) $mongoDB->executeQuery('bustops.route', new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectID($routeId)], ['projection' => ['_id' => 0, 'type' => 1, 'stops' => 1], 'limit' => 1]))->toArray()[0];

    if (count($result)) {
        $stops = $result['stops'];
        $type = $result['type'];
        $max_execution_time = ini_get('max_execution_time');
        try {
            ini_set('max_execution_time', 180);

            $tx = $neo4jClient->transaction();
//dnt delete or edit relationships by its id, use d nodes its connected to, relationship id's dnt have indexes so if u use d id u're in for a full database scan (and it'll be slower)
            for ($i = 0, $stopsLength = count($stops), $routes = '', $matches = '', $reltags = ''; $i < $stopsLength; ++$i) {
                $activeStopMatch = 'MATCH (s:BUSTOP{i: "' . $stops[$i] . '"})';
                for ($i0 = $i + 1, $routes = '', $matches = '', $reltags = '', $relCt = 0; $i0 < $stopsLength; ++$i0) {
                    $reltag = 'r' . $relCt;
                    $stopTag = 's' . $relCt;
                    $matches .= ',(' . $stopTag . ':BUSTOP{i: "' . $stops[$i0] . '"})';
                    $routes .= 'MATCH (s)-[' . $reltag . ':' . $type . ']->(' . $stopTag . ') SET ' . $reltag . '.f=' . $transportFares[$i] . ',' . $reltag . '.m="' . $timecreated . '",' . $reltag . '.s="' . $startTime . '",' . $reltag . '.e="' . $endTime . '",' . $reltag . '.i="' . $id . '" ';
                    $reltags .= ($reltags ? ',' : '') . $reltag;
                    ++$relCt;

                    if (!($i0 % 5)) {
                        $tx->run($activeStopMatch . $matches . $routes . 'DELETE ' . $reltags);
                        $routes = $matches = $reltags = '';
                    }
                }
                $routes && $tx->run($activeStopMatch . $matches . $routes . 'DELETE ' . $reltags);
            }

            $tx->commit();

            //delete 
            //the outcome of the delete operation shouldnt affect the overall results of the script operation
            mongoDB_delete($routeId, 'routes');

            ini_set('max_execution_time', $max_execution_time);
            $response['result'] = true;
        } catch (GraphAware\Neo4j\Client\Exception\Neo4jException $e) {
            ini_set('max_execution_time', $max_execution_time);
            $response['err'] = ['error' => 'DB', 'message' => 'Problem deleting the route, please try again'];
        }
    } else {
        $response['err'] = ['error' => 'NOTFOUND', 'message' => 'Route not found'];
    }

    echo json_encode($response);
}