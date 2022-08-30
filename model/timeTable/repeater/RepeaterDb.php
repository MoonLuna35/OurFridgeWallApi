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
    class RepeaterWeeklyDb extends AbstractRepeaterDb {
        public function insert($repeater): array {

            $out = array();

            //str coll
            if($repeater->get_for_ever()) {//SI l'evenement ne se termine jamais ALORS
                $this->_querry_str_coll = "
                    repeat_is_for_ever,
                    
                ";
                $this->_querry_args = array(
                    ":repeat_is_for_ever" => 1
                    
                );

            }
            else {
                $this->_querry_str_coll = "
                    repeat_end,
                ";
                $this->_querry_args = array(
                    ":repeat_end" => 1,
                );
            }

            $this->_querry_str_coll .= "
                repeat_n_week,
                is_repeating_monday,
                is_repeating_tuesday,
                is_repeating_wednesday,
                is_repeating_thursday,
                is_repeating_friday,
                is_repeating_saturday,
                is_repeating_sunday
            ";

            $this->_querry_args[":repeat_n_week"] = $repeater->get_n_week();
            $this->_querry_args[":is_repeating_monday"] = $repeater->get_is_repeating_monday() ? 1 : 0;
            $this->_querry_args[":is_repeating_tuesday"] = $repeater->get_is_repeating_tuesday() ? 1 : 0;
            $this->_querry_args[":is_repeating_wednesday"] = $repeater->get_is_repeating_wednesday() ? 1 : 0;
            $this->_querry_args[":is_repeating_thursday"] = $repeater->get_is_repeating_thursday() ? 1 : 0;
            $this->_querry_args[":is_repeating_friday"] = $repeater->get_is_repeating_friday() ? 1 : 0;
            $this->_querry_args[":is_repeating_saturday"] = $repeater->get_is_repeating_saturday() ? 1 : 0;
            $this->_querry_args[":is_repeating_sunday"] = $repeater->get_is_repeating_sunday() ? 1 : 0;
            
            //str args
            $this->_querry_str_args = $this->generate_querry_str_args($this->_querry_str_coll);

            $out["str_coll"] = $this->_querry_str_coll;
            $out["str_args"] =  $this->_querry_str_args;
            $out["args"] = $this->_querry_args;
            return $out;
        }
    }
    class RepeaterMonthlyDb extends AbstractRepeaterDb {
        public function insert($repeater): array {

            $out = array();

            //str coll
            if($repeater->get_for_ever()) {//SI l'evenement ne se termine jamais ALORS
                $this->_querry_str_coll = "
                    repeat_is_for_ever,
                ";
                $this->_querry_args = array(
                    ":repeat_is_for_ever" => 1
                );

            }
            else {
                $this->_querry_str_coll = "
                    repeat_end,
                ";
                $this->_querry_args = array(
                    ":repeat_end" => 1,
                );
            }

            $this->_querry_str_coll .= "
                repeat_n_month,
                repeat_days_to_repeat,
                repeat_is_by_monthDay
            ";

            $this->_querry_args[":repeat_n_month"] = $repeater->get_n_month();
            $this->_querry_args[":repeat_days_to_repeat"] = $repeater->get_days_to_repeat();
            $this->_querry_args[":repeat_is_by_monthDay"] = $repeater->get_is_by_monthDay() ? 1 : 0;
            
            
            //str args
            $this->_querry_str_args = $this->generate_querry_str_args($this->_querry_str_coll);

            $out["str_coll"] = $this->_querry_str_coll;
            $out["str_args"] =  $this->_querry_str_args;
            $out["args"] = $this->_querry_args;
            return $out;
        }
    }
    class RepeaterYearlyDb extends AbstractRepeaterDb {
        public function insert($repeater): array {

            $out = array();

            //str coll
            if($repeater->get_for_ever()) {//SI l'evenement ne se termine jamais ALORS
                $this->_querry_str_coll = "
                    repeat_is_for_ever,
                ";
                $this->_querry_args = array(
                    ":repeat_is_for_ever" => 1
                );

            }
            else {
                $this->_querry_str_coll = "
                    repeat_end,
                ";
                $this->_querry_args = array(
                    ":repeat_end" => 1,
                );
            }

            $this->_querry_str_coll .= "
                repeat_n_year
            ";

            $this->_querry_args[":repeat_n_year"] = $repeater->get_n_year();
            
            
            //str args
            $this->_querry_str_args = $this->generate_querry_str_args($this->_querry_str_coll);

            $out["str_coll"] = $this->_querry_str_coll;
            $out["str_args"] =  $this->_querry_str_args;
            $out["args"] = $this->_querry_args;
            return $out;
        }
    }