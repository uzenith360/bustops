<?php
require_once 'mongodb.php';

function mongoDB_delete($oid, $collection) {
    global $mongoDB;
    
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->delete(['_id' => new MongoDB\BSON\ObjectID($oid)]);
    try{
        return $mongoDB->executeBulkWrite('bustops.'.$collection, $bulk);
    }catch(MongoDB\Driver\Exception\Exception $e){
         return null;
    }
}
