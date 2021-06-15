<?php

class ApiRequest{

    private $url;
    private $response;
    private $token;
    private $statusCode;

    function __construct($url, $token){
        $this->url = $url;
        $this->token = $token;
    }

    function request(){
        require_once "vendor/autoload.php";

        try{
            $client = new GuzzleHttp\Client();
            $res = $client->request('GET', $this->url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Accept' => 'application/xml'
                ]
            ]);
        
            $xml_raw = $res->getBody();
            
            $xml = simplexml_load_string($xml_raw);
            $json = json_encode($xml);
            $array = json_decode($json,TRUE);

            $this->response = $json;
            $this->statusCode = $res->getStatusCode();
        
        } catch(Exception $e){
            error_log($e->getMessage());
        }
    }

    function getResponse(){
        return $this->response;
    }

    function getStatusCode(){
        return $this->statusCode;
    }

}