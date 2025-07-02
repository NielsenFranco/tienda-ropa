<?php
require_once 'config.php';

class Database {
    private $con;

    public function __construct() {
        $this->con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->con->connect_error) {
            die("Connection failed: " . $this->con->connect_error);
        }
    }

    public function getConnection() {
        return $this->con;
    }
}
?>