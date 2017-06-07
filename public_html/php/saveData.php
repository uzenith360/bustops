<?php

//Saves data to mongodb and elastic search and neo4j if applicable
//We use the mongodb oid for all ids in all the index/graph DBs

require_once 'mongodb.php';
require_once 'elasticsearch_client.php';
require_once 'mongodb_delete.php';
require_once 'map_routes.php';

//submit to db
function saveData($data, $index, $collection, $newBusRoute) {
    global $mongoDB;
    global $elasticsearchClient;
    
    //MongoDB
    try {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->insert(array_merge(['timecreated' => (new DateTime())->format(DateTime::ISO8601), '_id' => $bsonOID = new MongoDB\BSON\ObjectID], $data));
        $result = $mongoDB->executeBulkWrite('bustops.' . $collection, $bulk);
        $id = $bsonOID->oid;
        if (isset($index)) {//Elastic search
            $params = [];
            $params['body'] = array_merge(['id' => $id], $index);
            //Database
            $params['index'] = 'bustops';
            //collection, table
            $params['type'] = $collection;

            $elasticresult = $elasticsearchClient->index($params);

            if (isset($elasticresult['error'])) {
                //Roll back mongo insert
                mongoDB_Delete($id, $collection);
                return null;
            } else {
                //U may go ahead to put in graph db
                //If its a bustop then u need to map routes
                isset($newBusRoute) && mapRoutes($newBusRoute);

                return $id;
            }
        } else {
            //U may go ahead to put in graph db
            //If its a bustop then u need to map routes
            isset($newBusRoute) && mapRoutes($newBusRoute);

            return $id;
        }
    } catch (MongoDB\Driver\Exception\Exception $e) {
        //Catch all exceptions
        return null;
    }
}
