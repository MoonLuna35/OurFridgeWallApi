<?php

require "../../init.php";
require_once ROOT_PATH . "user/is-loged.php";
require_once ROOT_PATH . "model/timeTable/event/Event.php";
require_once ROOT_PATH . "model/timeTable/event/EventDb.php";

class EventsOfWeek {
    private array $_events;
    private User $_user;
    private EventBaseDb $_eventDb;

    public function __construct(User $u) {
        $this->_user = $u; 
        $postdata = file_get_contents("php://input");
        
        if(!isset($postdata) || empty($postdata)) { 
            log400(__FILE__, __LINE__); 
        }
        else {
            $request = json_decode($postdata);
            if(
                isset($request->data->monday)
                &&
                validateDate($request->data->monday)
            ) {
                $this->_eventDb = new EventBaseDb();
                $this->_events = $this->_eventDb->select_by_week(DateTime::createFromFormat("Y-m-d", $request->data->monday), $this->_user);
            }
            else {
                log400(__FILE__, __LINE__);
            }
        }
    }


	public function get_events(): array  {
		return $this->_events;
	}
}

$eventsOfWeek = new EventsOfWeek($current_user);
$evts = $eventsOfWeek->get_events();

$arr_evts = array();

foreach($evts as $event) {
    array_push($arr_evts, $event->to_array());
}

$output["data"]["status"] = "ok";
$output["data"]["events"] = $arr_evts;

print_r(json_encode($output));

