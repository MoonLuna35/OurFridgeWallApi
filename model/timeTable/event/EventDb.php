<?php 
    require_once ROOT_PATH . 'model/timeTable/event/Event.php';
    require_once ROOT_PATH . 'model/timeTable/repeater/RepeaterDb.php';
    require_once ROOT_PATH . 'model/connect.php';
    
    abstract class AbstractEventDb extends DB {
        protected string $_querry_str_args = "";
        protected string $_querry_str_coll = "";
        protected Event|Task|Message $_event; 
        
        abstract public function insert($event, int $h_tree=-1);
        abstract public function update($event, int $h_tree=-1);

        

        protected function prepare_to_update():void {
            $this->_querry_str_coll = "
                date_begin = :date_begin,
                label = :label,
                date_end = :date_end,
                description = :description,
                place = :place,
                device = :device,
                sentance = :sentance,
                is_ring = :is_ring,
                house = :house,
            ";

            
            
            $this->_querry_args[":id"] = $this->_event->get_id();
            $this->_querry_args[":date_begin"] = date_format($this->_event->get_date_begin(), 'Y-m-d H:i:s');
            $this->_querry_args[":house"] = $this->_event->get_user()->get_house();
            $this->_querry_args[":label"] = $this->_event->get_label();
            $this->_querry_args[":date_end"] = NULL;
            $this->_querry_args[":description"] = NULL;
            $this->_querry_args[":place"] = NULL;
            $this->_querry_args[":device"] = NULL;
            $this->_querry_args[":sentance"] = NULL;
            $this->_querry_args[":is_ring"] = NULL;
            $this->_querry_args[":parent"] = NULL;

            $rep_arr = RepeaterBaseDB::prepare_to_update($this->_querry_str_coll, $this->_querry_args);
            $this->_querry_str_coll = $rep_arr[0];
            $this->_querry_args = $rep_arr[1];
            $this->manage_repeater(true);
            $this->_querry_str = "
                UPDATE  
                    timeTable_event
                SET 
                    " .$this->_querry_str_coll . "
                WHERE
                    id = :id 
                    AND
                    house = :house
            ";
        }


        protected function manage_repeater(bool $is_for_update=false): void {
            print_r($this->_event->get_repeater() === null);
            
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
            if(isset($repDb) && !$is_for_update) {
                //On gere lprint_r($this->_event);e repeteur
                $rep_arr = $repDb->insert($this->_event->get_repeater());
                $this->_querry_str_coll = trim($this->_querry_str_coll) . ", " .  $rep_arr["str_coll"];
                $this->_querry_str_args = trim($this->_querry_str_args) . ", " .  $rep_arr["str_args"];
                foreach($rep_arr["args"] as $key => $value) {
                    $this->_querry_args[$key] = $rep_arr["args"][$key];
                }
            } 
            else if(isset($repDb) && $is_for_update) {
                $this->_querry_args = $repDb->update($this->_event->get_repeater(), $this->_querry_args);
            }   
        }
    }

    class EventBaseDb extends DB {
        protected array $_querry_args = array();
        protected string $_querry_str = "";
        
        

        public function select_by_week(DateTime $monday, User $user) {
            $sunday = clone $monday;
            $sunday = $sunday->modify("+7 days");
            
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
                        DATEDIFF(date_begin,:monday) >=0 
                        AND 
                        DATEDIFF(:sunday , date_begin) >=0
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
                            DATEDIFF(repeat_date_end, :sunday) >=0
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
            for($i = 0 ; $i < sizeof($fetched); $i++) {
                
                $repeater = RepeaterBase::generate_repeater($fetched[$i]);
                
                if($fetched[$i]["date_end"]) { //SI c'est un event ALORS
                    array_push($events, new Event($fetched[$i]));
                }
                else if($fetched[$i]["sentance"]) { //SI c'est un event ALORS
                    
                    array_push($events, new Message ($fetched[$i]));
                }
                else if($fetched[$i]["parent"]) { //SINON SI c'est un message ALORS 
                    array_push($events, new Task ($fetched[$i]));
                }
                
                if(isset($repeater)) {
                    $repeater->set_event($events[sizeof($events) - 1]);
                    $interval = date_diff($events[sizeof($events) - 1]->get_date_begin(), $monday);
                    $interval = $interval->format('%a');
                    if($interval >= 0) {
                        array_pop($events); 
                    }
                    $repeater->repeat($monday, $events);
                }
            }
            usort($events, [EventBase::class, "compartEventByDate"]);
            return $events;
            
        }

        public function select_by_id(int $id, User $user): Event|Message|Task|false {
            $event; 
            $this->_querry_str = "
                SELECT
                    * 
                FROM  
                    timeTable_event
                WHERE 
                    id = :id 
                    AND
                    house = :house  
            ";

            $this->_querry_args = array(
                ":id" => $id,
                ":house" => $user->get_house()
            );
            $result = $this->commiter();
            if ($result->rowCount() === 1) {
                $fetched = $result->fetchAll(PDO::FETCH_ASSOC);
                $repeater = $this->generate_repeater($fetched[0]);

                if($fetched[0]["date_end"]) { //SI c'est un event ALORS 
                    $event = new Event($fetched[0]);
                }
                else if($fetched[0]["sentance"]) { //SI c'est un event ALORS
                    
                    $event = new Message ($fetched[0]);
                }
                else if($fetched[0]["parent"]) { //SINON SI c'est un message ALORS 
                    $event =  new Task ($fetched[0]);
                }
                $event->set_repeater($repeater);
                return $event;
            }
            else {
                return false;
            }
            
        }

        public function delete_by_id(int $event): bool {
            $this->_querry_str = "
                DELETE FROM  
                    timeTable_event
                WHERE (
                        id = :id
                        OR 
                        racine = :id
                    )
                    AND
                        house = :house
            ";

            $this->_querry_args = array(
                ":id" => $event,
                ":house" => $user->get_house()
            );
            $result = $this->commiter();
            if ($result->rowCount() > 0) {
                return true;
            }
            else {
                return false;
            }
        }
        

        
    }

    class EventDb extends AbstractEventDb {
        
        public function update($event, int $h_tree=0) {
            $this->_event = $event;

            $this->prepare_to_update();
            $this->_querry_args[":date_end"] = date_format($this->_event->get_date_end(), 'Y-m-d H:i:s');
            $this->_querry_args[":description"] = $this->_event->get_description();
            $this->_querry_args[":place"] = $this->_event->get_place();
            $this->_querry_args[":house"] = $this->_event->get_user()->get_house();
            
            $rep = $this->commiter();
            if($rep->rowCount() === 1) {
                return true;
            }
            return false;
        }
        public function insert($event, int $h_tree=0) {
            $this->_event = $event;
            
            $this->_querry_args[":date_begin"] = date_format($this->_event->get_date_begin(), 'Y-m-d H:i:s'); 
            $this->_querry_args[":label"] = $this->_event->get_label();
            $this->_querry_args[":date_end"] = date_format($this->_event->get_date_end(), 'Y-m-d H:i:s');
            $this->_querry_args[":description"] = $this->_event->get_description();
            $this->_querry_args[":place"] = $this->_event->get_place();
            $this->_querry_args[":house"] = $this->_event->get_user()->get_house();
            
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
            $com = $this->commiter(true);
            $this->_event->set_id($com["id"]);
            //On commit
        }
        
    }

    class MessageDb extends AbstractEventDb {

        public function update($event, int $h_tree=0): bool {
            $this->_event = $event;
            $this->prepare_to_update();

            $this->_querry_args[":sentance"] = $this->_event->get_sentance();
            $this->_querry_args[":is_ring"] = $this->_event->get_is_ring()? 1 : 0;
            $this->_querry_args[":device"] = $this->_event->get_device();
            
            $rep = $this->commiter();
            if($rep->rowCount() === 1) {
                return true;
            }
            else {
                return false;
            }
        }
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
            
            $com  = $this->commiter(true);
            $this->_event->set_id($com["id"]);
            //On commit
        }
    }

    class TaskDb extends AbstractEventDb {
        private array $_querries = array();
        private int $_id_root = -1;

        /**
         * DANS CETTE METHODE  : 
         * 
         * On separe 2 mdification
         *  
         * -Une modification d'un atribut d'une/des feuille ou d'un/des noeud sont modifier 
         * -Une modification structurelle de l'abre en lui meme
         *       suppression de l'ancien arbre puis ajout du nouveau
         * 
         * 
         * 
         */

        public function update($event, int $h_tree = -1){

        }
        public function update_leafs(array $tasks) {
            $this->_querries["args"] = array();
            $this->_querries["body"] = array();
            //attributs principaux
            foreach($tasks as $task) {
                array_push($this->_querries["args"], array(
                    ":label" => $task->get_label(),
                    ":description" => $task->get_description(),
                    ":id" => $task->get_id(),
                    ":house" => $task->get_user()->get_house()
                ));
                array_push($this->_querries["body"], "
                    UPDATE
                        timeTable_event
                    SET 
                        label = :label, 
                        description = :description
                    WHERE
                        id = :id
                        AND 
                        house = :house
                ");
            }
            //date

            //repeteur
            
            

            $rep = $this->commit_leafs(true);
            
            return $rep;
        }

        private function commit_leafs(int $h=0) {
            if ($h === $_ENV["MAX_TRY"]) {
                log503(__FILE__, __LINE__);
            }
            try {
                $this->_db->beginTransaction();

                for($i = 0; $i < sizeof($this->_querries["body"]); $i++) {
                    $query = $this->_db->prepare($this->_querries["body"][$i]);
                    $query->execute($this->_querries["args"][$i]);
                    
                    if($query->rowCount() !== 1) {
                        return false;
                    }
                }

                $result = $this->_db->commit();
                return true;
            }
            catch(Exeption $e) {
                $this->_db->rollBack();
                $this->commit_leafs($h +1);
            }
        }
        public function insert($event, int $h_tree=0, int $h=0, int $parent=-1) {
            $this->_querry_args = array(); //Le tableau qui contiens les argument de la requette r
            
            if($h===0) { //SI on est sur la racine
                //On initialise le tableau qui contiendra les trucs utiles
                $this->_querries["body"] = array(); //Le corps de la requette pour chaque taches
                $this->_querries["args"] = array(); //ses argument
                $this->_querries["parent_indice"] = array(); //L'indice de son parent dans le tableau de requette
                $this->_querries["task"] = array(); //Une reference vers la tache en elle meme
                $this->_event = $event; //L'arbre entier
            }
            
            //Le corps de la requette r
            $this->_querry_str_coll = "
                label, 
                description, 
                parent,
                house,
                date_begin,
                racine
            ";

            //On assigne les argument de la requette r
            $this->_querry_args[":label"] = $event->get_label(); 
            $this->_querry_args[":description"] = $event->get_description();
            $this->_querry_args[":house"] = $event->get_user()->get_house();
            $this->_querry_args[":date_begin"] = date_format($event->get_date_begin(), 'Y-m-d H:i:s');
            $this->_querry_args[":parent"] = -1;
            $this->_querry_args[":racine"] = -1;

            //On genere la chaine qui contiens les arguments
            $this->_querry_str_args = $this->generate_querry_str_args($this->_querry_str_coll);
            if($h===0) { //SI on est sur la racine
                $this->manage_repeater(); //On gere le repeteur
                array_push($this->_querries["parent_indice"], -1); //C'est la racine elle n'a donc pas de parent 
            }
            else { //SINON
                array_push($this->_querries["parent_indice"], $parent); //On stock, l'indice du parent
            }

            //On ajoute la requette r au tableau de requette
            array_push($this->_querries["body"],
                    "INSERT INTO 
                        timeTable_event(" . 
                            trim($this->_querry_str_coll) ."
                        )
                    VALUES(" . 
                        trim($this->_querry_str_args) . "
                        )
                        "
            );

            array_push($this->_querries["args"], $this->_querry_args);
            array_push($this->_querries["task"], $event);
            
            $indice = sizeof($this->_querries["args"]) - 1; //L'indice de la requette r
            
            $children = $event->get_children(); //On prends les enfants
            
            foreach($children as $child) { //POUR TOUT enfant FAIRE
                $child->set_date_begin($event->get_date_begin()); //On modifie la date de la sous tache
                $this->insert($child, $h_tree + 1, $h + 1, $indice);//On insert l'enfant. 
            }
            

            if($h === 0) { //SI on est sur la racine 
                $this->commiter_insert(); //On commit
            }
            
            return $event;
            
        }

        private function commiter_insert(int $h = 0): bool {
            
            $result = false;
            if ($h === $_ENV["MAX_TRY"]) {
                log503(__FILE__, __LINE__);
            }
            try {
                $racine = -1;
                $this->_db->beginTransaction();
                //POUR TOUTE requette r correspondant a la tache t FAIRE  
                for($i = 0; $i < sizeof($this->_querries["args"]); $i++) {
                    //On prepare la requette
                    $query = $this->_db->prepare($this->_querries["body"][$i]);
                    if($i > 0) { //SI on n'est pas a la racine ALORS
                        //On prends l'id du parent
                        $this->_querries["args"][$i][":parent"] = $this->_querries["task"][$this->_querries["parent_indice"][$i]]->get_id();  
                        $this->_querries["args"][$i][":racine"] = $racine;
                    }
                    
                    //On execute la requette
                    $query->execute($this->_querries["args"][$i]);
                    if($i === 0) { //SI on est dans la racine
                        $racine = $this->_db->lastInsertId();
                    }
                    //On sauve l'id la tache t
                    $this->_querries["task"][$i]->set_id($this->_db->lastInsertId());
                }
                //On commit
                $result = $this->_db->commit();
                return true; //On renvoie la 
            }
            catch(Exeption $e) {
                $this->_db->rollBack();
                $this->commiter_insert($h +1);
            }
        }

		
    }