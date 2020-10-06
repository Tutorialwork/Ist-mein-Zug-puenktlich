<?php

require_once "ResponseBuilder.php";
require_once "ListTrainUpdates.php";
require_once "ListStations.php";
require_once "ListTrains.php";
require_once "Database.php";

class Actions{

    /**
     * @var UserId (short) without the "amzn1.ask.account." part
     */
    private $userId;
    private $intent;
    private $requestType;
    private $requestId;
    private $response;
    private $slots;
    private $sessionData;

    /**
     * Actions constructor.
     * @param $userId
     * @param $intent
     * @param $requestType
     * @param $requestId
     * @param $slots
     * @param $sessionData
     */
    public function __construct($userId, $intent, $requestType, $requestId, $slots, $sessionData)
    {
        $this->userId = $userId;
        $this->intent = $intent;
        $this->requestType = $requestType;
        $this->requestId = $requestId;
        $this->slots = $slots;
        $this->sessionData = $sessionData;
    }

    public function process(){
        $builder = new ResponseBuilder();
        $database = new Database();

        switch ($this->intent){
            case "AMAZON.CancelIntent":
            case "AMAZON.StopIntent":
                $builder->speechText("Bis zum nächsten Mal.");
                $this->response = $builder->getResponse();
                break;
            case "AMAZON.YesIntent":
                if(isset($this->sessionData["intent"])){
                    switch ($this->sessionData["intent"]){
                        case "addTrain":
                            $builder->speechTextAndReprompt("Super! Um wie viel Uhr fährt dein Zug normalerweiße?",
                            "Wann fährt dein Zug normalerweiße?",
                            null);
                            break;
                        case "listTrains":
                            $hour = $this->sessionData["hour"];
                            $requestDate = date("ymd");
                            if($hour < date("H")){
                                $requestDate = date("ymd", strtotime('+1 day'));
                            }

                            $stmt = $database->getMysql()->prepare("SELECT * FROM istmeinzugpuenktlich WHERE userId = ?");
                            $stmt->execute([$this->userId]);

                            $row = $stmt->fetch();

                            $list = new ListTrains($row["stationId"], $requestDate, $hour);

                            $departureList = [];
                            foreach ($list->getTrains() as $train){
                                if($train->getDeparture() != "Endstation"){
                                    array_push($departureList, strtotime($train->getDeparture()));
                                }
                            }
                            sort($departureList);

                            $out = "";
                            foreach ($departureList as $train) {
                                $out .= date("H:i", $train) . ", ";
                            }

                            if(substr($hour, 0, 1) == "0"){
                                //Remove leading 0
                                $hour = substr($hour, 1, 2);
                            }
                            $builder->speechTextAndReprompt("Folgende Züge habe ich um " . $hour . " Uhr gefunden: " . $out . ". Möchtest du einen Zug zur Liste hinzufügen?",
                            "Möchtest du nochmal versuchen einen Zug zur Liste hinzuzufügen?",
                            ["intent" => "addTrain"]);
                            break;
                        default:
                            /**
                             * Unexpected behavior
                             */
                            $builder->speechText("Etwas ist schiefgelaufen");
                            break;
                    }
                } else {
                    /**
                     * Unexpected behavior
                     */
                    $builder->speechText("Etwas ist schiefgelaufen");
                }
                $this->response = $builder->getResponse();
                break;
            case "AMAZON.NoIntent":
                $builder->speechText("Okay");
                $this->response = $builder->getResponse();
                break;
            case "stationSearch":
                $station = new ListStations($this->slots["station"]["value"]);

                if($station->getStation() != null){
                    $userStmt = $database->getMysql()->prepare("SELECT * FROM istmeinzugpuenktlich WHERE userId = ?");
                    $userStmt->execute([$this->userId]);
                    if($userStmt->rowCount() == 0){
                        $stmt = $database->getMysql()->prepare("INSERT INTO istmeinzugpuenktlich (userId, stationId) VALUES (?, ?)");
                        $stmt->execute([$this->userId, $station->getStation()->getId()]);
                    } else {
                        $stmt = $database->getMysql()->prepare("UPDATE istmeinzugpuenktlich SET stationId = ? WHERE userId = ?");
                        $stmt->execute([$station->getStation()->getId(), $this->userId]);
                    }


                    $builder->speechText($station->getStation()->getName() . " ist jetzt dein Heimatbahnhof.");
                } else {
                    $builder->speechText("Ich habe diesen Bahnhof leider nicht gefunden.");
                }

                $this->response = $builder->getResponse();
                break;
            case "addTrain":
                $time = $this->slots["time"]["value"];
                $timeSplit = explode(":", $time);

                $stmt = $database->getMysql()->prepare("SELECT * FROM istmeinzugpuenktlich WHERE userId = ?");
                $stmt->execute([$this->userId]);

                $row = $stmt->fetch();

                $watchedTrains = [];
                if($row["watchedTrains"] != null){
                    $watchedTrains = json_decode($row["watchedTrains"]);
                }
                if($row["stationId"] != null){
                    $requestDate = date("ymd");
                    if($timeSplit[0] < date("H")){
                        $requestDate = date("ymd", strtotime('+1 day'));
                    }

                    $list = new ListTrains($row["stationId"], $requestDate, $timeSplit[0]);

                    foreach ($list->getTrains() as $train) {
                        if($train->getDeparture() == $time){
                            $trainHumanId = $train->getTrainType() . $train->getTrainNumber();
                            if(!$this->containsTrain($time, $watchedTrains)){
                                array_push($watchedTrains, ["time" => $time, "train" => $trainHumanId]);
                                $watchedTrains = json_encode($watchedTrains);

                                $stmt = $database->getMysql()->prepare("UPDATE istmeinzugpuenktlich SET watchedTrains = ? WHERE userId = ?");
                                $stmt->execute([$watchedTrains, $this->userId]);

                                $builder->speechText($trainHumanId . " wurde zu deiner Liste hinzugefügt.");
                                $this->response = $builder->getResponse();
                            } else {
                                $builder->speechText("Du hast diesen Zug schon auf deiner Liste.");
                                $this->response = $builder->getResponse();
                            }

                            return;
                        }
                    }

                    //if this reached = train 404
                    $hour = $timeSplit[0];
                    if(substr($hour, 0, 1) == "0"){
                        //Remove leading 0
                        $hour = substr($hour, 1, 2);
                    }
                    $builder->speechTextAndReprompt("Ich habe kein Zug um diese Zeit gefunden. Willst du wissen welche Züge um " . $hour . " Uhr fahren?",
                    "Soll ich dir alle Züge nennen die um " . $hour . " Uhr fahren?",
                        ["intent" => "listTrains", "hour" => $timeSplit[0]]);
                } else {
                    //home station id missing
                    $builder->speechTextAndReprompt("Ich habe deinen Heimatbahnhof noch nicht gespeichert. Möchtest du mir verraten was dein Heimatbahnhof ist?",
                        "Was ist dein Heimatbahnhof?",
                        []
                    );
                }

                $this->response = $builder->getResponse();

                break;
            case "deleteTrain":
                $time = $this->slots["time"]["value"];

                $stmt = $database->getMysql()->prepare("SELECT * FROM istmeinzugpuenktlich WHERE userId = ?");
                $stmt->execute([$this->userId]);

                $row = $stmt->fetch();

                $watchedTrains = [];
                if($row["watchedTrains"] != null){
                    $watchedTrains = json_decode($row["watchedTrains"]);
                }

                if($this->containsTrain($time, $watchedTrains)){
                    unset($watchedTrains[$this->getTrainIndex($time, $watchedTrains)]);

                    $watchedTrains = json_encode($watchedTrains, true);

                    $updateStmt = $database->getMysql()->prepare("UPDATE istmeinzugpuenktlich SET watchedTrains = ? WHERE userId = ?");
                    $updateStmt->execute([$watchedTrains, $this->userId]);

                    $builder->speechText("Der Zug wurde von deiner Liste erfolgreich gelöscht.");
                } else {
                    $builder->speechText("Ein Zug um diese Zeit hast du nicht auf deiner Liste.");
                }

                $this->response = $builder->getResponse();
                break;
            default:
                $stmt = $database->getMysql()->prepare("SELECT * FROM istmeinzugpuenktlich WHERE userId = ?");
                $stmt->execute([$this->userId]);
                $row = $stmt->fetch();

                if($row["stationId"] != null){
                    if($row["watchedTrains"] != null){
                        $listTrainChanges = new ListTrainUpdates($row["stationId"], $this->userId);

                        $out = $listTrainChanges->requestChanges();

                        $delayList = $listTrainChanges->getDelayList();
                        $uiItems = [];
                        foreach ($delayList as $item){
                            array_push($uiItems, [
                                "primaryText" => $item["plannedDeparture"],
                                "tertiaryText" => ($item["delay"] != 0) ? "+ " . $item["delay"] . " Min" : null,
                                "imageAlignment" => "center",
                                "imageBlurredBackground" => true,
                                "imageScale" => "best-fit"
                            ]);
                        }

                        if(count($delayList) != 0){
                            $builder->speechAPL($out, $uiItems);
                        } else {
                            $builder->speechText($out);
                        }
                    } else {
                        $builder->speechTextAndReprompt("Du hast noch keinen Zug auf deiner Liste. Möchtest du jetzt einen hinzufügen?",
                            "Möchtest du einen Zug zu deiner Liste hinzufügen?",
                            ["intent" => "addTrain"]);
                    }
                } else {
                    $builder->speechTextAndReprompt("Ich habe deinen Heimatbahnhof noch nicht gespeichert. Möchtest du mir verraten was dein Heimatbahnhof ist?",
                        "Was ist dein Heimatbahnhof?",
                        []
                    );
                }

                $this->response = $builder->getResponse();
                break;
        }
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    private function containsTrain($time, $trainList){
        foreach ($trainList as $item){
            if($item->time == $time){
                return true;
            }
        }
        return false;
    }

    private function getTrainIndex($time, $trainList){
        $index = 0;
        foreach ($trainList as $item){
            if($item->time == $time){
                return $index;
            }
            $index++;
        }
        return -1;
    }

}