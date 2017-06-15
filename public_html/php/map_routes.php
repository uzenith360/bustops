<?php

require_once 'neo4j_client.php';

//Create new bustops if they dnt already exists
//Map the bustops to other bustops that interlink
//Save in mongodb too, incase thrs a problem and we'll need to rebuild the graph
function map_routes($routeInfo) {
    //Warning: This function wouldn't work well if the routes stops are more than 255

    global $neo4jClient;

    $timecreated = (new DateTime())->format(DateTime::ISO8601);
    $transportType = $routeInfo['type'];
    $transportStops = $routeInfo['stops'];
    $transportFares = $routeInfo['fares'];
    $startTime = $routeInfo['startTime'];
    $endTime = $routeInfo['endTime'];
    $stops = '';
    $routes = '';

    array_unshift($transportStops, $routeInfo['hub']);
    for ($i = 0, $stopsLength = count($transportStops), $stopsLengthMinus1 = $stopsLength - 1, $relCt = 0; $i < $stopsLength; ++$i) {
        $stoptag = 's' . $i;
        $stops .= 'MERGE (' . $stoptag . '{i: "' . $transportStops[$i] . '"}) ON CREATE SET ' . $stoptag . '.c="' . $timecreated . '" ';
        if ($i < $stopsLengthMinus1) {
            for ($i0 = $i; $i0 < $stopsLengthMinus1; ++$i0) {
                $reltag = 'r' . $relCt;
                $routes .= 'MERGE (' . $stoptag . ')-[' . $reltag . ':' . $transportType . ']->(s' . ($i0 + 1) . ') SET ' . $reltag . '.f=' . $transportFares[$i] . ',' . $reltag . '.m="' . $timecreated. '",' . $reltag . '.s="' . $startTime. '",' . $reltag . '.e="' . $endTime . '" ';
                ++$relCt;
            }
        }
    }

    try {
        $neo4jClient->run($stops . $routes);
        return true;
    } catch (GraphAware\Neo4j\Client\Exception\Neo4jException $e) {
        return false;
    }
}
