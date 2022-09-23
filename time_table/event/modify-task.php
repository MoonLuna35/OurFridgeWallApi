<?php

require "../../init.php";
require_once ROOT_PATH . "user/is-loged.php";
require_once ROOT_PATH . "model/timeTable/event/Event.php";
require_once ROOT_PATH . "model/timeTable/event/EventDb.php";

class ModifyTask {
    private ?array $_task_to_edit = array();
    private ?int $_racine_id;
    private ?Task $_taskTree;

    public function __construct($current_user) {
        $event_var; 
        $postdata = file_get_contents("php://input");

        if(!isset($postdata) || empty($postdata)) { 
        
            log400(__FILE__, __LINE__); 
        }
        
        else {
            
            $request = json_decode($postdata);
            if(isset($request->data->modify)){ 
                foreach($request->data->modify as $info) {
                    if(isset($info->id)) {
                        array_push($this->_task_to_edit, new Task($info, $current_user, null, true));
                    }    
                }
            }
        }
        
    }

    public function update_attr() {
        $evtDb = new TaskDb();
        if($evtDb->update_leafs($this->_task_to_edit)){
            print_r("ok");
        }
        else {
            print_r("n_ok");
        }
    }
}

$modify_event = new ModifyTask($current_user);
$modify_event->update_attr();
//$foo = $modify_event->update();