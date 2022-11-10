<?php 

    $root = realpath($_SERVER["DOCUMENT_ROOT"]);    
    require_once(ROOT_PATH . "model/timeTable/event/AbstractEvent.php");
    
    class Message extends AbstractEvent {
        private string $_device; 
        private string $_sentance;
        private bool $_is_ring; 

        private static function constructor_base(array $data, User $current_user): Message {
            $evt = new Message();
            static::control_device($data["event"]["device"]);

            $evt->_device = $data["event"]["device"];
            $evt->_sentance = htmlentities($data["event"]["sentance"]);
            $evt->_is_ring = $data["event"]["is_ring"];
            
            return $evt;
        }
        
        public static function fromInsert(array $data, User $current_user): Message {
            $evt = static::constructor_base($data, $current_user);
            parent::fromInsertBase($data, $current_user, $evt); 

            return $evt;
        }

        public static function fromUpdate(array $data, User $current_user): Message {
            $evt = static::constructor_base($data, $current_user);
            parent::fromUpdateBase($data, $current_user, $evt); 

            return $evt;
        }
        
        private static function control_device(string $device): bool {
            if (!in_array($device, VOCAL_ASSISTANT)) { 
                log400(__FILE__, __LINE__);
            }
            return true;
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