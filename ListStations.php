<?php

require_once "ApiRequest.php";
require_once "ApiToken.php";
require_once "Station.php";

class ListStations{

    /**
     * All stations that are found with this query
     * @var Station[]
     */
    private $stations = [];

    function __construct($query){
        $database = new Database();

        $stationQuery = $database->getMysql()->prepare('SELECT * FROM `stationList` WHERE stationName LIKE ?');
        $stationQuery->execute(["%" . $query . "%"]);
        $stationsResult = $stationQuery->fetchAll();

        foreach ($stationsResult as $station) {
            array_push(
                $this->stations,
                new Station($station['stationName'], $station['stationId'])
            );
        }
    }

    /**
     * @return Station[]
     */
    public function getStations()
    {
        return $this->stations;
    }

    /**
     * Return the station that the user can mean (only prediction)
     * First check if a station contains Hbf in name because when user only says a station name without any extra phases he means probably the main station.
     * If no station with Hbf is found use the shortest station name.
     * @return Station
     */
    public function getRecommendedStation(): Station {
        $targetStation = null;

        foreach ($this->getStations() as $station) {
            if (strpos($station->getName(), 'Hbf')) {
                $targetStation = $station;
            }
        }

        if ($targetStation == null) {
            $shortest = 999;

            foreach ($this->getStations() as $station) {
                if (strlen($station->getName()) < $shortest) {
                    $shortest = strlen($station->getName());
                    $targetStation = $station;
                }
            }
        }

        return $targetStation;
    }

    public function setStationAsDefault($station, $userId): void {
        $database = new Database();

        if ($userId == null) {
            return;
        }

        $userStmt = $database->getMysql()->prepare("SELECT * FROM istmeinzugpuenktlich WHERE userId = ?");
        $userStmt->execute([$userId]);

        if($userStmt->rowCount() == 0){
            $stmt = $database->getMysql()->prepare("INSERT INTO istmeinzugpuenktlich (userId, stationId) VALUES (?, ?)");
            $stmt->execute([$userId, $station->getId()]);
        } else {
            $stmt = $database->getMysql()->prepare("UPDATE istmeinzugpuenktlich SET stationId = ? WHERE userId = ?");
            $stmt->execute([$station->getId(), $userId]);
        }
    }

}