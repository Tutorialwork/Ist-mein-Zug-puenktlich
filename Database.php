<?php

class Database{

    private $host = "dbHost";
    private $name = "dbName";
    private $user = "dbUser";
    private $password = "dbPassword";

    private $mysql;

    public function __construct(){
        try{
            $this->mysql = new PDO("mysql:host=$this->host;dbname=$this->name", $this->user, $this->password);
            $this->mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e){
            echo "MySQL Error: " . $e->getMessage();
        }
    }

    /**
     * @return PDO
     */
    public function getMysql()
    {
        return $this->mysql;
    }


}