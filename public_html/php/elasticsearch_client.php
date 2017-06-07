<?php
require 'vendor/autoload.php';

use Elasticsearch\ClientBuilder;

$elasticsearchClient = ClientBuilder::create()->build();