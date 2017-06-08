<?php

require_once 'mongodb.php';

function mongoDB_Insert($data, $collection) {
    global $mongoDB;

    try {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->insert(array_merge(['timecreated' => (new DateTime())->format(DateTime::ISO8601), '_id' => $bsonOID = new MongoDB\BSON\ObjectID], $data));
        $result = $mongoDB->executeBulkWrite('bustops.' . $collection, $bulk);
        return $bsonOID->oid;
    } catch (MongoDB\Driver\Exception\Exception $e) {
        //Catch all exceptions
        return null;
    }
}
