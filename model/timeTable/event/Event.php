<?php 

$root = realpath($_SERVER["DOCUMENT_ROOT"]);    
require_once(ROOT_PATH . "model/timeTable/event/AbstractEvent.php");

    //classes
    class Event extends AbstractEvent {
        private DateTime $_date_end;
        private string $_description; 
        protected string $_place;
        
        //constructeurs
        private static function constructor_base(array $data, User $current_user): Event {
            $evt = new Event();  

            static::control_date_end($data["event"]["date_end"]);

            $evt->_date_end = new DateTime($data["event"]["date_end"]);
            $evt->_description = htmlentities($data["event"]["description"]);
            $evt->_place = htmlentities($data["event"]["place"]);

            return $evt;
        }

        public static function fromInsert(array $data, User $current_user): Event {
            $evt = static::constructor_base($data, $current_user);
            parent::fromInsertBase($data, $current_user, $evt); 

            return $evt;

        }

        public static function fromUpdate(array $data, User $current_user): Event {
            $evt = static::constructor_base($data, $current_user);
            parent::fromUpdateBase($data, $current_user, $evt); 

            return $evt;
        }

        public static function fromDelete(int $id, User $current_user): Event {
            parent::control_id($id); 

            $evt = new Event(); 
            $evt->_id = $id;
            $evt->_user = $current_user; 
            
            return $evt; 
        }

        //controleurs
        private static function control_date_end(string $date_end) {
            if(validateDateTime($date_end)) { 
                return true; 
            }
            else {
                log400(__FILE__, __LINE__);
            }
        } 
        

        public function get_date_end(): DateTime  {
			return clone $this->_date_end;
		}
        public function get_description(): string  {
			return $this->_description;
		}
        public function get_place(): string  {
			return $this->_place;
		}
		public function set_date_end(DateTime $new_date_end): void  {
			$this->_date_end = $new_date_end;
		}
		public function set_description(string $new_description): void  {
			$this->_description = $new_description;
		}
		public function set_place(string $new_place): void  {
			$this->_place = $new_place;
		}

        public function to_array($repeater=true): Array {
            $arr = parent::to_array($repeater);
            $arr["date_end"] = $this->_date_end;
            $arr["description"] = $this->_description;
            $arr["place"] = $this->_place;

            return $arr;
        }
    }

 
    


    