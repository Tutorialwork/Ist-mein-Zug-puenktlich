<?php

/**
 * Class Train contains all relevant information to a train
 */
class Train{

    /**
     * Contains planned departure time like 7:20
     * @var string
     */
    private $departure;
    /**
     * Type of train for example ICE, RE, RB etc.
     * @var string
     */
    private $trainType;
    /**
     * Train number part after train type RE ->17170<-
     * @var string
     */
    private $trainNumber;
    /**
     * Train UUID that identifies train. This is unique, every day has the a train at the same time a different train UUID.
     * @var string
     */
    private $trainId;
    /**
     * goal of the travel e.g. Berlin, Köln etc.
     * @var string
     */
    private $destination;
    /**
     * the number of the rail in the station where the train departure.
     * can be null if the train ends in this station.
     * @var string
     */
    private $platform;

    /**
     * Train constructor.
     * @param $departure
     * @param $trainType
     * @param $trainNumber
     * @param $trainId
     * @param $destination
     * @param $platform
     */
    public function __construct($departure, $trainType, $trainNumber, $trainId, $destination, $platform)
    {
        $this->departure = $departure;
        $this->trainType = $trainType;
        $this->trainNumber = $trainNumber;
        $this->trainId = $trainId;
        $this->destination = $destination;
        $this->platform = $platform;
    }

    /**
     * Returns train id that is public in the app or website
     * e.g. RE17121
     * @return string
     */
    public function getTrainHumanId(): string {
        return $this->getTrainType() . $this->getTrainNumber();
    }

    /**
     * @return string
     */
    public function getDeparture(): string
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
     * @return string
     */
    public function getTrainType(): string
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
     * @return string
     */
    public function getTrainId(): string
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

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @param mixed $destination
     */
    public function setDestination($destination): void
    {
        $this->destination = $destination;
    }

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    /**
     * @param mixed $platform
     */
    public function setPlatform($platform): void
    {
        $this->platform = $platform;
    }

    /**
     * For debugging purpose
     * @return string
     */
    public function __toString()
    {
        return "Train " . $this->getTrainType() . $this->getTrainNumber() . " 
        Train UUID: " . $this->getTrainId() .
        "Platform: " . $this->getPlatform() .
        "Departure: " . $this->getDeparture() .
        "Destination: " . $this->getDestination();
    }


}