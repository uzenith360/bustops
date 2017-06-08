<?php

require_once 'neo4j_client.php';

//Create new bustops if they dnt already exists
//Map the bustops to other bustops that interlink

//Save in mongodb too, incase thrs a problem and we'll need to rebuild the graph
function map_routes($routeInfo){
    global $neo4jClient;
    
    //if (a)-[r:{type:'Type', fare:50}]->(b) //do nothing, //if rel exists bt nt equal to that, update rel, if rel doesnt exist, onCreate create REL, 
    //Use merge, because d route might nt actually exist
}