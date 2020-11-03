<?php
class Database {
    private $_connection;
    private $_host = "devweb2016.cis.strath.ac.uk";
    private $_database = "xnb14132";
    private $_username = "xnb14132";
    private $_password = "Iechee5loa0U";
    private static $_instance;

    public static function getInstance() {
        if (!self::$_instance) {
            // If no instance then make one
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        try {
            $this->_connection = new PDO("mysql:host=$this->_host;dbname=$this->_database", "$this->_username", "$this->_password");
            $this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $ex) {
            echo 'ERROR: ' . $ex->getMessage();
        }
    }

    public function getConnection() {
        return $this->_connection;
    }
}
?>