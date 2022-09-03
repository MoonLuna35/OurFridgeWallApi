<?php 
    include_once ROOT_PATH . 'model/timeTable/event/Event.php';
    include_once ROOT_PATH . 'model/timeTable/repeater/RepeaterDb.php';
    include_once ROOT_PATH . 'model/connect.php';
    
    abstract class AbstractEventDb extends DB {
        protected string $_querry_str_args = "";
        protected string $_querry_str_coll = "";
        protected array $_querry_args = array();
        protected string $_querry_str = "";
        protected Event|Task|Message $_event; 
        
        abstract public function insert($event, int $h_tree=-1);
        //abstract public function select();

        protected function commiter(int $h = 0): PDOStatement|int|false {
            $result = false;
            if ($h === $_ENV["MAX_TRY"]) {
                log503(__FILE__, __LINE__);
            }
            try {
                $this->_db->beginTransaction();
                
                $query = $this->_db->prepare($this->_querry_str);
                $result = $query->execute($this->_querry_args);
                $this->_db->commit();
                
                
                return $result;
            }
            catch(Exeption $e) {
                $this->_db->rollBack();
                $this->commiter($h +1);
            }
        }

        /**
         * Dans cette methode on gere le repeteur. On l'instancie suivant son type
         * 
         */
        protected function manage_repeater(): void {
            
            if($this->_event->get_repeater() instanceof RepeaterDaily) {
                $repDb = new RepeaterDailyDb();
            }
            else if($this->_event->get_repeater() instanceof RepeaterWeekly) {
                $repDb = new RepeaterWeeklyDb();
            }
            else if($this->_event->get_repeater() instanceof RepeaterMonthly) {
                $repDb = new RepeaterMonthlyDb();
            }
            else if($this->_event->get_repeater() instanceof RepeaterYearly) {
                $repDb = new RepeaterYearlyDb();
            }
            //On gere le repeteur
            $rep_arr = $repDb->insert($this->_event->get_repeater());
            $this->_querry_str_coll = trim($this->_querry_str_coll) . ", " .  $rep_arr["str_coll"];
            $this->_querry_str_args = trim($this->_querry_str_args) . ", " .  $rep_arr["str_args"];

            foreach($rep_arr["args"] as $key => $value) {
                $this->_querry_args[$key] = $rep_arr["args"][$key];
            }
        }
    }

    class EventBaseDb extends DB {
        protected array $_querry_args = array();
        protected string $_querry_str = "";
        
        public function select_by_week(DateTime $monday, User $user) {
            $sunday = $monday->modify("+7 days");
            $events = array();
            $this->_querry_str = "
                SELECT 
                * 
                FROM 
                    (SELECT 
                        * 
                    FROM 
                        timeTable_event 
                    WHERE
                        DATEDIFF(date_begin,:monday) >=0 AND DATEDIFF(:sunday , date_begin) >=0
                        AND (
                            parent IS NULL 
                            OR 
                            parent = -1
                        )
                        AND
                        house = :house
                    UNION 
                    SELECT 
                        * 
                    FROM 
                        timeTable_event 
                    WHERE
                        (
                            repeat_is_for_ever = 1
                            OR 
                            DATEDIFF(repeat_end, :sunday) >=0
                        )
                        AND (
                            parent IS NULL 
                            OR 
                            parent = -1
                        )
                        AND
                        house = :house
                    ) as main
                ORDER BY date_begin;
            ";
            $this->_querry_args = array(
                ":monday" => $monday->format("Y-m-d"),
                ":sunday" => $sunday->format("Y-m-d"),
                ":house" => $user->get_house()
            );
            $result = $this->commiter();
            $fetched = $result->fetchAll(PDO::FETCH_ASSOC);

            for($i = 0; $i < sizeof($fetched); $i++) {
                if($fetched[$i]["date_end"]) { //SI c'est un event ALORS
                    array_push($events, new Event($fetched[$i]));
                    
                }
                else if($fetched[$i]["sentance"]) { //SI c'est un event ALORS
                    array_push($events, new Message ($fetched[$i]));
                }
                else if($fetched[$i]["parent"]) { //SINON SI c'est un message ALORS 
                    array_push($events, new Message ($fetched[$i]));
                }
                //On determine le repeteur si il y en a un
                //$events[$i]->set_repeater(); 
            }
            print_r($events);
        }
        

        private function commiter(int $h = 0): PDOStatement|int|false {
            $result = false;
            if ($h === $_ENV["MAX_TRY"]) {
                log503(__FILE__, __LINE__);
            }
            try {
                $this->_db->beginTransaction();
                
                $query = $this->_db->prepare($this->_querry_str);
                $result = $query->execute($this->_querry_args);
                $this->_db->commit();
                
                
                return $query;
            }
            catch(Exeption $e) {
                $this->_db->rollBack();
                $this->commiter($h +1);
            }
        }
    }

    class EventDb extends AbstractEventDb {
        

        public function insert($event, int $h_tree=0) {
            $this->_event = $event;
            $this->_querry_args = array(
                ":date_begin" => date_format($this->_event->get_date_begin(), 'Y-m-d H:i:s'), 
                ":label" => $this->_event->get_label(),
                ":date_end" => date_format($this->_event->get_date_end(), 'Y-m-d H:i:s'),
                ":description" => $this->_event->get_desc(),
                ":place" => $this->_event->get_place(),
                ":house" => $this->_event->get_user()->get_house()
            );
            //On genere le nom des collones
            $this->_querry_str_coll = "
                date_begin,
                label,
                date_end,
                description,
                place,
                house
            ";
            //On genere le nom des arguments
            $this->_querry_str_args = $this->generate_querry_str_args($this->_querry_str_coll);
            $this->manage_repeater();
            
            //On genere la requette
            $this->_querry_str = "
                INSERT INTO 
                    timeTable_event(" . 
                        trim($this->_querry_str_coll) ."
                    )
                    VALUES(" . 
                        trim($this->_querry_str_args) . "
                    )
            ";
            
            $this->commiter(true);
            //On commit
        }
    }

    class MessageDb extends AbstractEventDb {

        public function insert($event, int $h_tree=0) {
            $rep_arr =array();
            $this->_event = $event;
            $this->_querry_args = array(
                ":date_begin" => date_format($this->_event->get_date_begin(), 'Y-m-d H:i:s'), 
                ":label" => $this->_event->get_label(),
                ":sentance" => $this->_event->get_sentance(),
                ":is_ring" => $this->_event->get_is_ring()? 1 : 0 ,
                ":device" => $this->_event->get_device(),
                ":house" => $this->_event->get_user()->get_house()
            );
            //On genere le nom des collones
            $this->_querry_str_coll = "
                date_begin,
                label,
                sentance,
                is_ring,
                device,
                house
                ";
            //On genere le nom des arguments
            $this->_querry_str_args = $this->generate_querry_str_args($this->_querry_str_coll);
            $this->manage_repeater();
            
            //On genere la requette
            $this->_querry_str = "
                INSERT INTO 
                    timeTable_event(" . 
                        trim($this->_querry_str_coll) ."
                    )
                    VALUES(" . 
                        trim($this->_querry_str_args) . "
                    )
            ";
            
            $this->commiter(true);
            //On commit
        }
    }

    class TaskDb extends AbstractEventDb {
        private array $_querries = array();
        public function insert($event, int $h_tree=0) {
            
            $rep_arr =array();
            if ($h_tree === 0) {
                $this->_event = $event;
            }
            $children = $event->get_children();
            $this->_querry_args = array(
                
                ":label" => $event->get_label(),
                ":description" => $event->get_desc(),
                ":house" => $this->_event->get_user()->get_house()
            );
            //On genere le nom des collones
            $this->_querry_str_coll= "
                label,
                description,
                house
            ";
            //On genere le nom des arguments
            
            if(true) {
                $this->_querry_args[":date_begin"] = date_format($this->_event->get_date_begin(), 'Y-m-d H:i:s');
                $this->_querry_str_coll .= ", date_begin";
                $this->_querry_str_args = $this->generate_querry_str_args($this->_querry_str_coll);
                $this->manage_repeater();
            }
            if($h_tree > 0) {
                $this->_querry_args[":parent"] = null;
                $this->_querry_str_coll = trim($this->_querry_str_coll) . ", parent"; 
                $this->_querry_str_args = $this->generate_querry_str_args($this->_querry_str_coll);
            }
            else {
                $this->_querry_args[":parent"] = -1;
                $this->_querry_str_coll = trim($this->_querry_str_coll) . ", parent"; 
                $this->_querry_str_args = $this->generate_querry_str_args($this->_querry_str_coll);
            }
            //On genere la requette
            array_push($this->_querries, array("
                INSERT INTO 
                    timeTable_event(" . 
                        trim($this->_querry_str_coll) ."
                    )
                    VALUES(" . 
                        trim($this->_querry_str_args) . "
                    )
            ", $this->_querry_args)) ;
            for($i = 0; $i < sizeof($children); $i++) {
                $this->insert($children[$i], $h_tree + 1);
            }
            if($h_tree === 0) {
                $this->commiter();
                return $this->_event;
            }
            
        }

        protected function commiter(int $h = 0): PDOStatement|int|false {
            
            $result = false;
            if ($h === $_ENV["MAX_TRY"]) {
                log503(__FILE__, __LINE__);
            }
            try {
                $this->_db->beginTransaction();
                for($i =0; $i<sizeof($this->_querries); $i++) {
                    
                    $query[$i] = $this->_db->prepare($this->_querries[$i][0]);
                    if($i > 0) {
                        $this->_querries[$i][1][":parent"] = $this->_db->lastInsertId();
                       
                    }
                    if($i === 1) {
                        $this->_event->set_id($this->_db->lastInsertId());
                        
                    } 
                    
                    $query[$i]->execute($this->_querries[$i][1]);
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