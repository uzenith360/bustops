<?php

//protect this file o
require_once '../php/mongodb.php';
require_once '../php/elasticsearch_client.php';

foreach ($mongoDB->executeQuery('bustops.locations', new MongoDB\Driver\Query([], ['projection' => ['names' => 1, 'addresses' => 1]])) as $r) {
    $elasticresult = $elasticsearchClient->index([
        'body' => ['names' => $r->names, 'addresses' => $r->addresses],
        //Database
        'index' => 'bustops',
        //collection, table
        'type' => 'locations',
        'id' => $r->_id->__toString()
    ]);
}
