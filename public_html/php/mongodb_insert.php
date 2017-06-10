<?php

require_once 'mongodb.php';

function mongoDB_insert($data, $collection) {
    global $mongoDB;

    try {
        $bulk = new MongoDB\Driver\BulkWrite();
//_id must be a BSON ObjectID
        $bulk->insert(array_merge(['timecreated' => isset($data['timecreated']) ? $data['timecreated'] : (new DateTime())->format(DateTime::ISO8601), '_id' => $bsonOID = isset($data['_id']) ? $data['_id'] : new MongoDB\BSON\ObjectID], $data));
        $result = $mongoDB->executeBulkWrite('bustops.' . $collection, $bulk);
        return $bsonOID->oid;
    } catch (MongoDB\Driver\Exception\Exception $e) {
        //Catch all exceptions
        return null;
    }
}
