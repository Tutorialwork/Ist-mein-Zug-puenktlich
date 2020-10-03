<?php

require_once "listTrains.php";
require_once "ApiRequest.php";

$atr = "@attributes";

$list = new ListTrains("8000718", "201003", "14");
$trainList = $list->getTrains();
$searchTrain = "RE17020";
$searchedTrain = null;

foreach ($trainList as $train){
    $trainHumanId = $train->getTrainType() . $train->getTrainNumber();
    if($trainHumanId == $searchTrain){
        $searchedTrain = $train;
    }
}

$apiToken = new ApiToken();
$api = new ApiRequest("https://api.deutschebahn.com/timetables/v1/fchg/8000718", $apiToken->getApiToken());
$api->request();

$response = $api->getResponse();
$response = json_decode($response, true);

if($searchedTrain != null){
    foreach($response["s"] as $item){
        if($item[$atr]["id"] == $searchedTrain->getTrainId()){ //can be null
            if(isset($item["ar"][$atr]["ct"])){
                $newDeparture = $item["ar"][$atr]["ct"];
                $newDeparture = substr($newDeparture, 6, strlen($newDeparture));
                $newDeparture = substr($newDeparture, 0, 2) . ":" . substr($newDeparture, 2, 4);
                echo "Requested train dp is today " . $newDeparture . " instead of " . $searchedTrain->getDeparture();
                die();
            } // else other change for example other station ...
        }
    }

    echo "No changes :)";
} else {
    echo "Train not found for requested time";
}