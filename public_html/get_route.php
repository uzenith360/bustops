<?php

/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */

//get start coords
//find closest bustop to start coords
//get end coords
//find closest bustops close to end coords
//get the routes connecting these from neo4j
//do a provision for stuffs like take bike to the bustop or trek 2mins left to the bustop or take taxi e.t.c, for proximity transport
//maybe later u can find like 3 close bustops to start and end coords, then map them to provide alternative routes

if (($startLat = doubleval($_GET['start']['lat'])) && ($startLng = doubleval($_GET['start']['lng'])) && ($endLat = doubleval($_GET['end']['lat'])) && ($endLng = doubleval($_GET['end']['lng'])) && !($startLat === $endLat && $startLng === $endLng)) {
    require_once 'php/mongodb.php';
    require_once 'php/neo4j_client.php';

    function haversineGreatCircleDistance(
    $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    $mode = filter_input(INPUT_GET, 'mode', FILTER_SANITIZE_STRING);

    $nearBustopsLimit = 25;
    $startNearBustops = [];
    $endNearBustops = [];
    $route;
    //maybe we'll have multiple routes suggestions later
    //by the we'll use allShortestPaths and getRecords()
    $selectedRoutes = [];

    //remove this wen u start displaying for all possible routes to users
    //we'll search for d shortest route and stop wen we gt a route with 1 relationship
    //Maybe later, improve this to search for the shortest and least expensive(fares) route, or maybe u should jst implement that now :-)
    $bestRoute = null;
    $bestRouteLength = null;
    $startToBustopDists = [];
    $bustopToDestDists = [];
    $bestRouteStartToBustopDistKey;
    $bestRouteBustopToDestDistKey;
    $startCoords;
    $endCoords;

    //get points close to start
    foreach ($mongoDB->executeQuery('bustops.bustops', new MongoDB\Driver\Query(['loc' => ['$near' => ['$geometry' => ['type' => 'Point', 'coordinates' => [$startLng, $startLat]]/* , '$maxDistance' => 5000 */]]], ['limit' => $nearBustopsLimit/* , 'projection' => ['loc' => 0] */])) as $r) {
        //echo $r->_id.'<br/>';
        $startNearBustops[] = $r;
    }


    //get points close to end
    foreach ($mongoDB->executeQuery('bustops.bustops', new MongoDB\Driver\Query(['loc' => ['$near' => ['$geometry' => ['type' => 'Point', 'coordinates' => [$endLng, $endLat]]/* , '$maxDistance' => 5000 */]]], ['limit' => $nearBustopsLimit/* , 'projection' => ['loc' => 0] */])) as $r) {
        $endNearBustops[] = $r;
    }

    foreach ($startNearBustops as $startNearBustop) {
        foreach ($endNearBustops as $endNearBustop) {
            if (!strcmp($startNearBustop->_id, $endNearBustop->_id)) {
                continue;
            }
            //echo $startNearBustop->_id . '<br/>';
            if (($route = $neo4jClient->run('MATCH (a:BUSTOP{ i: "' . $startNearBustop->_id . '" }),(b:BUSTOP{ i: "' . $endNearBustop->_id . '" }), p = shortestPath((a)-[' . ($mode ? ':' . $mode : '*') . ']->(b)) RETURN p')->records())) {
                $route = $route[0];
                $relCt;
                $routeStartToBustopDistKey;
                $routeBustopToDestDistKey;
                //$rels;
                //$ct;
                //$tFares = 0;

                /* $ct = */$relCt = count(/* $rels = */$route->value('p')->relationships());

                //echo $relCt.' - '.$bestRouteLength.'<br/>';
                if ($bestRouteLength) {
                    if ($relCt < $bestRouteLength) {
                        /* while ($ct--) {//echo $ct.'<br/>';
                          $tFares+=$rels[$ct]->f;
                          }//echo $tFares.'<br/>'; */

                        $bestRouteLength = $relCt;
                        $bestRoute = $route;
                        $startCoords = $startNearBustop->loc->coordinates;
                        !isset($startToBustopDists[$bestRouteStartToBustopDistKey = (string) $startNearBustop->_id]) && $startToBustopDists[(string) $startNearBustop->_id] = (int)haversineGreatCircleDistance($startLat, $startLng, $startCoords[1], $startCoords[0]);
                        $endCoords = $endNearBustop->loc->coordinates;
                        !isset($bustopToDestDists[$bestRouteBustopToDestDistKey = (string) $endNearBustop->_id]) && $bustopToDestDists[(string) $endNearBustop->_id] = (int)haversineGreatCircleDistance($endCoords[1], $endCoords[0], $endLat, $endLng);
                    } else if ($relCt === $bestRouteLength) {//echo $bestRouteLength.'<br/>';
                        $startCoords = $startNearBustop->loc->coordinates;
                        !isset($startToBustopDists[$routeStartToBustopDistKey = (string) $startNearBustop->_id]) && $startToBustopDists[(string) $startNearBustop->_id] = (int)haversineGreatCircleDistance($startLat, $startLng, $startCoords[1], $startCoords[0]);
                        $endCoords = $endNearBustop->loc->coordinates;
                        !isset($bustopToDestDists[$routeBustopToDestDistKey = (string) $endNearBustop->_id]) && $bustopToDestDists[(string) $endNearBustop->_id] = (int)haversineGreatCircleDistance($endCoords[1], $endCoords[0], $endLat, $endLng);

                        if ($startToBustopDists[$routeStartToBustopDistKey] < $startToBustopDists[$bestRouteStartToBustopDistKey] || $bustopToDestDists[$routeBustopToDestDistKey] < $bustopToDestDists[$bestRouteBustopToDestDistKey]) {
                            //echo $startToBustopDists[$bestRouteStartToBustopDistKey].' - '.$startToBustopDists[$routeStartToBustopDistKey].' + '.$bustopToDestDists[$bestRouteBustopToDestDistKey] .' - '.$bustopToDestDists[$routeBustopToDestDistKey].'<br/>';
                            $bestRouteStartToBustopDistKey = $routeStartToBustopDistKey;
                            $bestRouteBustopToDestDistKey = $routeBustopToDestDistKey;
                            $bestRouteLength = $relCt;
                            $bestRoute = $route;
                        }
                    }
                } else {
                    $bestRouteLength = $relCt;
                    $bestRoute = $route;
                    $startCoords = $startNearBustop->loc->coordinates;
                    $startToBustopDists[$bestRouteStartToBustopDistKey = (string) $startNearBustop->_id] = (int)haversineGreatCircleDistance($startLat, $startLng, $startCoords[1], $startCoords[0]);
                    $endCoords = $endNearBustop->loc->coordinates;
                    $bustopToDestDists[$bestRouteBustopToDestDistKey = (string) $endNearBustop->_id] = (int)haversineGreatCircleDistance($endCoords[1], $endCoords[0], $endLat, $endLng);
                }
            }

            //use $result->getRecord(); to get the first one
        }
    }
//echo print_r($startToBustopDists).'<br/>';
//echo print_r($bustopToDestDists).'<br/>';
    if ($bestRoute) {
        $nodes = [];
        $relationships = [];
        foreach ($bestRoute->value('p')->nodes() as $node) {
            //get the details of the nodes
            $nodes[] = (array) $mongoDB->executeQuery('bustops.locations', new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectID($node->values()['i'])], ['projection' => ['_id' => 0]]))->toArray()[0];
        }
        foreach ($bestRoute->value('p')->relationships() as $relationship) {
            $relationshipValues = $relationship->values();
            $relationships[] = array_merge(['t' => $relationship->type()], $relationshipValues, (array) $mongoDB->executeQuery('bustops.routes', new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectID($relationshipValues['i'])], ['projection' => ['_id' => 0, 'destinations' => 1]]))->toArray()[0]);
        }

        echo json_encode(['n' => $nodes, 'r' => $relationships]);
    } else {
        //no route found
        echo '{}';
    }

    //we could save best routes routes for commonly searched routes or think of a way to make this routing algorithm better
    //try route matching
    //start from nearest start route, and nearest end route and keep iterating until u find a route, then return the route to the user
}