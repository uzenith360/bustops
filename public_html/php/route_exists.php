<?php
require_once 'mongodb.php';

function route_exists($hub, $lastBustop, $type) {
    global $mongoDB;

    $command = new MongoDB\Driver\Command([
        'aggregate' => 'routes',
        'pipeline' => [
            ['$match' => ['$and' => [['hub' => $hub], ['type' => $type]]]],
            ['$redact' => [
                    '$cond' => [
                        'if' => [
                            '$eq' => [['$arrayElemAt' => ['$stops', -1]], $lastBustop]
                        ],
                        'then' => '$$KEEP',
                        'else' => '$$PRUNE'
                    ]
                ]
            ],
            ['$count' => 'n']
        ]
    ]);
    $res = $mongoDB->executeCommand('bustops', $command)->toArray()[0];
    return $res->ok ? isset($res->result[0]) ? $res->result[0]->n : 0 : null;
}

//echo route_exists('593be78dda48142b10003573', '5944f058da48142df00007c5', 'DANFO');
