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

    /**
     * Actions constructor.
     * @param $userId
     * @param $intent
     * @param $requestType
     * @param $requestId
     * @param $slots
     */
    public function __construct($userId, $intent, $requestType, $requestId, $slots)
    {
        $this->userId = $userId;
        $this->intent = $intent;
        $this->requestType = $requestType;
        $this->requestId = $requestId;
        $this->slots = $slots;
    }

    public function process(){
        $builder = new ResponseBuilder();
        $database = new Database();

        switch ($this->intent){
            case "stationSearch":
            case "stationInput":
                $station = new ListStations($this->slots["station"]["value"]);

                if($station->getStation() != null){
                    $stmt = $database->getMysql()->prepare("INSERT INTO istmeinzugpuenktlich (userId, stationId) VALUES (?, ?)");
                    $stmt->execute([$this->userId, $station->getStation()->getId()]);

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
                    $builder->speechText("Ich habe kein Zug um diese Zeit gefunden.");
                } else {
                    //home station id missing
                    $builder->speechText("Setzte zuerst dein Heimatbahnhof");
                }

                $this->response = $builder->getResponse();

                break;
            default:
                $stmt = $database->getMysql()->prepare("SELECT * FROM istmeinzugpuenktlich WHERE userId = ?");
                $stmt->execute([$this->userId]);
                $row = $stmt->fetch();

                if($row["stationId"] != null){
                    $listTrainChanges = new ListTrainUpdates($row["stationId"], $this->userId);

                    $builder->speechText($listTrainChanges->requestChanges());
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

}