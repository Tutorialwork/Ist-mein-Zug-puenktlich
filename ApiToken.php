<?php

class ApiToken{

    /**
     * This token will be used to access the DB api.
     */
    private $apiToken = "Your API Key from developer.deutschebahn.com";

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }



}