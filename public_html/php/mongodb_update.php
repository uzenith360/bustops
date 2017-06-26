<?php

require_once 'mongodb.php';

function mongoDB_update($id, $collection, $changes) {
    global $mongoDB;

    try {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update(
                ['_id' => new MongoDB\BSON\ObjectID($id)], ['$set' => $changes]
        );
        return $mongoDB->executeBulkWrite('bustops.' . $collection, $bulk);
    } catch (MongoDB\Driver\Exception\Exception $e) {
        return null;
    }
}
