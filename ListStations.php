<?php

require_once "ApiRequest.php";
require_once "ApiToken.php";
require_once "Station.php";

class ListStations{

    private $station;

    function __construct($query){
        $atr = "@attributes";

        $apiToken = new ApiToken();
        $api = new ApiRequest("https://api.deutschebahn.com/timetables/v1/station/" . $query, $apiToken->getApiToken());
        $api->request();

        $response = $api->getResponse();
        $response = json_decode($response, true);

        if(isset($response["station"])){
            $this->station = new Station($response["station"][$atr]["name"], $response["station"][$atr]["eva"]);
        }
    }

    /**
     * @return Station
     */
    public function getStation()
    {
        return $this->station;
    }

}