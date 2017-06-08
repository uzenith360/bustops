<?php
require_once 'mongodb_insert.php';
require_once 'map_routes.php';

 map_routes($routeInfo);
 mongoDB_Insert($routeInfo, 'routes');