<?php

require_once '../php/mongodb.php';
/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */
//get adminid from session
$adminId = '2';
$routeId = filter_input(INPUT_POST, 'i', FILTER_SANITIZE_STRING);

if ($routeId) {
    $r = $mongoDB->executeQuery('bustops.routes', new MongoDB\Driver\Query(['admin_id' => $adminId, '_id' => new MongoDB\BSON\ObjectID($routeId)], ['projection' => ['_id' => 0, 'admin_id' => 0], 'limit' => 1]))->toArray()[0];
    $r->nstops = [];
    $n;
    foreach ($r->stops as $i) {
        $n = $mongoDB->executeQuery('bustops.locations', new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectID($i)], ['projection' => ['_id' => 0, 'names' => 1], 'limit' => 1]))->toArray();
        $r->nstops[] = isset($n[0]) ? $n[0]->names : [];
    }
    $n = $mongoDB->executeQuery('bustops.locations', new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectID($r->hub)], ['projection' => ['_id' => 0, 'names' => 1], 'limit' => 1]))->toArray();
    $r->nhub = isset($n[0]) ? $n[0]->names : [];

    echo json_encode($r);
}