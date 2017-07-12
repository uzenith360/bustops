<?php

require_once 'neo4j_client.php';

//Create new bustops if they dnt already exists
//Map the bustops to other bustops that interlink
//Save in mongodb too, incase thrs a problem and we'll need to rebuild the graph
function map_routes($id, $routeInfo) {
    global $neo4jClient;

    $timecreated = (new DateTime())->format(DateTime::ISO8601);
    $transportType = $routeInfo['type'];
    $transportStops = $routeInfo['stops'];
    $transportFares = $routeInfo['fares'];
    $startTime = isset($routeInfo['startTime']) ? $routeInfo['startTime'] : null;
    $closeTime = isset($routeInfo['closeTime']) ? $routeInfo['closeTime'] : null;

    array_unshift($transportStops, $routeInfo['hub']);

    $max_execution_time = ini_get('max_execution_time');
    try {
        ini_set('max_execution_time', 180);

        $tx = $neo4jClient->transaction();

        $stopsLength = count($transportStops);

        //get all the stops and create them
        for ($i = 0, $stops = ''; $i < $stopsLength; ++$i) {
            $stoptag = 's' . $i;
            $stops .= 'MERGE (' . $stoptag . ':BUSTOP{i: "' . $transportStops[$i] . '"}) ON CREATE SET ' . $stoptag . '.c="' . $timecreated . '" ';

            if (!($i % 10)) {
                $tx->run($stops);
                $stops = '';
            }
        }
        $stops && $tx->run($stops);

        for ($i = 0, $routes = '', $matches = ''; $i < $stopsLength; ++$i) {
            $activeStopMatch = 'MATCH (s:BUSTOP{i: "' . $transportStops[$i] . '"})';
            for ($i0 = $i + 1, $i1 = $i, $routes = '', $matches = '', $relCt = 0; $i0 < $stopsLength; ++$i0, ++$i1) {
                $reltag = 'r' . $relCt;
                $stopTag = 's' . $relCt;
                $matches .= ',(' . $stopTag . ':BUSTOP{i: "' . $transportStops[$i0] . '"})';
                $routes .= 'MERGE (s)-[' . $reltag . ':' . $transportType . ']->(' . $stopTag . ') SET ' . $reltag . '.f=' . $transportFares[$i1] . ',' . $reltag . '.m="' . $timecreated . '"' . ($startTime ? ',' . $reltag . '.s="' . $startTime . '"' : '') . ($closeTime ? ',' . $reltag . '.e="' . $closeTime . '"' : '') . ',' . $reltag . '.i="' . $id . '" ';
                ++$relCt;

                if (!($i0 % 5)) {
                    $tx->run($activeStopMatch . $matches . $routes);
                    $routes = $matches = '';
                }
            }
            $routes && $tx->run($activeStopMatch . $matches . $routes);
        }

        //we'll allow the calling code to commit this whenever it feels d whole operation was successful
        //$tx->commit();

        ini_set('max_execution_time', $max_execution_time);
        return [true, $tx];
    } catch (GraphAware\Neo4j\Client\Exception\Neo4jException $e) {
        ini_set('max_execution_time', $max_execution_time);
        return [false, null];
    }
}
