<?php

require "../../init.php";

class AddEvent {
    private Event | Message | Task $_event;
    public function __construct() {
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
            if(isset($request->data->type)) {
                switch ($request->data->type) {
                    case "event": {
                        print_r("bite");
                        $this->controlEvent($request->data->event);
                    }break;
                    case "voice_reminder": {

                    }break;
                    case "task": {

                    }break;
                }
            }
        }
    }

    private function validateDate($date, $format = 'Y-m-d H:i') {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }
    

    private function controlEvent($event) {
        if(
            isset($event->date_begin)
            &&
            isset($event->time_begin)
            &&
            isset($event->date_end)
            &&
            isset($event->time_end)
            &&
            isset($event->label)
            &&
            isset($event->desc)
            &&
            isset($event->place)
            &&
            "" !== trim($event->label) 
            &&
            $this->validateDate($event->date_begin . " " . $event->time_begin)
            &&
            $this->validateDate($event->date_end . " " . $event->time_end)

        ) {
            $event->date_begin = new DateTime($event->date_begin . $event->time_begin);
            $event->date_end = new DateTime($event->date_end . $event->time_end);
            unset($event->time_begin);
            unset($event->time_end);
            $event->label = htmlentities($event->label);
            $event->desc = htmlentities($event->desc);
            $event->place = htmlentities($event->place);
            print_r($event);
            return $event;
        }
        else {
            header('HTTP/1.1 400 Bad Request');
            exit; 
        }
        return false;
    }
}

$add_evt = new AddEvent();