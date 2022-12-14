<?php

//Saves data to mongodb and elastic search and neo4j if applicable
//We use the mongodb oid for all ids in all the index/graph DBs

require_once 'mongodb.php';
require_once 'elasticsearch_client.php';
require_once 'mongodb_delete.php';

//submit to db
function saveData($data, $index, $collection) {
    global $mongoDB;
    global $elasticsearchClient;

    //MongoDB
    try {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->insert(array_merge(['timecreated' => (new DateTime())->format(DateTime::ISO8601), '_id' => $bsonOID = new MongoDB\BSON\ObjectID], $data));
        $result = $mongoDB->executeBulkWrite('bustops.' . $collection, $bulk);
        $id = $bsonOID->oid;
        if (isset($index)) {//Elastic search
            $elasticresult = $elasticsearchClient->index([
                'body' => $index,
                //Database
                'index' => 'bustops',
                //collection, table
                'type' => $collection,
                'id' => $id
            ]);

            if (isset($elasticresult['error'])) {
                //Roll back mongo insert
                mongoDB_delete($id, $collection);
                return null;
            } else {
                return $id;
            }
        } else {
            return $id;
        }
    } catch (MongoDB\Driver\Exception\Exception $e) {
        //Catch all exceptions
        return null;
    }
}
