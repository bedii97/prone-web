<?php

class DbConnect{
    private $con;

    function connect(){

        include_once dirname(__FILE__) . '/Constants.php';

        try {
            $this->con = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8",DB_USER, DB_PASSWORD);
        } catch ( PDOException $e ){
            echo $e->getMessage();
            return null;
        }
        return $this->con; 
    }
}

    