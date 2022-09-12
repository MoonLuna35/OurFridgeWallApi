<?php

require "../../init.php";
require_once ROOT_PATH . "user/is-loged.php";
require_once ROOT_PATH . "model/timeTable/event/Event.php";
require_once ROOT_PATH . "model/timeTable/event/EventDb.php";

class ModifyEvent {
    private Event | Message | Task $_event;
    public function __construct($current_user) {
        $event_var; 
        $postdata = file_get_contents("php://input");

        if(!isset($postdata) || empty($postdata)) { 
        
            log400(__FILE__, __LINE__); 
        }
        
        else {
            
            $request = json_decode($postdata);
            //controle de l'evenement
            
            if(isset($request->data->type)) {
                 
                switch ($request->data->type) {
                    case "event": {
                        if(isset($request->data->repeater)) {
                            $this->_event = new Event($request->data->event, $current_user, $request->data->repeater, true);
                            
                        }
                        else {
                            $this->_event = new Event($request->data->event, $current_user, null, true);
                        }
                    }break;
                    case "voice_reminder": {
                        
                        if(isset($request->data->repeater)) {
                            //print_r($request);
                            $this->_event = new Message($request->data->event, $current_user, $request->data->repeater, true);
                        }
                        else {
                            $this->_event = new Message($request->data->event, $current_user, null, true);
                        }
                    }break;
                    case "task": {
                        
                        if(isset($request->data->repeater)) {
                            $this->_event = new Task($request->data->event, $current_user, $request->data->repeater, true);
                        }
                        else {
                            $this->_event = new Task($request->data->event, $current_user, null, true);
                        }
                    }break;
                }
            }
            
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
        else if($this->_event instanceof Task) {
            $evtDb = new TaskDb();
            $this->_event = $evtDb->update($this->_event);
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

$modify_event = new ModifyEvent($current_user);

$foo = $modify_event->update();
if ($foo) {
    $output["data"]["status"] = "ok";
}
else {
    $output["data"]["status"] = "error";
    $output["data"]["error"] = "event not edited";
}

print_r(json_encode($output));