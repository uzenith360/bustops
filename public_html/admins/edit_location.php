<?php

$locationId = filter_input(INPUT_POST, 'i', FILTER_SANITIZE_STRING);
$changes = is_array($_POST['c']) ? $_POST['c'] : null;
$isBustop = boolval($_POST['b']);

if ($locationId && $changes) {
    require_once '../php/mongodb_update.php';
//edit from elastic search too
    $response = ['err' => null, 'result' => null];

    //if a bustop, edit it in neo4j too
    //neo4j only contains d bustop_id and that is readonly

    if (mongoDB_update($locationId, 'locations', $changes)) {
        //dnt forget to edit the bustops collection o
        $ntErr = true;
        if (isset($changes['names']) || isset($changes['addresses'])) {
            require_once 'elasticsearch_client.php';
            
            $doc = [];
            isset($changes['names']) && $doc['names'] = $changes['names'];
            isset($changes['addresses']) && $doc['addresses'] = $changes['addresses'];

            $elasticresult = $elasticsearchClient->update([
                'index' => 'bustops',
                'type' => 'locations',
                'id' => $locationId,
                'body' => [
                    'doc' => $doc
                ]
            ]);
            
            if (isset($elasticresult['error'])) {
                //Roll back mongo insert
                $response['err'] = ['error' => 'DB', 'message' => 'Problem updating location, please try again'];
                $ntErr = false;
            }
        }

        if ($isBustop && isset($changes['latlng']) && $ntErr) {
            if (!mongoDB_update($locationId, 'bustops', ['loc.coordinates' => [$changes['latlng']['lng'], $changes['latlng']['lat']]])) {
                $response['err'] = ['error' => 'DB', 'message' => 'Problem updating location, please try again'];
            }
        }
    } else {
        $response['err'] = ['error' => 'DB', 'message' => 'Problem updating location, please try again'];
    }

    echo json_encode($response);
}

