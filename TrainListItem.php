<?php

class TrainListItem{

    /**
     * List item for train watchlist
     */
    private $humanTrainId;
    private $plannedDeparture;

    /**
     * TrainListItem constructor.
     * @param $humanTrainId
     * @param $plannedDeparture
     */
    public function __construct($humanTrainId, $plannedDeparture)
    {
        $this->humanTrainId = $humanTrainId;
        $this->plannedDeparture = $plannedDeparture;
    }

    /**
     * @return mixed
     */
    public function getHumanTrainId()
    {
        return $this->humanTrainId;
    }

    /**
     * @param mixed $humanTrainId
     */
    public function setHumanTrainId($humanTrainId)
    {
        $this->humanTrainId = $humanTrainId;
    }

    /**
     * @return mixed
     */
    public function getPlannedDeparture()
    {
        return $this->plannedDeparture;
    }

    /**
     * @param mixed $plannedDeparture
     */
    public function setPlannedDeparture($plannedDeparture)
    {
        $this->plannedDeparture = $plannedDeparture;
    }




}