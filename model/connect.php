

<?php


// Connect with the database.
class DB {
        protected $_db;
        function __construct() {
                $this->_db = new PDO(
                        'mysql:host=' . $_ENV["DB_HOST"] . ';dbname=' . $_ENV["DB_NAME"], 
                        $_ENV["DB_USER"], 
                        $_ENV["DB_PASS"], array(
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
                );
                $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
}
