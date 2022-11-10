<?php
    class RepeaterWeekly extends AbstractRepeater {
        private int $_n_week;
        private bool $_is_repeating_monday;
        private bool $_is_repeating_tuesday;
        private bool $_is_repeating_wednesday;
        private bool $_is_repeating_thursday;
        private bool $_is_repeating_friday;
        private bool $_is_repeating_saturday;
        private bool $_is_repeating_sunday; 
        


		public static function constructFromArray(array $data): RepeaterWeekly{
			$body = $data["repeat_body"];
			static::control_n_week($body["n_week"]);
			static::control_repeater(
				$body["is_repeating_monday"],
				$body["is_repeating_tuesday"],
				$body["is_repeating_wednesday"],
				$body["is_repeating_thursday"],
				$body["is_repeating_friday"],
				$body["is_repeating_saturday"],
				$body["is_repeating_sunday"]
			);

			$rep = new RepeaterWeekly();
			$rep->_n_week = $body["n_week"];
			$rep->_is_repeating_monday = $body["is_repeating_monday"];
			$rep->_is_repeating_tuesday = $body["is_repeating_tuesday"];
			$rep->_is_repeating_wednesday = $body["is_repeating_wednesday"];
			$rep->_is_repeating_thursday = $body["is_repeating_thursday"];
			$rep->_is_repeating_friday = $body["is_repeating_friday"];
			$rep->_is_repeating_saturday = $body["is_repeating_saturday"];
			$rep->_is_repeating_sunday = $body["is_repeating_sunday"];

			parent::constructBaseFromArray($rep, $data);

			return $rep;
		}

		private static function control_n_week(int $n_week) { 
			if(0 < $n_week) {
				return true;
			}
			else {
				log400(__FILE__, __LINE__);
			}
		}

		private static function control_repeater(
			bool $is_repeating_monday,
			bool $is_repeating_tuesday,
			bool $is_repeating_wednesday,
			bool $is_repeating_thursday,
			bool $is_repeating_friday,
			bool $is_repeating_saturday,
			bool $is_repeating_sunday
		): bool {
			return true;
		}


		public function repeat(DateTime $monday, &$events): void {
			$bool_arr = $this->get_repeat_day_array();
			$i_init = 0;
			$interval = $this->_event->get_date_begin()->diff($monday); //On fait la difference entre le jour de debut et le Lundi
			$interval = $interval->format('%R');
			if($interval === "-" ) {
				$i_init = $this->_event->get_date_begin()->format("N");
			}
			for($i = $i_init; $i < sizeof($bool_arr); $i++) {
				if($bool_arr[$i]) {
					$cloned_evt = clone $this->_event;
					$d = (clone $monday)->modify("+" . $i . " days");
					$cloned_evt->set_date_begin($cloned_evt->get_date_begin()->setDate($d->format("Y"), $d->format("m"), $d->format("d")));
					if($this->_event instanceof Event) {
						$cloned_evt->set_date_end($cloned_evt->get_date_end()->modify("+$interval days"));
					}
					array_push($events, $cloned_evt);
				}
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
		public function get_repeat_day_array(): array {
			return array(
				$this->_is_repeating_monday,
				$this->_is_repeating_tuesday,
				$this->_is_repeating_wednesday,
				$this->_is_repeating_thursday,
				$this->_is_repeating_friday,
				$this->_is_repeating_saturday,
				$this->_is_repeating_sunday
			);
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