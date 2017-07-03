<?php

require_once 'vendor/autoload.php';

use GraphAware\Neo4j\Client\ClientBuilder;

//Use d ip address or link local address to prevent latency due to DNS lookups
$neo4jClient = ClientBuilder::create()
        ->addConnection('default', 'http://neo4j:zenith360@127.0.0.1:7474') // Example for HTTP connection configuration (port is optional)
        //->addConnection('bolt', 'bolt://neo4j:zenith360@127.0.0.1:7687') // Example for BOLT connection configuration (port is optional)
        ->setDefaultTimeout(18)
        ->build();
