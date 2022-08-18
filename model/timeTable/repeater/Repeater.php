<?php
    
    class RepeaterBase {
        protected Event $_event;
        protected ?Date $_date_end;
        protected bool $_for_ever; 
        
		public function get_event(): Event  {
			return $this->_event;
		}
		public function get_date_end(): Date  {
			return $this->_date_end;
		}
        public function get_for_ever(): bool  {
			return $this->for_ever;
		}
        
        public function set_event(Event $new_event): void  {
			$this->_event = $new_event;
		}
		public function set_date_end(Date $new_date_end): void  {
			$this->_date_end = $new_date_end;
		}
		public function set_for_ever(bool $newfor_ever): void  {
			$this->for_ever = $newfor_ever;
		}

        public function to_array($from_event=false): Array { 
            $arr = array(
                "event" => $this->_event,
                "date_end" => $this->_date_end,
                "for_ever" => $this->_for_ever,
            );
        } 
    }

    class RepeaterDaily extends RepeaterBase {
        private int $_n_day;
        
        public function get_n_day(): int  {
			return $this->_n_day;
		}
		public function set_n_day(int $new_n_day): void  {
			$this->_n_day = $new_n_day;
		}
    }
    class RepeaterWeekly extends RepeaterBase {
        private int $_n_week;
        private bool $_is_repeating_monday;
        private bool $_is_repeating_tuesday;
        private bool $_is_repeating_wednesday;
        private bool $_is_repeating_thursday;
        private bool $_is_repeating_friday;
        private bool $_is_repeating_saturday;
        private bool $_is_repeating_sunday; 
        
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
    }
    class RepeaterMonthly extends RepeaterBase {
        private int $_n_month;
        private array $_days_to_repeat;
        private bool $_is_by_monthDay;
        public function get_n_month(): int  {
			return $this->_n_month;
		}
        public function get_days_to_repeat(): array  {
			return $this->_days_to_repeat;
		}
        public function get_is_by_monthDay(): bool  {
			return $this->_is_by_monthDay;
		}

		public function set_n_month(int $new_n_month): void  {
			$this->_n_month = $new_n_month;
		}
		public function set_days_to_repeat(array $new_days_to_repeat): void  {
			$this->_days_to_repeat = $new_days_to_repeat;
		}
		public function set_is_by_monthDay(bool $new_is_by_monthDay): void  {
			$this->_is_by_monthDay = $new_is_by_monthDay;
		}
    }
    class RepeaterYearly extends RepeaterBase {
        private int $_n_year;
        private int $_day; 
        private int $_month;

        public function is_valid_date(): bool {

        }

		public function get_n_year(): int  {
			return $this->_n_year;
		}
        public function get_day(): int  {
			return $this->_day;
		}
        public function get_month(): int  {
			return $this->_month;
		}

		public function set_n_year(int $new_n_year): void  {
			$this->_n_year = $new_n_year;
		}
		public function set_day(int $new_day): void  {
			$this->_day = $new_day;
		}
		public function set_month(int $new_month): void  {
			$this->_month = $new_month;
		}
    }
