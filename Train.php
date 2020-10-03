<?php

class Train{

    private $departure;
    private $trainType;
    private $trainNumber;
    private $trainId;

    /**
     * Train constructor.
     * @param $departure
     * @param $trainType
     * @param $trainNumber
     * @param $trainId
     */
    public function __construct($departure, $trainType, $trainNumber, $trainId)
    {
        $this->departure = $departure;
        $this->trainType = $trainType;
        $this->trainNumber = $trainNumber;
        $this->trainId = $trainId;
    }

    /**
     * @return mixed
     */
    public function getDeparture()
    {
        return $this->departure;
    }

    /**
     * @param mixed $departure
     */
    public function setDeparture($departure)
    {
        $this->departure = $departure;
    }

    /**
     * @return mixed
     */
    public function getTrainType()
    {
        return $this->trainType;
    }

    /**
     * @param mixed $trainType
     */
    public function setTrainType($trainType)
    {
        $this->trainType = $trainType;
    }

    /**
     * @return mixed
     */
    public function getTrainNumber()
    {
        return $this->trainNumber;
    }

    /**
     * @param mixed $trainNumber
     */
    public function setTrainNumber($trainNumber)
    {
        $this->trainNumber = $trainNumber;
    }

    /**
     * @return mixed
     */
    public function getTrainId()
    {
        return $this->trainId;
    }

    /**
     * @param mixed $trainId
     */
    public function setTrainId($trainId)
    {
        $this->trainId = $trainId;
    }





}