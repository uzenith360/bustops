<?php
//make this script extremely fast

/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */
require_once 'php/mongodb.php';
echo '[';
$f=false;
foreach($mongoDB->executeQuery('bustops.locations', new MongoDB\Driver\Query(['$and'=>[["latlng.lat"=>['$gte'=> (+$_GET['south'])]], ["latlng.lat"=>['$lte'=>(+$_GET['north'])]], ["latlng.lng"=>['$gte'=>(+$_GET['west'])]], ["latlng.lng"=>['$lte'=>(+$_GET['east'])]]]], ['limit' => 100])) as $r){
   echo (!$f&&($f=true)?'':',').json_encode($r);
}
echo ']';