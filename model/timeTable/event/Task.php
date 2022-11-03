<?php 

    $root = realpath($_SERVER["DOCUMENT_ROOT"]);    
    require_once(ROOT_PATH . "model/timeTable/event/AbstractEvent.php");
    class Task extends AbstractEvent { 
        private string $_description = "";
        private ?Array $_children;


		public function get_description(): string  {
			return $this->_description;
		}
        public function get_children(): Array  {
			return $this->_children;
		}
		public function set_description(string $new_description): void  {
			$this->_description = $new_description;
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
            $arr["description"] = $this->_description;
            $arr["children"] = $children_arr;
            return $arr;
        }

        

		
    }