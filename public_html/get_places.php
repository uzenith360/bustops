<?php
//this script extremely fast

/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */

echo print_r($mongo->executeQuery('bustops.locations', new MongoDB\Driver\Query(["$and"=>[["latlng.lat"=>["$gte"=>doubleval($_GET['south'])]], ["latlng.lat"=>["$lte"=>doubleval($_GET['north'])]], ["latlng.lng"=>["$gte"=>doubleval($_GET['west'])]], ["latlng.lng"=>["$lte"=>doubleval($_GET['east'])]]]], [])));