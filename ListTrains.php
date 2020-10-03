<?php

require_once "ApiRequest.php";
require_once "Train.php";
require_once "ApiToken.php";

class ListTrains{

    private $trains = [];

    function __construct($stationId, $date, $hour){
        $atr = "@attributes";

        $url = "https://api.deutschebahn.com/timetables/v1/plan/" . $stationId . "/" . $date . "/" . $hour;
        $apiToken = new ApiToken();
        $api = new ApiRequest($url, $apiToken->getApiToken());

        $api->request();

        $response = $api->getResponse();
        if($api->getStatusCode() == 200){
            $response = json_decode($response, true);

            foreach($response["s"] as $item){
                $departure = "Endstation";
                if(isset($item["dp"])){
                    $departure = $item["dp"][$atr]["pt"];
                    $departure = substr($departure, 6, strlen($departure));
                    $departure = substr($departure, 0, 2) . ":" . substr($departure, 2, 4);
                }
                array_push($this->trains, new Train($departure, $item["tl"][$atr]["c"], $item["tl"][$atr]["n"], $item[$atr]["id"]));
            }
        }
    }

    /**
     * @return array
     */
    public function getTrains()
    {
        return $this->trains;
    }

}