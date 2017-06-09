<?php
require_once 'mongodb.php';

function mongoDB_delete($oid, $collection) {
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->delete(['_id' => $oid]);
    try{
        return $mongoDB->executeBulkWrite('bustops.'.$collection, $bulk);
    }catch(MongoDB\Driver\Exception\Exception $e){
         return null;
    }
}
