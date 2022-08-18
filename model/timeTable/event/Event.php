<?php 
    require_once("../../controlers/global.php");

    abstract class AbstractEvent {
        protected int $_id = -1;
        protected ?DateTime $_date_begin;
        protected string $_label;
        protected RepeaterDaily | RepeaterWeekly | RepeaterMonthly | RepeaterYearly | null $_repeater = null;
        
        public function __construct($event, $h=-1) {
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

            if(isset($event->repeater)) { //si il se repete ALORS
                //on defini les classes a instanciees
                if (isset($event->repeater->n_day)) { //SI jour ALORS

                }
                else if (isset($event->repeater->n_week)) { //SI semaine ALORS

                }
                else if (isset($event->repeater->n_month)) { //SI mois ALORS

                }
                else if (isset($event->repeater->n_year)) { //SI annee ALORS

                }
                //$this->$_repeater
            }
        }
        


        protected function controlEvent($event, $h=-1) {
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
                
                header('HTTP/1.1 400 Bad Request'); //On renvoie une erreur
                exit; 
            }
            return false;
        }
        //getters
        public function get_id(): int  {
			return $this->_id;
		}
        public function get_date_begin(): Date  {
			return $this->_date_begin;
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
		public function set_date_begin(Date $new_date_begin): void  {
			$this->_date_begin = $new_date_begin;
		}
		public function set_label(string $new_label): void  {
			$this->_label = $new_label;
		}

        //to array
        public function to_array(): Array {
            $arr = array(
                "id" => $this->_id,
                "date_begin" => $this->_date_begin,
                "label" => $this->_label,
            );
            if(isset($this->_repeater)) {
               $arr = array_push($arr, ["repeater" => 'this->_repeater->to_array()']); 
            }
            return $arr;
        }
    }

    class Event extends AbstractEvent {
        private DateTime $_date_end;
        private string $_desc; 
        protected string $_place;
        
        public function __construct($event) { 
            if(is_array($event)) {
                $event = json_encode($event);
                $event = json_decode($event);
            }
            $event = $this->controlEvent($event);
            
            parent::__construct($event);

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
                header('HTTP/1.1 400 Bad Request');
                exit; 
            }
            return false;
        }

        public function get_date_end(): Date  {
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

        public function to_array(): Array {
            $arr = parent::to_array();
            return array_push($arr, [
                "date_end" => $this->_date_end,
                "desc" => $this->$_desc,
                "place" => $this->$_place
            ]);
        }
    }

    class Message extends AbstractEvent {
        private string $_device; 
        private string $_sentance;
        private bool $_is_ring; 
        
        public function __construct($event) {
            if(is_array($event)) {
                $event = json_encode($event);
                $event = json_decode($event);
            }
            $event = $this->controlEvent($event);
            parent::__construct($event);
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
                header('HTTP/1.1 400 Bad Request');
                exit;
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
        public function to_array(): Array {
            $arr = parent::to_array();
            return array_push($arr, [
                "device" => $this->_device, 
                "sentance" => $this->_sentance,
                "is_ring" => $this->_is_ring
            ]);
        }
    }

    class Task extends AbstractEvent { 
        private string $_desc;
        private Array $_children;

        public function __construct($event, $h=0) { 
            if(is_array($event)) {
                $event = json_encode($event);
                $event = json_decode($event);
            }
            $this->controlEvent($event, $h);
            parent::__construct($event);
            $this->_desc = $event->desc;
            for ($i = 0; $i < sizeof($event->children); $i++) {
                $this->_children[$i] = new Task($event->children[$i], $h + 1);
            }

        }

        protected function controlEvent($event, $h=-1) {
            $event = parent::controlEvent($event, $h);//On controle la tache courante
            if(
                isset($event->desc)
                &&
                isset($event->children)
                &&
                is_array($event->children)
               
            ) {
                $event->desc = htmlentities($event->desc);
                for ($i = 0; $i < sizeof($event->children); $i++) { //POUR TOUT enfant FAIRE
                    $this->controlEvent($event->children[$i], $h + 1); //On controle chaque sous taches
                }

                return $event;
            }
            else { 
                header('HTTP/1.1 400 Bad Request');
                exit;
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
        public function to_array($h=0): Array {
            $arr = parent::to_array();
            if ($h > 0) {
                unset($arr["date_begin"]);
            }
            $children_arr = array();
            for($i = 0; $i < sizeof($this->children); $i++) {
                $children_arr = array_push($children_arr, $this->children[$i]->to_array($h + 1)); 
            }
            return array_push($arr, [
                "desc" => $this->_desc,
                "children" => $children_arr
            ]);
        }

        
    }