<?php

require_once 'mongodb.php';

function delete_route_stop($stop) {
    global $mongoDB;

    $command = new MongoDB\Driver\Command([
        'aggregate' => 'routes',
        'pipeline' => [
            ['$redact' => [
                    '$cond' => [
                        'if' => [
                            '$ne' => [['$indexOfArray' => ['$stops', $stop]], -1]
                        ],
                        'then' => '$$KEEP',
                        'else' => '$$PRUNE'
                    ]
                ]
            ],
            ['$project' => [
                    'i' => ['$indexOfArray' => ['$stops', $stop]]
                ]
            ]
        ]
    ]);
    $res = $mongoDB->executeCommand('bustops', $command)->toArray()[0];

    if ($res->ok && isset($res->result[0])) {
        try {
            $timeedited = (new DateTime())->format(DateTime::ISO8601);
            $bulk = new MongoDB\Driver\BulkWrite;
            foreach ($res->result as $result) {
                $bulk->update(
                        ['_id' => $result->_id], ['$unset' => ['fares.' . $result->i => 1]]
                );
                $bulk->update(
                        ['_id' => $result->_id], ['$pull' => ['fares'=> null]]
                );
                $bulk->update(
                        ['_id' => $result->_id], ['$pull' => ['stops'=> $stop]]
                );
            }
            return $mongoDB->executeBulkWrite('bustops.routes', $bulk);
        } catch (MongoDB\Driver\Exception\Exception $e) {
            return null;
        }
    } else {
        return 0;
    }
}

//echo print_r(delete_route_stop('593be564da48142b1000356e'));
