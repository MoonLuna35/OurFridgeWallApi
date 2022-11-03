<?php    
    $root = realpath($_SERVER["DOCUMENT_ROOT"]);    
    require_once(ROOT_PATH . "model/timeTable/repeater/Repeater.php");

    abstract class AbstractEvent {
        protected ?int $_id = -1;
        protected ?DateTime $_date_begin;
        protected ?string $_label = "";
        protected RepeaterDaily | RepeaterWeekly | RepeaterMonthly | RepeaterYearly | null $_repeater = null;
        protected ?User $_user =  null;

        //constructeurs
        
        
        protected static function fromInsert(array $data, User $current_user, Event | Message | Task $evt =null): Event | Message | Task {
            //controles
            if(
                static::control_date_begin($data["event"]["date_begin"])
                &&
                static::control_label($data["event"]["label"])
            ) {
                //intialisation
                $evt->_date_begin = new DateTime();
                $evt->_label = $data["event"]["label"];
                $evt->_user = $current_user;
            }
            if(isset($data["repeater"])) { //instanciation de repeteur
                $evt->_repeater = RepeaterUtils::instanciate($data["repeater"]);
            }
            return $evt;
            
        }
        //controls
        protected static function control_id(int $id): bool {
            if (0 > $id) {
                return true;
            }
            else {
                log400(__FILE__, __LINE__);
            }
        }
        protected static function control_date_begin(string $date_begin): bool {
            if (validateDateTime($date_begin)) {
                return true;
            }
            else {
                log400(__FILE__, __LINE__);
            }
        }
        protected static function control_label(string $label): bool {
            if (preg_match(RegExp::REG_ALPHANUM_PONCT ,$label)) {
                return true;
            }
            else {
                log400(__FILE__, __LINE__);
            }
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
        public function get_user(): User  {
			return $this->_user;
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
        public function set_user(User $new_user): void  {
			$this->_user = $new_user;
		}
        public function set_repeater(RepeaterDaily | RepeaterWeekly | RepeaterMonthly | RepeaterYearly $new_repeater): void  {
			$this->_repeater = $new_repeater;
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