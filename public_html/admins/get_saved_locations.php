<?php
require_once '../php/mongodb.php';
/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */
//get adminid from session
$adminId = '2';
$page = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT);

if($page){
    echo json_encode($mongoDB->executeQuery('bustops.locations', new MongoDB\Driver\Query(['admin_id' => $adminId], ['projection'=>['names'=>1,'type'=>1], 'limit' => 10,'skip' => ($page-1)*10,'sort'=>['_id'=>-1]]))->toArray());
}else{
    echo json_encode($mongoDB->executeCommand('bustops', new MongoDB\Driver\Command(["count" => "locations", "query" => ['admin_id' => $adminId]]))->toArray()[0]->n);
}