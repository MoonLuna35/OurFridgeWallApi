<?php

require "../../init.php";
require_once "../../model/timeTable/event/Event.php";
require_once "../../model/timeTable/event/EventDb.php";

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
        
            header('HTTP/1.1 400 Bad Request');
            exit;   
        }
        
        else {
            $request = json_decode($postdata);
            //controle de l'evenement
            if(isset($request->data->type)) {
                switch ($request->data->type) {
                    case "event": {
                        $this->_event = new Event($request->data->event);
                    }break;
                    case "voice_reminder": {
                        $this->_event = new Message($request->data->event);
                    }break;
                    case "task": {
                        $this->_event = new Task($request->data->event);
                    }break;
                }
            }
            print_r($this->_event);
        }
    }

    public function add() { 

    }

    public function get_event_JSON() {
        
    }
}

$add_evt = new AddEvent();