<?php 
    

    class EventBase {
        protected int $_id;
        protected Date $_date_begin;
        protected string $_label;
        protected RepeaterDaily | RepeaterWeekly | RepeaterMonthly | RepeaterYearly | null $_repeater;
        
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
		public function set_id(int $new_id): void  {
			$this->_id = $new_id;
		}
		public function set_date_begin(Date $new_date_begin): void  {
			$this->_date_begin = $new_date_begin;
		}
		public function set_label(string $new_label): void  {
			$this->_label = $new_label;
		}
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
    class Event extends EventBase {
        private Date $_date_end;
        private string $_desc; 
        protected string $_place;
        
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
    class Message extends EventBase {
        private string $_device; 
        private string $_sentance;
        private bool $_ring; 

        public function get_device(): string  {
			return $this->_device;
		}
        public function get_sentance(): string  {
			return $this->_sentance;
		}
        public function get_ring(): bool  {
			return $this->_ring;
		}
        public function set_device(string $new_device): void  {
			$this->_device = $new_device;
		}
		public function set_sentance(string $new_sentance): void  {
			$this->_sentance = $new_sentance;
		}
		public function set_ring(bool $new_ring): void  {
			$this->_ring = $new_ring;
		}
        public function to_array(): Array {
            $arr = parent::to_array();
            return array_push($arr, [
                "device" => $this->_device, 
                "sentance" => $this->_sentance,
                "ring" => $this->_ring
            ]);
        }
        

        
    }

    class Task extends EventBase { 
        private string $_desc;
        private Array $_children;

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