<?php

require_once ROOT_PATH . "model/timeTable/event/EventUtils.php";

class ModifyEvent {
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
            $this->_event = EventUtils::instanciateFromUpdate($data, $current_user);
                    
        }
    }

    public function update(): bool { 
        
        if($this->_event instanceof Event) {
            $evtDb = new EventDb();
            
            return $evtDb->update($this->_event);
        }
        else if($this->_event instanceof Message) {
            $evtDb = new MessageDb();
            return $evtDb->update($this->_event);
        }

        else {
            log500(__FILE__, __LINE__);
        }
    }

    public function get_event_array () {
      
        $evt_arr = array();
        foreach($events as $evt) {
            array_push($evt_arr, $evt->to_array(false));
        }
        return $evt_arr;
    }
}