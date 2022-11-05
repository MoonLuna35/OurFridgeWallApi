<?php
	class RepeaterUtils {
		public static function generate_repeater($fetched) {
            if( //SI il y a un repeteur ALORS
                $fetched["repeat_is_for_ever"]  
                ||
                $fetched["repeat_date_end"]
            ) {
                //on forme le repeteur
                $repeater_arr = array();
                foreach($fetched as $key => $value) {
                    if(str_contains($key, "repeat_")) {
                        $k = str_replace("repeat_", "", $key);
                        if(str_contains($key, "is_by_monthDay") && is_int($value)) {
                            $repeater_arr[$k] = $value === 1 ? true : false; 
                        }
                        else {
                            $repeater_arr[$k] = $value;
                        }
                    }
                    else if(str_contains($key, "is_repeating")) {
                        if(is_int($value)) {
                            $repeater_arr[$key] = $value === 1 ? true : false; 
                        }
                    }
                }
                
                if($fetched["repeat_n_day"]) {
                    $repeater = new RepeaterDaily($repeater_arr);
                }
                else if($fetched["repeat_n_week"]) {
                    
                    $repeater = new RepeaterWeekly($repeater_arr);
                }
                else if($fetched["repeat_n_month"]) {
                    $repeater = new RepeaterMonthly($repeater_arr);
                }
                else if($fetched["repeat_n_year"]) {
                    $repeater = new RepeaterYearly($repeater_arr);
                }
                return $repeater;
            }
        }

		public static function instantiate($repeater): RepeaterDaily | RepeaterWeekly | RepeaterMonthly | RepeaterYearly{
			switch($repeater["repeat_patern"]){
                case "daily": {
                    return RepeaterDaily::constructFromArray($repeater);
                } break;
                case "weekly": {
                    return RepeaterWeekly::constructFromArray($repeater);
                } break;
                case "monthly": {
                    return RepeaterMonthly::constructFromArray($repeater);
                } break;
            }
		}
	}
