<?php

//deletes route from neo4j only
require_once 'mongodb.php';
require_once 'neo4j_client.php';

function delete_route($routeId) {
    global $mongoDB;
    global $neo4jClient;

    $result = (array) $mongoDB->executeQuery('bustops.routes', new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectID($routeId)], ['projection' => ['_id' => 0, 'type' => 1, 'stops' => 1, 'hub'=>1], 'limit' => 1]))->toArray();

    if (count($result)) {
        $result = $result[0];
        $stops = $result->stops;
        $type = $result->type;
        array_unshift($stops, $result->hub);
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
                    $routes .= ',(s)-[' . $reltag . ':' . $type . ']->(' . $stopTag . ') ';
                    $reltags .= ($reltags ? ',' : '') . $reltag;
                    ++$relCt;

                    if (!($i0 % 5)) {
                        $tx->run($activeStopMatch . $matches . $routes . 'DELETE ' . $reltags);
                        $routes = $matches = $reltags = '';
                    }
                }
                $routes && $tx->run($activeStopMatch . $matches . $routes . 'DELETE ' . $reltags);
            }

            //the calling code would make the commit whenever it wants to
            // $tx->commit();

            ini_set('max_execution_time', $max_execution_time);
            return [0, $tx];
        } catch (GraphAware\Neo4j\Client\Exception\Neo4jException $e) {
            ini_set('max_execution_time', $max_execution_time);
            return [2, null];
        }
    } else {
        return [1, null];
    }
}
