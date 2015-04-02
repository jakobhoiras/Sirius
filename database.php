<?php

require_once 'constants.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class Mysql_spil {
   
    function __construct() {
		$this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD) or 
									die('there was a problem connecting to the database.');
    }
