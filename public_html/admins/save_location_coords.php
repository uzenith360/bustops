<?php
//check if logged in
ini_set('precision', '17');
$locationId = filter_input(INPUT_GET, 'i', FILTER_SANITIZE_STRING);
$lat = doubleval($_GET['lat']);
$lng = doubleval($_GET['lng']);
$isBustop = $_GET['b'] === 'true';

if ($locationId && $lat && $lng) {
    require_once '../php/mongodb_update.php';

    echo json_encode(mongoDB_update($locationId, 'locations', ['latlng' => ['lat' => $lat, 'lng' => $lng]]) && (!$isBustop || mongoDB_update($locationId, 'bustops', ['loc.coordinates' => [$lng, $lat]])));
}