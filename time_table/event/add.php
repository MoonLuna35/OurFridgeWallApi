<?php

require "../../init.php";
require_once ROOT_PATH . "model/timeTable/event/Event.php";
require_once ROOT_PATH . "model/timeTable/event/EventDb.php";

class AddEvent {
    private Event | Message | Task $_event;
    public function __construct() {
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
                            $this->_event = new Event($request->data->event, $request->data->repeater);
                        }
                        else {
                            $this->_event = new Event($request->data->event);
                        }
                        
                    }break;
                    case "voice_reminder": {
                        if(isset($request->data->repeater)) {
                            $this->_event = new Message($request->data->event, $request->data->repeater);
                        }
                        else {
                            $this->_event = new Message($request->data->event);
                        }
                    }break;
                    case "task": {
                        if(isset($request->data->repeater)) {
                            $this->_event = new Task($request->data->event, $request->data->repeater);
                        }
                        else {
                            $this->_event = new Task($request->data->event);
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
    }

    public function get_event_JSON() {
        
    }
}

$add_evt = new AddEvent();
$add_evt->add();
