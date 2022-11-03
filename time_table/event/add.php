<?php

require_once "../../init.php";
require_once ROOT_PATH . "user/is-loged.php";
require_once ROOT_PATH . "model/timeTable/event/Event.php";
require_once ROOT_PATH . "model/timeTable/event/EventDb.php";
require_once ROOT_PATH . "controlers/timeTable/event/AddObject.php";




$add_evt = new AddEvent($current_user);
$add_evt->add();


$output["data"]["status"] = "ok";
$output["data"]["events"] = $add_evt->get_event_array();

print_r(json_encode($output));
