<?php 

    $root = realpath($_SERVER["DOCUMENT_ROOT"]);    
    require_once("../../controlers/global.php");
    require_once(ROOT_PATH . "model/timeTable/repeater/Repeater.php");

    abstract class AbstractEvent {
        protected int $_id = -1;
        protected ?DateTime $_date_begin;
        protected string $_label;
        protected RepeaterDaily | RepeaterWeekly | RepeaterMonthly | RepeaterYearly | null $_repeater = null;
        
        public function __construct($event, $repeater=null, $h=-1) {
            
            if(is_array($event)) { //SI l'event est un tableau ALORS
                $event = json_encode($event); //On je convertie le tableau en objet 
                $event = json_decode($event);
            }
            if(isset($event->id)) { //SI l'evenement a un id(il vient d'etre construit depuis la base de donnee) ALORS
                $this->_id = $event->id; //On l'initialise
            }
            
            if(isset($event->date_begin)) { //Si il a une date de debut(ce n'est pas une tache) ALORS 
                $this->_date_begin = $event->date_begin;
            }
            
            $this->_label = $event->label; //On initialise le label

            if(isset($repeater->repeat_patern) && $h < 1 ) { //si il se repete ALORS
                //on defini les classes a instanciees
                if ($repeater->repeat_patern === "daily") { //SI jour ALORS
                    $this->_repeater = new RepeaterDaily($repeater);
                }
                else if ($repeater->repeat_patern === "weekly") { //SI semaine ALORS
                    $this->_repeater = new RepeaterWeekly($repeater);
                }
                else if ($repeater->repeat_patern === "monthly") { //SI mois ALORS
                    $this->_repeater = new RepeaterMonthly($repeater);
                }
                else if ($repeater->repeat_patern === "yearly") { //SI annee ALORS
                    
                    $this->_repeater = new RepeaterYearly($repeater);
                }
                //$this->$_repeater
            }
        }
        
        public static function event_duplicator(Event|Message|Task $event): Array {
            //Dans le cas ou un evenement commence le dimanche soir et se poursuit le lundi
            //On doit afficher la fin de l'event
            
            $events = array($event);
            if($event->get_repeater() instanceof RepeaterDaily) {
                if($event->get_repeater()->get_n_day() < 7) {
                    $i = $event->get_date_begin()->modify('+ ' . $event->get_repeater()->get_n_day() . ' days')->format( 'N' );
                    $i_init = $i;
                    $c = 1;
                    //TANT QUE i est superieur ou = a i init FAIRE
                    while($i >= $i_init) {
                        array_push($events, clone $events[$c - 1]);
                        $events[$c]->set_date_begin($events[$c]->get_date_begin()->modify('+ ' . $event->get_repeater()->get_n_day() . ' days'));  
                        $i = $events[$c]->get_date_begin()->modify('+ ' . $event->get_repeater()->get_n_day() . ' days')->format( 'N' );
                        $c++;
                    }

                }
            }
            else if ($event->get_repeater() instanceof RepeaterWeekly) {
                $repeat_day = $event->get_repeater()->get_repeat_day_array();
                
                $current_day = $event->get_date_begin()->format( 'N' ) - 1;//On prends le jour de la semaine de la date de debut (on est sur qu'il est repete)
                for($i = $current_day + 1; $i < sizeof($repeat_day); $i++) {
                    if($repeat_day[$i]) {
                        $cloned_evt = clone $event;
                        $cloned_evt->set_date_begin($event->get_date_begin()->modify("+" . $i - $current_day . " days")); 
                        array_push($events, $cloned_evt);
                    }
                }
                //On regarde les jours suivant    
            }
            else if ($event->get_repeater() instanceof RepeaterMonthly) {
                $days = $event->get_repeater()->get_days_to_repeat(true);
                $current_day = $event->get_date_begin()->format( 'd' );
                $current_weekDay = $event->get_date_begin()->format( 'N' ) - 1;
                $monday = $event->get_date_begin()->modify("-$current_weekDay days") -> format("d");
                $sunday = $event->get_date_begin()->modify("+ " . 6 - $current_weekDay . " days") -> format("d");

                print_r($sunday);
                foreach($days as $value) {
                    if($value >= $monday && $value <= $sunday ) {
                        $cloned_evt = clone $event;
                        $cloned_evt->set_date_begin($event->get_date_begin()->modify("+" . $value - $current_day . " days")); 
                        array_push($events, $cloned_evt);
                    }
                }
            }
            return $events;
        }

        public function __clone() {
            foreach($this as $key => $val) {
                
                if (is_object($val) || (is_array($val))) {
                    $this->{$key} = unserialize(serialize($val));
                }
            }
        }

        protected function controlEvent($event, $h=-1) {
            ($h  . " control  ". __LINE__ ."<br/>");
            if( $h < 1 //Si ce 'est pas une sous tache 
                &&
                isset($event->date_begin) //que l'evenement a une date de debut
                &&
                isset($event->time_begin) //une heure de debut
                &&
                isset($event->label)//Un label
                &&
                "" !== trim($event->label) //que le label n'est pas vide
                &&
                validateDate($event->date_begin . " " . $event->time_begin)
                //que la date de debut est valide ALORS 
            ) {
                
                $event->date_begin = new DateTime($event->date_begin . $event->time_begin); //On instancie la date de debut
                unset($event->time_begin); //On supprime l'heure de debut(maintenant elle est dans la date )
                $event->label = htmlentities($event->label);//On securise le label
                
                return $event;
            }
            else if( //SI c'est une sous tache (il n'y a pas de date de debut)
                $h >= 1 
                &&
                isset($event->label)
                &&
                "" !== trim($event->label) 
            ) { //ALORS 
                unset($event->date_begin); //On supprime cette derniere
                unset($event->time_begin);
                $event->label = htmlentities($event->label); //On securise le label
                
                return $event;
            }
            
            else { //SINON
                log400(__FILE__, __LINE__);
            }
            
            return false;
        }
        //getters
        public function get_id(): int  {
			return $this->_id;
		}
        public function get_date_begin(): DateTime  {
			return clone $this->_date_begin;
		}
        public function get_label(): string  {
			return $this->_label;
		}
        public function get_repeater(): mixed {
            return $this->_repeater;
        }

        //setters
		public function set_id(int $new_id): void  {
			$this->_id = $new_id;
		}
		public function set_date_begin(DateTime $new_date_begin): void  {
			$this->_date_begin = $new_date_begin;
		}
		public function set_label(string $new_label): void  {
			$this->_label = $new_label;
		}

        //to array
        public function to_array($repeater=true): Array {
            $arr = array(
                "id" => $this->_id,
                
                "label" => $this->_label,
            );
            if(isset($this->_repeater) && $repeater) {
               $arr["repeater"] = $this->_repeater->to_array(); 
            }
            if(isset($this->_date_begin)) {
                $arr["date_begin"] = $this->_date_begin;
            }
            return $arr;
        }

        
    }

    class Event extends AbstractEvent {
        private DateTime $_date_end;
        private string $_desc; 
        protected string $_place;
        
        public function __construct($event, $repeater=null) { 
            if(is_array($event)) {
                $event = json_encode($event);
                $event = json_decode($event);
            }
            $event = $this->controlEvent($event);
            
            parent::__construct($event, $repeater);

            $this->_date_end = $event->date_end;
            $this->_desc = $event->desc;
            $this->_place = $event->place;
        }

        protected function controlEvent($event, $h=-1) {
            $event = parent::controlEvent($event);
            if(
                isset($event->date_end)
                &&
                isset($event->time_end)
                &&
                isset($event->desc)
                &&
                isset($event->place)
                &&
                validateDate($event->date_end . " " . $event->time_end)
    
            ) {
                $event->date_end = new DateTime($event->date_end . $event->time_end);
                unset($event->time_end);
                $event->label = htmlentities($event->label);

                return $event;
            }
            else {
                log400(__FILE__, __LINE__); 
            }
            return false;
        }

        public function get_date_end(): DateTime  {
			return $this->_date_end;
		}
        public function get_desc(): string  {
			return $this->_desc;
		}
        public function get_place(): string  {
			return $this->_place;
		}
		public function set_date_end(Date $new_date_end): void  {
			$this->_date_end = $new_date_end;
		}
		public function set_desc(string $new_desc): void  {
			$this->_desc = $new_desc;
		}
		public function set_place(string $new_place): void  {
			$this->_place = $new_place;
		}

        public function to_array($repeater=true): Array {
            $arr = parent::to_array($repeater);
            $arr["date_end"] = $this->_date_end;
            $arr["desc"] = $this->_desc;
            $arr["place"] = $this->_place;

            return $arr;
        }
    }

    class Message extends AbstractEvent {
        private string $_device; 
        private string $_sentance;
        private bool $_is_ring; 
        
        public function __construct($event, $repeater=null) {
            if(is_array($event)) {
                $event = json_encode($event);
                $event = json_decode($event);
            }
            $event = $this->controlEvent($event);
            parent::__construct($event, $repeater);
            $this->_device = $event->device; 
            $this->_sentance = $event->sentance;
            $this->_is_ring = $event->is_ring;
        }

        protected function controlEvent($event, $h=-1) {
            $event = parent::controlEvent($event);
            if(
                isset($event->device)
                &&
                isset($event->sentance)
                &&
                isset($event->is_ring)
                &&
                in_array($event->device, VOCAL_ASSISTANT)
                &&
                is_bool($event->is_ring)
            ) {
                $event->sentance = htmlentities($event->sentance);
                return $event;
            }
            else { 
                log400(__FILE__, __LINE__);
            }  
        }

        public function get_device(): string  {
			return $this->_device;
		}
        public function get_sentance(): string  {
			return $this->_sentance;
		}
        public function get_is_ring(): bool  {
			return $this->_is_ring;
		}
        public function set_device(string $new_device): void  {
			$this->_device = $new_device;
		}
		public function set_sentance(string $new_sentance): void  {
			$this->_sentance = $new_sentance;
		}
		public function set_is_ring(bool $new_is_ring): void  {
			$this->_is_ring = $new_is_ring;
		}
        public function to_array($repeater=true): Array {
            $arr = parent::to_array($repeater);
            $arr["device"] = $this->_device; 
            $arr["sentance"] = $this->_sentance;
            $arr["is_ring"] = $this->_is_ring;
            return $arr;
        }
    }

    class Task extends AbstractEvent { 
        private string $_desc;
        private Array $_children = array();

        public function __construct($event, $repeater=null, $h=0) { 
            if(is_array($event)) {
                $event = json_encode($event);
                $event = json_decode($event);
            }
            ($h  . " construct task ". __LINE__ ."<br/>");
            $event = parent::controlEvent($event, $h);//On controle la tache courante
            $event = $this->controlEvent($event, $h);
            parent::__construct($event, $repeater, $h);
            $this->_desc = $event->desc;
            for ($i = 0; $i < sizeof($event->children); $i++) {
                $this->_children[$i] = new Task($event->children[$i], $repeater, $h + 1);
            }

        }

        protected function controlEvent($event, $h=0) {
            ($h  . " control task ". __LINE__ ."<br/>");
            if(
                isset($event->desc)
                &&
                isset($event->children)
                &&
                is_array($event->children)
               
            ) {
                $event->desc = htmlentities($event->desc);
                
                for ($i = 0; $i < sizeof($event->children); $i++) { //POUR TOUT enfant FAIRE
                    $event->children[$i] = $this->controlEvent($event->children[$i], $h + 1); //On controle chaque sous taches
                }

                return $event;
            }
            else { 
                log400(__FILE__, __LINE__);
            }  
        }

		public function get_desc(): string  {
			return $this->_desc;
		}
        public function get_children(): Array  {
			return $this->_children;
		}
		public function set_desc(string $new_desc): void  {
			$this->_desc = $new_desc;
		}
		public function set_children(Array $new_children): void  {
			$this->_children = $new_children;
		}
        public function to_array($repeater=true, $h=0): Array {
            $arr = parent::to_array($repeater);
            if ($h > 0) {
                unset($arr["date_begin"]);
            }
            $children_arr = array();
            for($i = 0; $i < sizeof($this->_children); $i++) {
                array_push($children_arr, $this->_children[$i]->to_array($repeater, $h + 1)); 
            }
            $arr["desc"] = $this->_desc;
            $arr["children"] = $children_arr;
            return $arr;
        }

        
    }