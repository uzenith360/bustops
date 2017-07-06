<?php

require_once '../php/mongodb.php';
/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */
//get adminid from session
$adminId = '2';
$page = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT);

if ($page) {
    $r = $mongoDB->executeQuery('bustops.routes', new MongoDB\Driver\Query(['admin_id' => $adminId], ['projection' => ['type' => 1, 'hub' => 1, 'destinations' => 1], 'limit' => 10, 'skip' => ($page - 1) * 10, 'sort' => ['_id' => -1]]))->toArray();
    foreach ($r as &$i) {
        $n = $mongoDB->executeQuery('bustops.locations', new MongoDB\Driver\Query(['_id' => new MongoDB\BSON\ObjectID($i->hub)], ['projection' => ['_id' => 0, 'names' => 1], 'limit' => 1]))->toArray();
        $i->hub = isset($n[0]) ? $n[0]->names : [];
        $i->_id = $i->_id->__toString();
    }
    echo json_encode($r);
} else {
    echo json_encode($mongoDB->executeCommand('bustops', new MongoDB\Driver\Command(["count" => "routes", "query" => ['admin_id' => $adminId]]))->toArray()[0]->n);
}