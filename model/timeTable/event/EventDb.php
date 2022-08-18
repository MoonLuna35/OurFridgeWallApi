<?php 
    define('EVENT_PATH', dirname(dirname(__FILE__)));
    include_once EVENT_PATH . '/event/Event.php';
    include_once EVENT_PATH . '/../connect.php';
    
    class EventDb extends DB {
        public function add(Event | Message | Task $event, $h=0) {
            
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                //premiere partie de la requette
                if($event instanceof Event) {
                    
                }
                else if ($event instanceof Message) {

                }
                else {

                }
                //repeater
            
                
                $this->_db->beginTransaction();
            }
            catch (Exeption $e) {
                $this->_db->rollBack();
                $this->add($event, $h+1);
            }
        }
    }