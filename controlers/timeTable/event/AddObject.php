<?php
    require_once ROOT_PATH . "model/timeTable/event/Event.php";
    require_once ROOT_PATH . "model/timeTable/event/EventDb.php";
    require_once ROOT_PATH . "model/timeTable/event/EventUtils.php";
    class AddEvent {
        private Event | Message | Task $_event;
        public function __construct($current_user) {
            $event_var; 
            $postdata = file_get_contents("php://input");

            if(!isset($postdata) || empty($postdata)) { 
            
                log400(__FILE__, __LINE__); 
            }
            
            else {
                
                $request = json_decode($postdata, true);
                $data = $request["data"];
                
                $this->_event = EventUtils::instanciateFromInsert($data, $current_user);
            }
        }

        public function add() { 
            
            if($this->_event instanceof Event) {
                $evtDb = new EventDb();
                $evtDb->insert($this->_event);
            }
            else if($this->_event instanceof Message) {
                $evtDb = new MessageDb();
                $evtDb->insert($this->_event);
            }
            else if($this->_event instanceof Task) {
                $evtDb = new TaskDb();
                
                $this->_event = $evtDb->insert($this->_event);
            }
            else {
                log500(__FILE__, __LINE__);
            }
        }

        public function get_event_array () {
            $events = EventUtils::event_duplicator($this->_event);
            $evt_arr = array();
            foreach($events as $evt) {
                array_push($evt_arr, $evt->to_array(false));
            }
            return $evt_arr;
        }
    }


