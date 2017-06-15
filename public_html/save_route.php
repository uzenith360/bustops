<?php

/* session_start();

  if (!isset($_SESSION['id'])) {
  exit();
  } */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'php/form_validate.php';
    require_once 'php/form_validate.php';
    require_once 'php/mongodb_delete.php';

    $response = ['err' => null, 'result' => null];

    $cleanedUserInputMap = array_map(function($value) {
        return htmlspecialchars(strip_tags(trim(isset($_POST[$value]) ? $_POST[$value] : '')));
    }, ['type' => 'type', 'admin_id' => 'admin_id', 'hub' => 'hub', 'startTime'=>'startTime', 'endTime'=>'endTime']);
    $cleanedUserInputMap['stops'] = is_array($_POST['stops']) ? $_POST['stops'] : [];
    $cleanedUserInputMap['fares'] = is_array($_POST['fares']) ? $_POST['fares'] : [];

    $validationResult = $form_validate([
        'admin_id' => 'required',
        'fares' => 'required|array|arrayminlength:1', //array now
        'type' => 'required',
        'hub' => 'required',
        'startTime' => 'required',
        'endTime' => 'required',
        'stops' => 'required|array|arrayminlength:1', //array now
            ], $cleanedUserInputMap);

    if (empty($validationResult)) {
        require_once 'php/mongodb_insert.php';

        $ntMissingInfo = true;
        for ($i = 0, $routeInfoStops = $cleanedUserInputMap['stops'], $routeInfoFares = $cleanedUserInputMap['fares'], $routeInfoCt = count($routeInfoStops), $newStops = [], $cleanStops = [], $cleanFares = []; $i < $routeInfoCt; ++$i) {
            $cleanStop = htmlspecialchars(strip_tags(trim($routeInfoStops[$i])));
            $cleanFare = htmlspecialchars(strip_tags(trim($routeInfoFares[$i])));
            if (($cleanStop && !$cleanFare) || (!$cleanStop && $cleanFare)) {
                $ntMissingInfo = false;
                break;
            } else if ($cleanStop && $cleanFare) {
                $cleanStops[$i] = $cleanStop;
                $cleanFares[$i] = $cleanFare;
            }
        }

        if ($ntMissingInfo) {
            if (count($cleanStops)) {
                $cleanedUserInputMap['stops'] = $cleanStops;
                $cleanedUserInputMap['fares'] = $cleanFares;

                //save to mongo store
                if (($id = mongoDB_insert($cleanedUserInputMap, 'routes'))) {
                    require_once 'php/map_routes.php';
                    //actually create the routes
                    if (!($response['result'] = map_routes($cleanedUserInputMap))) {
                        $response ['err'] = ['error' => 'DB', 'msg' => 'Problem saving data, please try again'];
                        mongoDB_delete($id, 'routes');
                    }
                } else {
                    $response ['err'] = ['error' => 'DB', 'msg' => 'Problem saving data, please try again'];
                }
            } else {
                $response ['err'] = ['error' => 'NOSTOPS', 'msg' => 'No stops were specified'];
            }
        } else {
            $response ['err'] = ['error' => 'MISSINGINFO', 'msg' => 'Missing route information'];
        }
    } else {
        $response ['err'] = ['error' => 'VALIDATION', 'msg' => $validationResult];
    }
    echo json_encode($response);
}
?>