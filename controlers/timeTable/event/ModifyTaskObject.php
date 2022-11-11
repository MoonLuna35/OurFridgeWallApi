<?php 
    require_once ROOT_PATH . "model/timeTable/event/EventUtils.php";
    require_once ROOT_PATH . "model/timeTable/event/EventDb.php";
    require_once ROOT_PATH . "controlers/timeTable/event/AddObject.php";
    require_once ROOT_PATH . "controlers/timeTable/event/DeleteObject.php";
    
    class ModifyTask {
        private ?array $_task_to_edit = array();
        private ?DateTime $_new_date_begin = null;
        private mixed $_repeater = null;
        private int $_racine_id;
        private ?Task $_taskTree;
        private Task $_old_task;
        private bool $_is_leaf = true;
    
        public function __construct($current_user) {
            $event_var; 
            $postdata = file_get_contents("php://input");
    
            if(!isset($postdata) || empty($postdata)) { 
            
                log400(__FILE__, __LINE__); 
            }
            
            else {
                
                $request = json_decode($postdata, true);
                $data = $request["data"];
                if(isset($data["racine"]) && $data["racine"] > 0) {
                    $this->_racine_id = $data["racine"];
                }
                else {
                    log400(__FILE__, __LINE__);
                }
                if(isset($data["modify"])){ //Si on modifie les attributs ALORS
                    
                    foreach($data["modify"] as  $info) {
                        if(isset($info["id"])) {
                            
                            array_push($this->_task_to_edit, Task::fromUpdate($info, $current_user));
                        }
                        else if(isset($info["repeat_patern"])) {
                            $this->_repeater = RepeaterUtils::instantiate($info);
                            
                            //On modifie le repeteur sur la racine
                        }
                        else if(isset($info["date_begin"])) {
                            $this->_new_date_begin = new DateTime($info["date_begin"]); //On instancie la date de debut
                        }    
                        //On modifie les dates de debuts
                    }
                }
                else {//SINON SI on modifie la structure de l'arbre de tache 
                    $this->_racine_id = $data["racine"];
                    
                    $this->_taskTree = EventUtils::instanciateFromInsert($data, $current_user);
                    $this->_is_leaf = false;
                }
                
            }
            
        }
    
        public function update_attr($current_user) {
            
            $evtDb = new TaskDb();
            

            if($this->_is_leaf && $evtDb->update_leafs(
                $this->_task_to_edit,
                $this->_racine_id,
                $this->_new_date_begin,
                $this->_repeater,
                $current_user
            )){
                return true;
            }
            
            else if(
                !$this->_is_leaf 
                && 
                $evtDb->update($this->_racine_id, $this->_taskTree)
            ) {
                return true;
            }
            else {
                return false;
            }
        }

        public function update($current_user) {

        }
    }

    