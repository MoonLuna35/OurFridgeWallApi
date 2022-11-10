<?php 

    $root = realpath($_SERVER["DOCUMENT_ROOT"]);    
    require_once(ROOT_PATH . "model/timeTable/event/AbstractEvent.php");
    class Task extends AbstractEvent { 
        private string $_description = "";
        private Array $_children = array() ;

        public static function fromInsert(array $data, User $current_user=null, int $h=0): Task {
            $task = new Task();

            if(0 === $h) {
                parent::control_date_begin($data["event"]["date_begin"]);
                if(isset($data["repeater"])) { //instanciation de repeteur
                    $task->_repeater = RepeaterUtils::instantiate($data["repeater"]);
                }
                
                $task->_date_begin = new DateTime($data["event"]["date_begin"]);
            }
            parent::control_label($data["event"]["label"]);
            
            $task->_label = $data["event"]["label"];
            $task->_description = htmlentities($data["event"]["description"]);
            $task->_user = $current_user;
            foreach ($data["event"]["children"] as $child) {
                print_r($h);
                array_push(
                    $task->_children,
                    static::fromInsert($child, $current_user, $h+1)
                );
            }

            return $task;
            
        }

        public static function fromUpdate(array $event, User $current_user=null): Task {
            $task = new Task();
            parent::control_label($event["label"]);
            parent::control_id($event["id"]); 
            
            $task->_id = $event["id"];
            $task->_label = $event["label"];
            $task->_description = htmlentities($event["description"]);
            $task->_user = $current_user;

            return $task;
            
        }

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