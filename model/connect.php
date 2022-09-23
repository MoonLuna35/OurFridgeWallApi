

<?php


// Connect with the database.
class DB {
        protected PDO $_db;
        protected string $_querry_str;
        protected array $_querry_args;
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

        protected function commiter(bool $is_needed_id=false, int $h = 0): PDOStatement|array|false {
                $result = false;
                if ($h === $_ENV["MAX_TRY"]) {
                    log503(__FILE__, __LINE__);
                }
                try {
                    $this->_db->beginTransaction();
                    
                    $query = $this->_db->prepare($this->_querry_str);
                    
                    $query->execute($this->_querry_args);
                    
                    if ($is_needed_id) {
                        $out["id"] = $this->_db->lastInsertId();
                        $this->_db->commit();
                        $out["querry"] = $query;

                        return $out;
                    }
                    else {
                        $this->_db->commit();
                    }
                    return $query;
                }
                catch(Exeption $e) {
                    $this->_db->rollBack();
                    $this->commiter($is_needed_id, $h +1);
                }
            }
}
