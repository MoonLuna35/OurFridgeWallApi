<?php 
    include_once ROOT_PATH . 'model/timeTable/event/Event.php';
    include_once ROOT_PATH . 'model/timeTable/repeater/RepeaterDb.php';
    include_once ROOT_PATH . 'model/connect.php';
    
    abstract class AbstractEventDb extends DB {
        protected string $_querry_str_args = "";
        protected string $_querry_str_coll = "";
        protected array $_querry_args = array();
        protected string $_querry_str = "";
        
        abstract public function insert($event, int $h_tree=0);
        //abstract public function select();

        protected function commiter(bool $is_insert=false, int $h=0): PDOStatement|int|false {
            $result = false;
            if ($h === $_ENV["MAX_TRY"]) {
                log503(__FILE__, __LINE__);
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare($this->_querry_str);
                $result = $query->execute($this->_querry_args);
                if($is_insert) { 
                    $result = $this->_db->lastInsertId();
                }
                $this->_db->commit();
                return $result;
            }
            catch(Exeption $e) {
                $this->_db->rollBack();
                $this->commiter($h +1);
            }
        }
    }

    class EventDb extends AbstractEventDb {
        private Event $_event; 

        public function insert($event, int $h_tree=0) {
            $rep_arr =array();
            $this->_event = $event;
            //On genere le nom des collones
            $this->_querry_str_coll = "
                date_begin, 
                label,
                date_end,
                desc,
                place
            ";
            //On genere le nom des arguments
            $this->_querry_str_args = $this->generate_querry_str_args($this->_querry_str_coll);
            if($this->_event->get_repeater() instanceof RepeaterDaily) {
                
                $repDb = new RepeaterDailyDb();
                //On gere le repeteur
                $rep_arr = $repDb->insert($this->_event->get_repeater());
            }
            $this->_querry_str_coll = trim($this->_querry_str_coll) . ", " .  $rep_arr["str_coll"];
            $this->_querry_str_args = trim($this->_querry_str_args) . ", " .  $rep_arr["str_args"];

            $this->_querry_args = array(
                ":date_begin" => $this->_event->get_date_begin(), 
                ":label" => $this->_event->get_label(),
                ":date_end" => $this->_event->get_date_end(),
                ":desc" => $this->_event->get_desc(),
                ":place" => $this->_event->get_place()
            );
            foreach($rep_arr["args"] as $key => $value) {
                $this->_querry_args[$key] = $rep_arr["args"][$key];
            }
            
            //On genere la requette
            $this->_querry_str = "
                INSERT INTO 
                    timeTable_event(
                        $this->_querry_str_coll
                    )
                    VALUES(
                        $this->_querry_str_args
                    )
            ";
            print_r($this->_querry_args);
            //On commit
        }

		

		
    }