<?php
require_once '../php/mongodb.php';
//get adminid from session
$adminId = '2';
$page = filter_input(INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT);

if($page){
    echo json_encode($mongoDB->executeQuery('bustops.routes', new MongoDB\Driver\Query(['admin_id' => $adminId], ['limit' => 10,'skip' => ($page-1)*10]))/*->skip($page*10)*/->toArray());
}else{
    echo json_encode($mongoDB->executeCommand('bustops', new MongoDB\Driver\Command(["count" => "routes", "query" => ['admin_id' => $adminId]]))->toArray()[0]->n);
}