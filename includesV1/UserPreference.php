<?php

class UserPreference{
    private $con;

    function __construct(){
        require_once dirname(__FILE__) . '/DbConnect.php';
        require_once dirname(__FILE__) . '/DbOperations.php';

        $db = new DbConnect;

        $this->con = $db->connect();
    }
}