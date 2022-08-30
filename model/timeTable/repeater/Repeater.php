<?php
    
    abstract class AbstractRepeater {
        protected ?Date $_date_end;
        protected ?bool $_for_ever; 
        
		public function __construct($repeater) {
			if(is_array($repeater)) {
				$repeater = json_encode($repeater);
				$repeater = json_decode($repeater);
			}
			if(isset($repeater->repeat_body->date_end)) {
				$this->_date_end = $repeater->repeat_body->date_end;
			}
			else {
				$this->_for_ever = true;
			}
		}

		protected function controlRepeater($repeater) {
			if(
				isset($repeater->date_end)
				&&
				!isset($repeater->for_ever)
			){
				$repeater->repeat_body->date_end = new Date($repeater->repeat_body->date_end);
				return $repeater;
			}
			else if (
				!isset($repeater->date_end)
				&&
				isset($repeater->for_ever)
				&&
				is_bool($repeater->for_ever)
			) {
				return $repeater;
			}
			else {
				log400(__FILE__, __LINE__); 
			}
		}
		public function get_date_end(): Date  {
			return $this->_date_end;
		}
        public function get_for_ever(): bool  {
			return $this->_for_ever;
		}
		public function set_date_end(Date $new_date_end): void  {
			$this->_date_end = $new_date_end;
		}
		public function set_for_ever(bool $newfor_ever): void  {
			$this->for_ever = $newfor_ever;
		}

        public function to_array(): Array {
			if (!isset($this->_date_end) && isset($this->_for_ever)) {
				$arr = array(
					"for_ever" => $this->_for_ever
				);
			}
			else {
				$arr = array(
					"date_end" => $this->_date_end
				);
			}
			return $arr;
            
        } 
    } 

    class RepeaterDaily extends AbstractRepeater {
        private int $_n_day;
        public function __construct($repeater) {
			$repeater = $this->controlRepeater($repeater);
			parent::__construct($repeater);
			$this->_n_day = $repeater->repeat_body->n_day;
		}
		protected function controlRepeater($repeater) {
			$repeater = parent::controlRepeater($repeater);
			if(isset($repeater->repeat_body->n_day) && $repeater->repeat_body->n_day > 0) { 
				return $repeater;
			}
			else {
				log400(__FILE__, __LINE__);
			}
		}
        public function get_n_day(): int  {
			return $this->_n_day;
		}
		public function set_n_day(int $new_n_day): void  {
			$this->_n_day = $new_n_day;
		}

		public function to_array(): Array { 
            $arr = parent::to_array();
			$arr["n_day"] = $this->_n_day;
			return $arr;
        } 
    }
    class RepeaterWeekly extends AbstractRepeater {
        private int $_n_week;
        private bool $_is_repeating_monday;
        private bool $_is_repeating_tuesday;
        private bool $_is_repeating_wednesday;
        private bool $_is_repeating_thursday;
        private bool $_is_repeating_friday;
        private bool $_is_repeating_saturday;
        private bool $_is_repeating_sunday; 
        
		public function __construct($repeater) {
			$repeater = $this->controlRepeater($repeater);
			parent::__construct($repeater);
			
			$this->_n_week = $repeater->repeat_body->n_week;
			$this->_is_repeating_monday = $repeater->repeat_body->is_repeating_monday;
        	$this->_is_repeating_tuesday = $repeater->repeat_body->is_repeating_tuesday;
        	$this->_is_repeating_wednesday = $repeater->repeat_body->is_repeating_wednesday;
        	$this->_is_repeating_thursday = $repeater->repeat_body->is_repeating_thursday;
        	$this->_is_repeating_friday = $repeater->repeat_body->is_repeating_friday;
        	$this->_is_repeating_saturday = $repeater->repeat_body->is_repeating_saturday;
        	$this->_is_repeating_sunday = $repeater->repeat_body->is_repeating_sunday;
		}
		protected function controlRepeater($repeater) {
			$repeater = parent::controlRepeater($repeater);
			if(
				isset($repeater->repeat_body->n_week) 
				&&
				isset($repeater->repeat_body->is_repeating_monday)
				&& 
				isset($repeater->repeat_body->is_repeating_tuesday)
				&& 
				isset($repeater->repeat_body->is_repeating_wednesday)
				&& 
				isset($repeater->repeat_body->is_repeating_thursday)
				&& 
				isset($repeater->repeat_body->is_repeating_friday)
				&& 
				isset($repeater->repeat_body->is_repeating_saturday)
				&& 
				isset($repeater->repeat_body->is_repeating_sunday)
				&& 
				$repeater->repeat_body->n_week > 0
				&&
				is_bool($repeater->repeat_body->is_repeating_monday)
				&& 
				is_bool($repeater->repeat_body->is_repeating_tuesday)
				&& 
				is_bool($repeater->repeat_body->is_repeating_wednesday)
				&& 
				is_bool($repeater->repeat_body->is_repeating_thursday)
				&& 
				is_bool($repeater->repeat_body->is_repeating_friday)
				&& 
				is_bool($repeater->repeat_body->is_repeating_saturday)
				&& 
				is_bool($repeater->repeat_body->is_repeating_sunday)
				) { 
				return $repeater;
			}
			else {

				log400(__FILE__, __LINE__);
			}
		}

        public function get_n_week(): int  {
			return $this->_n_week;
		}
        public function get_is_repeating_monday(): bool  {
			return $this->_is_repeating_monday;
		}
        public function get_is_repeating_tuesday(): bool  {
			return $this->_is_repeating_tuesday;
		}
        public function get_is_repeating_wednesday(): bool  {
			return $this->_is_repeating_wednesday;
		}
        public function get_is_repeating_thursday(): bool  {
			return $this->_is_repeating_thursday;
		}
        public function get_is_repeating_friday(): bool  {
			return $this->_is_repeating_friday;
		}
        public function get_is_repeating_saturday(): bool  {
			return $this->_is_repeating_saturday;
		}
        public function get_is_repeating_sunday(): bool  {
			return $this->_is_repeating_sunday;
		}

		public function set_n_week(int $new_n_week): void  {
			$this->_n_week = $new_n_week;
		}
    	public function set_is_repeating_monday(bool $new_is_repeating_monday): void  {
			$this->_is_repeating_monday = $new_is_repeating_monday;
		}
		public function set_is_repeating_tuesday(bool $new_is_repeating_tuesday): void  {
			$this->_is_repeating_tuesday = $new_is_repeating_tuesday;
		}
		public function set_is_repeating_wednesday(bool $new_is_repeating_wednesday): void  {
			$this->_is_repeating_wednesday = $new_is_repeating_wednesday;
		}
		public function set_is_repeating_thursday(bool $new_is_repeating_thursday): void  {
			$this->_is_repeating_thursday = $new_is_repeating_thursday;
		}
		public function set_is_repeating_friday(bool $new_is_repeating_friday): void  {
			$this->_is_repeating_friday = $new_is_repeating_friday;
		}
		public function set_is_repeating_saturday(bool $new_is_repeating_saturday): void  {
			$this->_is_repeating_saturday = $new_is_repeating_saturday;
		}
		public function set_is_repeating_sunday(bool $new_is_repeating_sunday): void  {
			$this->_is_repeating_sunday = $new_is_repeating_sunday;
		}

		public function to_array(): Array { 
            $arr = parent::to_array();
			$arr["n_week"] = $this->_n_week;
			$arr["is_repeating_monday"] = $this->_is_repeating_monday;
			$arr["is_repeating_tuesday"] = $this->_is_repeating_tuesday;
			$arr["is_repeating_wednesday"] = $this->_is_repeating_wednesday;
			$arr["is_repeating_thursday"] = $this->_is_repeating_thursday;
			$arr["is_repeating_friday"] = $this->_is_repeating_friday;
			$arr["is_repeating_saturday"] = $this->_is_repeating_saturday;
			$arr["is_repeating_sunday"] = $this->_is_repeating_sunday; 
			return $arr;
        } 
    }
    class RepeaterMonthly extends AbstractRepeater {
        private int $_n_month;
        private string $_days_to_repeat;
        private bool $_is_by_monthDay;

		public function __construct($repeater) { 
			$repeater = $this->controlRepeater($repeater);
			$repeater = $this->controlRepeater($repeater);
			parent::__construct($repeater);

			$this->_n_month = $repeater->repeat_body->n_month;
			$this->_days_to_repeat = $repeater->repeat_body->days_to_repeat;
			$this->_is_by_monthDay =  $repeater->repeat_body->is_by_monthDay;
		}
		public function controlRepeater($repeater) {
			$repeater = parent::controlRepeater($repeater);
			if(
				isset($repeater->repeat_body->n_month)
				&&
				isset($repeater->repeat_body->days_to_repeat)
				&&
				isset($repeater->repeat_body->is_by_monthDay)
				&&
				$repeater->repeat_body->n_month > 0
				&&
				preg_match("/^(\d\d;)*$/", $repeater->repeat_body->days_to_repeat)
				&&
				is_bool($repeater->repeat_body->is_by_monthDay)
			) {
				$exploded_days = explode(";", $repeater->repeat_body->days_to_repeat);

				foreach ($exploded_days as $value) {
					if($repeater->repeat_body->is_by_monthDay) {
						if($value < 1 && $value > 31) {
							log400(__FILE__, __LINE__);
						}
					}
					else {
						if($value < 1 && $value > 35) {
							log400(__FILE__, __LINE__);
						}
					}
				}

				return $repeater;
			}
			else {
				log400(__FILE__, __LINE__);
			}
		}

        public function get_n_month(): int  {
			return $this->_n_month;
		}
        public function get_days_to_repeat(): string  {
			return $this->_days_to_repeat;
		}
        public function get_is_by_monthDay(): bool  {
			return $this->_is_by_monthDay;
		}

		public function set_n_month(int $new_n_month): void  {
			$this->_n_month = $new_n_month;
		}
		public function set_days_to_repeat(string $new_days_to_repeat): void  {
			$this->_days_to_repeat = $new_days_to_repeat;
		}
		public function set_is_by_monthDay(bool $new_is_by_monthDay): void  {
			$this->_is_by_monthDay = $new_is_by_monthDay;
		}

		public function to_array(): Array { 
            $arr = parent::to_array();
			$arr["n_month"] = $this->_n_month;
			$arr["days_to_repeat"] = $this->_days_to_repeat;
			$arr["is_by_monthDay"] = $this->_is_by_monthDay;
			return $arr;
        } 
    }
    class RepeaterYearly extends AbstractRepeater {
        private int $_n_year;

		public function __construct($repeater) {
			$repeater = $this->controlRepeater($repeater);
			parent::__construct($repeater);
			$this->_n_year = $repeater->repeat_body->n_year;
		}

		private function controlDay(int $day, bool $is_short=true) {
			if($is_short && $day > 30) {
				log400(__FILE__, __LINE__);
			}
			else if($is_short && $day > 31) {
				log400(__FILE__, __LINE__);
			}
		}

		protected function controlRepeater($repeater) {
			$repeater = parent::controlRepeater($repeater);
			if(
				isset($repeater->repeat_body->n_year)
				&&
				$repeater->repeat_body->n_year > 0
			) { 
				return $repeater;
			}
			else {
				log400(__FILE__, __LINE__);
			}
		}

		public function get_n_year(): int  {
			return $this->_n_year;
		}

		public function set_n_year(int $new_n_year): void  {
			$this->_n_year = $new_n_year;
		}
		public function to_array(): Array { 
            $arr = parent::to_array();
			$arr["n_year"] = $this->_n_year;
			return $arr;
        } 
    }
