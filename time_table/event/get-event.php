<?php

require "../../init.php";
require_once ROOT_PATH . "user/is-loged.php";
require_once ROOT_PATH . "model/timeTable/event/Event.php";
require_once ROOT_PATH . "model/timeTable/event/EventDb.php";

if(
    isset($_GET["id"])
    &&
    $_GET["id"] > 0 
) {
    $evtDb = new EventBaseDb(); 
    $event = $evtDb->select_by_id($_GET["id"], $current_user);
    if($event !== false) { //SI on a bien recu l'evenement
        $output["data"]["status"] = "ok";
        $output["data"]["event"] = $event->to_array(true);
    }
    else { //SINON
        $output["data"]["status"] = "error";
        $output["data"]["error"] = "event not found";
    }
    print_r(json_encode($output));
}
else {
    log400(__FILE__, __LINE__);
}