<?php

//Delete data saved by save_data.php

require_once 'elasticsearch_client.php';
require_once 'mongodb_delete.php';

function delete_data($id, $hasIndex, $collection) {
    global $elasticsearchClient;

    $errstat = 0;

    if (!mongoDB_delete($id, $collection)) {
        $errstat = 1;
    }

    if ($hasIndex) {
        $elasticresult = $elasticsearchClient->delete([
            //Database
            'index' => 'bustops',
            //collection, table
            'type' => $collection,
            'id' => $id
        ]);

        if (isset($elasticresult['error'])) {
            $errstat += 2;
        }
    }
    
    //if errstat=0(No error), errstat=1(deletion of mongodb only failed), errstat=2 (deletion of elasticsearch only failed), errStat=3 (Deletion of both mongodb and elasticsearch failed)
    return $errstat;
}
