<?php

require "../../init.php";
require_once ROOT_PATH . "user/is-loged.php";
require_once ROOT_PATH . "model/timeTable/event/Event.php";
require_once ROOT_PATH . "model/timeTable/event/EventDb.php";


class AddEvent {
    private Event | Message | Task $_event;
    public function __construct($current_user) {
        $event_var; 
        $postdata = file_get_contents("php://input");
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            // The request is using the POST method
            return http_response_code(200);
        
        }
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
                             
                            $this->_event = new Event($request->data->event, $current_user, $request->data->repeater);
                        }
                        else {
                            $this->_event = new Event($request->data->event, $current_user);
                        }
                        
                    }break;
                    case "voice_reminder": {
                        
                        if(isset($request->data->repeater)) {
                            //print_r($request);
                            $this->_event = new Message($request->data->event, $current_user, $request->data->repeater);
                        }
                        else {
                            $this->_event = new Message($request->data->event, $current_user);
                        }
                    }break;
                    case "task": {
                        
                        if(isset($request->data->repeater)) {
                            $this->_event = new Task($request->data->event, $current_user, $request->data->repeater);
                        }
                        else {
                            $this->_event = new Task($request->data->event, $current_user);
                        }
                    }break;
                }
            }
            
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
        $events = AbstractEvent::event_duplicator($this->_event);
        $evt_arr = array();
        foreach($events as $evt) {
            array_push($evt_arr, $evt->to_array(false));
        }
        return $evt_arr;
    }
}

$add_evt = new AddEvent($current_user);
$add_evt->add();


$output["data"]["status"] = "ok";
$output["data"]["events"] = $add_evt->get_event_array();

print_r(json_encode($output));
