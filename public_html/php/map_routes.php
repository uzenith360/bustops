<?php

require_once 'neo4j_client.php';

//Create new bustops if they dnt already exists
//Map the bustops to other bustops that interlink
//Save in mongodb too, incase thrs a problem and we'll need to rebuild the graph
function map_routes($routeInfo) {
    global $neo4jClient;

    $transportType = $routeInfo['type'];
    $transportHub = $routeInfo['hub'];
    $query = '';

    foreach ($routeInfo['stops'] as $stop) {$routeInfo['fares'];
        $query .= 'MERGE (A:City {id: 1})
                   MERGE (B:City {id: 2})
                   MERGE (A)-[r:next]->(B)
                   ON CREATE SET r.duration = newDuration
                   ON MATCH  SET r.duration = CASE 
                                 WHEN r.duration > newDuration 
                                 THEN newDuration 
                                 ELSE r.duration 
                   END';
    }

    return $neo4jClient->run($query)->containsUpdates();

//CREATE INDEX based on node id
//Create an index on all nodes with a particular label and property.
    //if (a)-[r:{type:'Type', fare:50}]->(b) //do nothing, //if rel exists bt nt equal to that, update rel, if rel doesnt exist, onCreate create REL, 
    //Use merge, because d route might nt actually exist
}
