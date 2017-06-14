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

if (($startLat = doubleval($_GET['start']['lat'])) && ($startLng = doubleval($_GET['start']['lng'])) && ($endLat = doubleval($_GET['end']['lat'])) && ($endLng = doubleval($_GET['end']['lng']))) {
    require_once 'php/mongodb.php';
    require_once 'php/neo4j_client.php';

    $nearBustopsLimit = 10;
    $startNearBustops = [];
    $endNearBustops = [];
    $route;
    //maybe we'll have multiple routes suggestions later
    //by the we'll use allShortestPaths and getRecords()
    $routes = [];

    //get points close to start
    foreach ($mongoDB->executeQuery('bustops.bustops', new MongoDB\Driver\Query(['loc' => ['$near' => ['$geometry' => ['type' => 'Point', 'coordinates' => [$startLng, $startLat]], '$maxDistance' => 5000]]], ['limit' => $nearBustopsLimit, 'projection' => ['loc' => 0]])) as $r) {
        $startNearBustops[] = $r;
    }


    //get points close to end
    foreach ($mongoDB->executeQuery('bustops.bustops', new MongoDB\Driver\Query(['loc' => ['$near' => ['$geometry' => ['type' => 'Point', 'coordinates' => [$endLng, $endLat]], '$maxDistance' => 5000]]], ['limit' => $nearBustopsLimit, 'projection' => ['loc' => 0]])) as $r) {
        $endNearBustops[] = $r;
    }

    foreach ($startNearBustops as $startNearBustop) {
        foreach ($endNearBustops as $endNearBustop) {
            if (($route = $neo4jClient->run('MATCH (a { i: "' . $startNearBustop->_id . '" }),(b { i: "' . $endNearBustop->_id . '" }), p = shortestPath((a)-[*]->(b)) RETURN p')->firstRecord())) {
                $nodes = [];
                $relationships = [];
                $value;

                foreach ($route->value('p')->nodes() as $node) {
                    //get the details of the nodes
                    $nodes[] = array_merge($value = $node->values(), (array)$mongoDB->executeQuery('bustops.locations', new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectID($value['i'])], ['projection' => ['_id' => 0]]))->toArray()[0]);
                }
                foreach ($route->value('p')->relationships() as $relationship) {
                    $relationships[] = array_merge(['t' => $relationship->type()], $relationship->values());
                }

                echo json_encode(['n' => $nodes, 'r' => $relationships]);
                return;
            }

            //use $result->getRecord(); to get the first one
        }
    }

    //no route found
    echo '{}';

    //we could save best routes routes for commonly searched routes or think of a way to make this routing algorithm better
    //try route matching
    //start from nearest start route, and nearest end route and keep iterating until u find a route, then return the route to the user
}