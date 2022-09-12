<?php 
   
    include_once ROOT_PATH . 'model/timeTable/repeater/Repeater.php';
    include_once ROOT_PATH . 'model/timeTable/event/Event.php';
    include_once ROOT_PATH . 'model/connect.php';
    
    abstract class AbstractRepeaterDb extends DB {
        protected string $_querry_str_args = "";
        protected string $_querry_str_coll = "";
        protected array $_querry_args = array(); 
        
        abstract public function insert($repeater): array;
        abstract public function update($repeater, $querry_args): array;

        
    }

    class RepeaterBase {
        public static function prepare_to_update(string $querry_str_coll, array $querry_args): array {
            $querry_str_coll .= "
                repeat_date_end = :repeat_date_end,
                repeat_is_for_ever = :repeat_is_for_ever,
                repeat_n_day = :repeat_n_day,
                repeat_n_week = :repeat_n_week,
                is_repeating_monday = :is_repeating_monday,
                is_repeating_tuesday = :is_repeating_tuesday,
                is_repeating_wednesday = :is_repeating_wednesday,
                is_repeating_thursday = :is_repeating_thursday,
                is_repeating_friday = :is_repeating_friday,
                is_repeating_saturday = :is_repeating_saturday,
                is_repeating_sunday = :is_repeating_sunday,
                repeat_n_month = :repeat_n_month,
                repeat_days_to_repeat = :repeat_days_to_repeat,
                repeat_is_by_monthDay = :repeat_is_by_monthDay,
                repeat_n_year = :repeat_n_year
            ";

            $querry_args["repeat_date_end"]=NULL;
            $querry_args["repeat_is_for_ever"]=NULL;
            $querry_args["repeat_n_day"]=NULL;
            $querry_args["repeat_n_week"]=NULL;
            $querry_args["is_repeating_monday"]=NULL;
            $querry_args["is_repeating_tuesday"]=NULL;
            $querry_args["is_repeating_wednesday"]=NULL;
            $querry_args["is_repeating_thursday"]=NULL;
            $querry_args["is_repeating_friday"]=NULL;
            $querry_args["is_repeating_saturday"]=NULL;
            $querry_args["is_repeating_sunday"]=NULL;
            $querry_args["repeat_n_month"]=NULL;
            $querry_args["repeat_days_to_repeat"]=NULL;
            $querry_args["repeat_is_by_monthDay"]=NULL;
            $querry_args["repeat_n_year"]=NULL;

            return [$querry_str_coll, $querry_args];
        }
    }

    class RepeaterDailyDb extends AbstractRepeaterDb {
        public function update($repeater, $querry_args): array {
            if($repeater->get_for_ever()) {
                $querry_args["repeat_is_for_ever"] = 1;
            }
            else {
                $querry_args["repeat_date_end"]=date_format($repeater->get_date_begin(), 'Y-m-d H:i:s');
            }
            $querry_args["repeat_n_day"] = $repeater->get_n_day();
            
            return $querry_args;
            
        }
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
                    repeat_date_end,
                    repeat_n_day
                ";
                $this->_querry_args = array(
                    ":repeat_date_end" => 1,
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
        public function update($repeater, $querry_args): array {
            print_r($querry_args);
            if($repeater->get_for_ever()) {
                
                $querry_args["repeat_is_for_ever"] = 1;
            }
            else {
                $querry_args["repeat_date_end"]=date_format($repeater->get_date_begin(), 'Y-m-d H:i:s');
            }
            $querry_args[":repeat_n_week"] = $repeater->get_n_week();
            $querry_args[":is_repeating_monday"] = $repeater->get_is_repeating_monday() ? 1 : 0;
            $querry_args[":is_repeating_tuesday"] = $repeater->get_is_repeating_tuesday() ? 1 : 0;
            $querry_args[":is_repeating_wednesday"] = $repeater->get_is_repeating_wednesday() ? 1 : 0;
            $querry_args[":is_repeating_thursday"] = $repeater->get_is_repeating_thursday() ? 1 : 0;
            $querry_args[":is_repeating_friday"] = $repeater->get_is_repeating_friday() ? 1 : 0;
            $querry_args[":is_repeating_saturday"] = $repeater->get_is_repeating_saturday() ? 1 : 0;
            $querry_args[":is_repeating_sunday"] = $repeater->get_is_repeating_sunday() ? 1 : 0;
            
            return $querry_args;
        }
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
                    repeat_date_end,
                ";
                $this->_querry_args = array(
                    ":repeat_date_end" => 1,
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
        public function update($repeater, $querry_args): array {
            
            if($repeater->get_for_ever()) {
                $querry_args["repeat_is_for_ever"] = 1;
            }
            else {
                $querry_args["repeat_date_end"]=date_format($repeater->get_date_begin(), 'Y-m-d H:i:s');
            }
            $querry_args[":repeat_n_month"] = $repeater->get_n_month();
            $querry_args[":repeat_days_to_repeat"] = $repeater->get_days_to_repeat();
            $querry_args[":repeat_is_by_monthDay"] = $repeater->get_is_by_monthDay() ? 1 : 0;
            
            return $querry_args;
        }
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
                    repeat_date_end,
                ";
                $this->_querry_args = array(
                    ":repeat_date_end" => 1,
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
        public function update($repeater, $querry_args): array {
            if($repeater->get_for_ever()) {
                $querry_args["repeat_is_for_ever"] = 1;
            }
            else {
                $querry_args["repeat_date_end"]=date_format($repeater->get_date_begin(), 'Y-m-d H:i:s');
            }
            $querry_args[":repeat_n_year"] = $repeater->get_n_year();

            return $querry_args;
        }
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
                    repeat_date_end,
                ";
                $this->_querry_args = array(
                    ":repeat_date_end" => 1,
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