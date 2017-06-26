<?php
require_once 'mongodb.php';

function mongoDB_delete($id, $collection) {
    global $mongoDB;
    
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->delete(['_id' => new MongoDB\BSON\ObjectID($id)]);
    try{
        return $mongoDB->executeBulkWrite('bustops.'.$collection, $bulk);
    }catch(MongoDB\Driver\Exception\Exception $e){
         return null;
    }
}
