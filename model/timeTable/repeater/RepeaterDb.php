<?php 
   
    include_once ROOT_PATH . 'model/timeTable/repeater/Repeater.php';
    include_once ROOT_PATH . 'model/timeTable/event/Event.php';
    include_once ROOT_PATH . 'model/connect.php';
    
    abstract class AbstractRepeaterDb extends DB {
        protected string $_querry_str_args = "";
        protected string $_querry_str_coll = "";
        protected array $_querry_args = array(); 
        
        abstract public function insert($repeater): array;
    }

    class RepeaterDailyDb extends AbstractRepeaterDb {
        public function insert($repeater): array {
            $out = array();

            //str coll
            if($repeater->get_for_ever()) {//SI l'evenement ne se termine jamais ALORS
                $this->_querry_str_coll = "
                    repeat_is_for_ever,
                    repeat_n_day
                ";
                $this->_querry_args = array(
                    ":repeat_is_for_ever" => 1,
                    ":repeat_n_day" => $repeater->get_n_day()
                );

            }
            else {
                $this->_querry_str_coll = "
                    repeat_end,
                    repeat_n_day
                ";
                $this->_querry_args = array(
                    ":repeat_end" => 1,
                    ":repeat_n_day" => $repeater->get_n_day()
                );
            }
            
            //str args
            $this->_querry_str_args = $this->generate_querry_str_args($this->_querry_str_coll);

            $out["str_coll"] = $this->_querry_str_coll;
            $out["str_args"] =  $this->_querry_str_args;
            $out["args"] = $this->_querry_args;
            return $out;
        }
    }
    /*class RepeaterWeeklyDb extends AbstractRepeaterDb {
        
    }
    class RepeaterMonthlyDb extends AbstractRepeaterDb {
        
    }
    class RepeaterYearlyDb extends AbstractRepeaterDb {
    
    }*/