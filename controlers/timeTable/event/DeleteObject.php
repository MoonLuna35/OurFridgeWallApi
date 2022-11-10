<?php
    require_once ROOT_PATH . "model/timeTable/event/Event.php";
    require_once ROOT_PATH . "model/timeTable/event/EventDb.php";
    class DeleteObject {
        private Event | Task $_event;

        public function __construct()  {
            $postdata = file_get_contents("php://input");

            if(!isset($postdata) || empty($postdata)) { 
            
                log400(__FILE__, __LINE__); 
            }
            
            else {
                
                $request = json_decode($postdata);
                //controle de l'evenement
                if ("event" === $this->control($request->data)) { //SI c'est un event ou un mesage ALORS
                    $this->_event = new Event();//On l'instancie
                }
                else { //SINON (C'est une tache ) ALORS
                    $this->_event = new Task();//On l'instancie

                }            
            }
        }

        public function delete(): bool {

        }
    }