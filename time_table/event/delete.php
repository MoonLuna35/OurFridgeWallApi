<?php

require "../../init.php";
require_once ROOT_PATH . "user/is-loged.php";
require_once ROOT_PATH . "controlers/timeTable/event/DeleteObject.php";
require_once ROOT_PATH . "model/timeTable/event/Event.php";
require_once ROOT_PATH . "model/timeTable/event/EventDb.php";

$del_evt = new DeleteObject($current_user);
if ($del_evt-> delete()) {
    $output["data"]["status"] = "ok";
}
else {
    $output["data"]["status"] = "nothing was deleted";
}

print_r(json_encode($output));