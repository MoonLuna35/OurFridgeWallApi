<?php
    require_once ROOT_PATH . "model/timeTable/event/Event.php";
    require_once ROOT_PATH . "model/timeTable/event/EventDb.php";
    class DeleteObject {
        private Event | Task $_event;

        public function __construct(User $current_user)  {
            $postdata = file_get_contents("php://input");

            if(!isset($postdata) || empty($postdata)) { 
            
                log400(__FILE__, __LINE__); 
            }
            
            else {
                
                $request = json_decode($postdata, true);
                $data = $request["data"]; 
                if (isset($data["event"]) && $data["event"]["id"]) { //SI c'est un event ou un mesage ALORS
                    $this->_event = Event::fromDelete($data["event"]["id"], $current_user);//On l'instancie
                }
                else {
                    log400(__FILE__, __LINE__); 
                }
            }
        }

        public function delete(): bool {
            $evt_DB = new EventBaseDb();
            if ($evt_DB->delete_by_id($this->_event)) {
                return true;
            } 
            return false;
        }
    }