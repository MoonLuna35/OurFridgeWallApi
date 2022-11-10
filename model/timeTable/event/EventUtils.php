<?php 
$root = realpath($_SERVER["DOCUMENT_ROOT"]);    
require_once ROOT_PATH . "model/timeTable/repeater/Repeater.php";
require_once ROOT_PATH . "model/timeTable/event/Event.php";
require_once ROOT_PATH . "model/timeTable/event/Message.php";
require_once ROOT_PATH . "model/timeTable/event/Task.php";

class EventUtils {
        /**
         * 
         * DANS CETTE METHODE : 
         * 
         * On determine la classe qui convient suivant le type et on la construit
         * 
         */
        public static function instanciateFromInsert(array $data, User $current_user): Event | Message | Task {
            if(isset($data["type"])) {
                switch ($data["type"]) {
                    case "event": {
                        return Event::fromInsert($data, $current_user);
                    }break;
                    case "voice_reminder": {
                        return Message::fromInsert($data, $current_user);
                    }break;
                    case "task": {
                        return Task::fromInsert($data, $current_user);
                    }break;
                    default: {
                        log400(__FILE__, __LINE__);
                    }
                }
            }
            else {
                log400(__FILE__, __LINE__);
            }
        }

        /**
         * 
         * DANS CETTE METHODE : 
         * 
         * On determine la classe qui convient suivant le type et on la construit
         * 
         */
        public static function instanciateFromUpdate(array $data, User $current_user): Event | Message | Task {
            if(isset($data["type"])) {
                switch ($data["type"]) {
                    case "event": {
                        return Event::fromUpdate($data, $current_user);
                    }break;
                    case "voice_reminder": {
                        return Message::fromUpdate($data, $current_user);
                    }break;
                    default: {
                        log400(__FILE__, __LINE__);
                    }
                }
            }
            else {
                log400(__FILE__, __LINE__);
            }
        }

        

        public static function event_duplicator(Event|Message|Task $event): Array {
            //Dans le cas ou un evenement commence le dimanche soir et se poursuit le lundi
            //On doit afficher la fin de l'event
            
            $events = array($event);
            if($event->get_repeater() instanceof RepeaterDaily) {
                if($event->get_repeater()->get_n_day() < 7) {
                    if ($event->get_date_begin()->format("N") + $event->get_repeater()->get_n_day()  <= 7) {
                        $i = $event->get_date_begin()->modify('+ ' . $event->get_repeater()->get_n_day() . ' days')->format( 'N' );
                        $i_init = $i;
                        $c = 1;
                        //TANT QUE i est superieur ou = a i init FAIRE
                        while($i >= $i_init) {
                    
                            array_push($events, clone $events[$c - 1]);
                            $events[$c]->set_date_begin($events[$c]->get_date_begin()->modify('+ ' . $event->get_repeater()->get_n_day() . ' days'));  
                            $i = $events[$c]->get_date_begin()->modify('+ ' . $event->get_repeater()->get_n_day() . ' days')->format( 'N' );
                            $c++;
                        }
                    }
                    

                }
            }
            else if ($event->get_repeater() instanceof RepeaterWeekly) {
                $repeat_day = $event->get_repeater()->get_repeat_day_array();
                
                $current_day = $event->get_date_begin()->format( 'N' ) - 1;//On prends le jour de la semaine de la date de debut (on est sur qu'il est repete)
                for($i = $current_day + 1; $i < sizeof($repeat_day); $i++) {
                    if($repeat_day[$i]) {
                        $cloned_evt = clone $event;
                        $cloned_evt->set_date_begin($event->get_date_begin()->modify("+" . $i - $current_day . " days")); 
                        array_push($events, $cloned_evt);
                    }
                }
                //On regarde les jours suivant    
            }
            else if ($event->get_repeater() instanceof RepeaterMonthly) {
                $days = $event->get_repeater()->get_days_to_repeat(true);
                $current_day = $event->get_date_begin()->format( 'd' );
                $current_weekDay = $event->get_date_begin()->format( 'N' ) - 1;
                $monday = $event->get_date_begin()->modify("-$current_weekDay days") -> format("d");
                $sunday = $event->get_date_begin()->modify("+ " . 6 - $current_weekDay . " days") -> format("d");

                foreach($days as $value) {
                    if($value >= $monday && $value <= $sunday ) {
                        $cloned_evt = clone $event;
                        $cloned_evt->set_date_begin($event->get_date_begin()->modify("+" . $value - $current_day . " days")); 
                        array_push($events, $cloned_evt);
                    }
                }
            }
            return $events;
        }

        public static function compartEventByDate($eventA, $eventB) {
            if ($eventA->get_date_begin()->getTimestamp() > $eventB->get_date_begin()->getTimestamp())
                return 1;
            else if ($eventA->get_date_begin()->getTimestamp() < $eventB->get_date_begin()->getTimestamp()) 
                return -1;
            else
            return 0;
        }

        public static function instance_repeter($repeater): RepeaterDaily|RepeaterWeekly|RepeaterMonthly|RepeaterYearly {
            
            //on defini les classes a instanciees
            if ($repeater->repeat_patern === "daily") { //SI jour ALORS
                return new RepeaterDaily($repeater);
            }
            else if ($repeater->repeat_patern === "weekly") { //SI semaine ALORS
                return new RepeaterWeekly($repeater);
            }
            else if ($repeater->repeat_patern === "monthly") { //SI mois ALORS
                return new RepeaterMonthly($repeater);
            }
            else { //SI annee ALORS
                return new RepeaterYearly($repeater);
            }
        }
        
    }