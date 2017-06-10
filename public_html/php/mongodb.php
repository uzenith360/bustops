<?php
//use ip address to eliminate dns lookups
$mongoDB = new MongoDB\Driver\Manager("mongodb://127.0.0.1:27017/bustops?w=1");