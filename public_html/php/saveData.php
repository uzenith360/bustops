<?php

//Saves data to mongodb and elastic search and neo4j if applicable
//We use the mongodb oid for all ids in all the index/graph DBs

require_once 'mongodb.php';
require_once 'elasticsearch_client.php';
require_once 'mongodb_delete.php';
require_once 'map_routes.php';

//submit to db
function saveData($data, $index, $collection, $newBusRoute) {
    //MongoDB
    try {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->insert(array_merge(['timecreated'=>''], $data));
        $result = $mongoDB->executeBulkWrite('bustops' . $collection, $bulk);

        //Elastic search
        $params = [];
        $params['body'] = array_merge(['id' => $result['_id']['$oid']], $params);
        //Database
        $params['index'] = 'bustops';
        //collection, table
        $params['type'] = $collection;

        $elasticresult = $elasticsearchClient->index($params);

        if (isset($elasticresult['error'])) {
            //Roll back mongo insert
            mongoDB_Delete($result['_id']['$oid'], $collection);
            return null;
        } else {
            //U may go ahead to put in graph db
            //If its a bustop then u need to map routes
            $newBusRoute && mapRoutes($newBusRoute);

            return $result['_id']['$oid'];
        }
    } catch (MongoDB\Driver\Exception\Exception $e) {
        //Catch all exceptions
        return null;
    }
}
