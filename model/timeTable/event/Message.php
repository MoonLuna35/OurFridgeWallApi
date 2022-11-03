<?php 

    $root = realpath($_SERVER["DOCUMENT_ROOT"]);    
    require_once(ROOT_PATH . "model/timeTable/event/AbstractEvent.php");
    
    class Message extends AbstractEvent {
        private string $_device; 
        private string $_sentance;
        private bool $_is_ring; 
        
        public function __construct($event, $user=null, $repeater=null, bool $is_for_update=false) {
            if(is_array($event)) {
                $d = new DateTime($event["date_begin"]);
                $event["is_ring"] = $event["is_ring"] === 1 ? true : false;
                $event["time_begin"] = $d->format("H:i");
                $event["date_begin"] = $d->format("Y-m-d");

                $event = json_encode($event);
                $event = json_decode($event);
            }
            $event = $this->controlEvent($event, $is_for_update);
            parent::__construct($event, $user, $repeater);
            $this->_device = $event->device; 
            $this->_sentance = $event->sentance;
            $this->_is_ring = $event->is_ring;
        }

        protected function controlEvent($event, bool $for_update=false, $h=-1) {
            $event = parent::controlEvent($event, $for_update);
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
                //print_r($event);
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