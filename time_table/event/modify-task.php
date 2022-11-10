<?php

require_once "../../init.php";
require_once ROOT_PATH . "user/is-loged.php";
require_once ROOT_PATH . "controlers/timeTable/event/ModifyTaskObject.php";

$modify_event = new ModifyTask($current_user);
$modify_event->update_attr($current_user);




