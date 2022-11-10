<?php

require "../../init.php";
require_once ROOT_PATH . "user/is-loged.php";
require_once ROOT_PATH . "controlers/timeTable/event/ModifyObject.php";
require_once ROOT_PATH . "model/timeTable/event/Event.php";
require_once ROOT_PATH . "model/timeTable/event/EventDb.php";



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