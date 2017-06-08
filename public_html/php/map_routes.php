<?php

require_once 'neo4j_client.php';

//Create new bustops if they dnt already exists
//Map the bustops to other bustops that interlink

//Save in mongodb too, incase thrs a problem and we'll need to rebuild the graph
function map_routes($routeInfo){
    global $neo4jClient;
    
    
}