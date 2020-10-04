<?php

require_once "ListTrains.php";
require_once "Database.php";
require_once "ApiRequest.php";
require_once "ApiToken.php";
require_once "TrainListItem.php";

class ListTrainUpdates{

    /**
     * This class request all know changes from the API
     */
    private $stationId;
    private $userId;
    private $trainIds = [];
    private $trainListItems = [];

    public function __construct($stationId, $userId){
        $this->stationId = $stationId;
        $this->userId = $userId;
        $this->requestTrainIds();
    }

    /**
     * This method requests the current train id (changes daily)
     */
    public function requestTrainIds(){
        $database = new Database();
        $stmt = $database->getMysql()->prepare("SELECT * FROM istmeinzugpuenktlich WHERE userId = ?");
        $stmt->execute([$this->userId]);
        $row = $stmt->fetch();
        $normalTrainList = json_decode($row["watchedTrains"]);
        foreach ($normalTrainList as $watchedTrain){
            $timeSplit = explode(":", $watchedTrain->time);
            $list = new ListTrains($this->stationId, date("ymd"), $timeSplit[0]);
            $trainList = $list->getTrains();
            foreach ($trainList as $train){
                $trainHumanId = $train->getTrainType() . $train->getTrainNumber();
                if($watchedTrain->train == $trainHumanId){
                    //Found train
                    $trainId = $train->getTrainId();
                    array_push($this->trainIds, $trainId);
                    array_push($this->trainListItems, new TrainListItem($watchedTrain->train, $watchedTrain->time));
                }
            }
        }
    }

    /**
     * Request changes and return string to speak
     */
    public function requestChanges(){
        $speechText = "";

        $atr = "@attributes";
        $apiToken = new ApiToken();
        $api = new ApiRequest("https://api.deutschebahn.com/timetables/v1/fchg/" . $this->stationId, $apiToken->getApiToken());
        $api->request();

        $response = $api->getResponse();
        $response = json_decode($response, true);

        $index = 0;
        foreach ($this->trainIds as $trainId){
            $foundChanges = false;
            foreach($response["s"] as $item){
                if($item[$atr]["id"] == $trainId){
                    if(isset($item["dp"][$atr]["ct"])){
                        $newDeparture = $item["dp"][$atr]["ct"];
                        $newDeparture = substr($newDeparture, 6, strlen($newDeparture));
                        $newDeparture = substr($newDeparture, 0, 2) . ":" . substr($newDeparture, 2, 4);
                        if($newDeparture != $this->trainListItems[$index]->getPlannedDeparture()){ //Ignore delay fewer as 60 seconds
                            $speechText .= $this->splitHumanTrainId($this->trainListItems[$index]->getHumanTrainId()) . " kommt heute " . $newDeparture . " statt " . $this->trainListItems[$index]->getPlannedDeparture() . " ";
                            $foundChanges = true;
                        }
                    } // else other change for example other station ...
                }
            }

            if(!$foundChanges){
                $speechText .= $this->splitHumanTrainId($this->trainListItems[$index]->getHumanTrainId()) . " kommt heute planmäßig um ". $this->trainListItems[$index]->getPlannedDeparture() . " ";
            }
            $index++;
        }

        return $speechText;
    }


    /**
     * Split human train id into RE12345 -> R E 12345
     * This can be better spoken by Alexa
     */
    public function splitHumanTrainId($toSplit){
        $trainType1 = substr($toSplit, 0, 1);
        $trainType2 = substr($toSplit, 1, 1);
        $trainNumber = substr($toSplit, 2, strlen($toSplit) - 2);
        return $trainType1 . " " . $trainType2 . " " . $trainNumber;
    }

}