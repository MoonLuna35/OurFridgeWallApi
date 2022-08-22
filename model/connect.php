

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

        public function generate_querry_str_args(string $coll): string {
                $str_arg = "";
                
                $splited_coll = explode(",", $coll);

                for($i = 0; $i < sizeof($splited_coll); $i++) {
                        $str_arg .= ":" . trim($splited_coll[$i]);
                       if ($i !== sizeof($splited_coll) -1) {
                                $str_arg .= ", ";
                        } 
                }
                return $str_arg;
        }
}
