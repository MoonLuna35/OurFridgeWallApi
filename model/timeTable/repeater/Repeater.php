<?php
    
    abstract class AbstractRepeater {
        protected ?DateTime $_date_end;
        protected ?bool $_for_ever; 
		protected Event|Message|Task $_event;
        
		abstract public function repeat(DateTime $monday, &$events): void; 
		public function __construct($repeater) {
			if(isset($repeater->repeat_body->date_end)) {
				$this->_date_end = $repeater->date_end;
			}
			else {
				$this->_for_ever = true;
			}
		}

		protected function array_transform_for_construct($repeater): mixed {
			$transformed_repeater = array();
			
			if(is_array($repeater)) {
				
				if(
					isset($repeater["date_end"])
					&&
					$repeater["date_end"] instanceof DateTime
				) {
					$repeater["date_end"] = $repeater["date_end"]->format("Y-m-d");
				}
				if(
					isset($repeater["is_for_ever"])
					&&
					is_int($repeater["is_for_ever"])
				) {
					$repeater["for_ever"] = $repeater["is_for_ever"] === 1 ? true : false;
				}
				foreach($repeater as $key => $value) {
					if($key !== "for_ever" && $key !== "date_end") {
						$repeater["repeat_body"][$key] = $value;
						
						unset($repeater[$key]);
					}
				}
				
				$repeater = json_encode($repeater);
				$repeater = json_decode($repeater);
				
			}
			
			return $repeater;
		}

		protected function controlRepeater($repeater) {
			
			if(
				isset($repeater->date_end)
				&&
				!isset($repeater->for_ever)
			){
				$repeater->date_end = new Date($repeater->date_end);
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

		protected function modify_date_end(&$event, $evt_date) {
			if($event instanceof Event) {
				$evt_durration = date_diff($event->get_date_begin(), $event->get_date_end()); 
				$evt_date_end = $event->get_date_end();
				$evt_date_end = (clone $evt_date)->add($evt_durration);
				$event->set_date_end($evt_date_end);
			}
		}

		

		//getters
		public function get_date_end(): DateTime  {
			return clone $this->_date_end;
		}
        public function get_for_ever(): bool  {
			return $this->_for_ever;
		}
		public function get_event(): mixed {
			return $this->_event;
		}
		public function set_date_end(DateTime $new_date_end): void  {
			$this->_date_end = $new_date_end;
		}
		public function set_for_ever(bool $newfor_ever): void  {
			$this->for_ever = $newfor_ever;
		}
		public function set_event($new_event): void  {
			$this->_event = $new_event;
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

	class RepeaterBase {
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
	}

    class RepeaterDaily extends AbstractRepeater {
        private int $_n_day;
        public function __construct($repeater) {
			$repeater = $this->array_transform_for_construct($repeater);
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

		public function repeat(DateTime $monday, &$events): void {

			$current_date = clone $monday;//La date du jour courant qui commence Lundi
			$sunday = clone $monday;//Le premier Dimanche depuis le Lundi
			
			$sunday->modify("+6 days");

			$interval = $this->_event->get_date_begin()->diff($monday); //On fait la difference entre le jour de debut et le Lundi
			$interval = $interval->format('%R%a');
			for($i = 0; $i < 7 ; $i++) { //POUR TOUT i de 0 a 6 FAIRE
				if($interval % $this->_n_day === 0) { //SI C'est un jour ou l'event se repete
					$cloned_evt = clone $this->_event;
					$cloned_evt->set_date_begin($cloned_evt->get_date_begin()->modify("+$interval days"));
					if($this->_event instanceof Event) {
						$cloned_evt->set_date_end($cloned_evt->get_date_end()->modify("+$interval days"));
					}
					if(
						isset($this->_date_end)
						&&
						(date_diff($this->_date_end, $cloned_evt->get_date_begin())->format("%a") <= 0)
					) {
						$i = 42;
					}
					else {
						array_push($events, $cloned_evt);
					}
					if(
						(date_diff($sunday, $cloned_evt->get_date_begin())->format("%a") <= 0)
					) {
						$i = 42;
					}
				}
				
				$interval ++;
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
			$repeater = $this->array_transform_for_construct($repeater);
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
    class RepeaterMonthly extends AbstractRepeater {
        private int $_n_month;
        private string $_days_to_repeat;
        private bool $_is_by_monthDay;

		public function __construct($repeater) {
			$repeater = $this->array_transform_for_construct($repeater); 
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
				print_r($repeater);
				log400(__FILE__, __LINE__);
			}
		}

		private function add_evt(&$events, $i, DateTime $monday) {
			$cloned_evt = clone $this->_event;
			$evt_date = $cloned_evt->get_date_begin();
			$evt_date->setDate($monday->format("Y"), $monday->format("m"), $i);
						
			$this->modify_date_end($cloned_evt, $evt_date);
			$cloned_evt->set_date_begin($evt_date);
			
			array_push($events, $cloned_evt);
		}

		public function repeat(DateTime $monday, &$events): void {
			
			$first_day = clone $monday; 
			$first_day->setTime(0, 0, 0, 0); 
			$first_day->modify("-" . $monday->format("d") - 1 . " days");

			if($this->_is_by_monthDay) {
				$days = $this->get_days_to_repeat(true);
				if(sizeof($days) > 0) {
					$current_day = $days[0];
					foreach ($days as $value) {
						for($i = $current_day; $i < 32; $i++) {
							if($value === $i) {
								$this->add_evt($events, $i, $monday);
							}
						}
					} 
				}
			}
			else {

				$days = $this->get_days_to_repeat(true);//Le tableau qui contiens les jours
				$event_day = $this->_event->get_date_begin()->format("d"); //Le jour ou l'evenement commence
				$month_first_day = (clone $monday)->setDate($monday->format("Y"), $monday->format("m"), 1)->format("N");//Le jour de la semaine du premier jour du mois
				$monday_day = $monday->format("d");// Le jour du lundi
				$s =floor(($monday_day + 7) / 7); //la semaine courante dans le mois
				for ($i = ($s - 1)  * 7 + 1; $i <= $s * 7; $i++) { //POUR TOUT i correspondant au jours de la semaine en cours FAIRE
					if($i <= ($s - 1)  * 7 + 1 + (7 - $month_first_day)) { //SI i est inferieur au jour ou on change de semaine dans le tableau des jours ALORS
						for ($j = 0; $j < sizeof($days); $j++) { //POUR TOUT jour FAIRE 
							if ($i + $month_first_day - 1 == $days[$j]) { //SI le jour corresponds a une repetition ALORS 
								$this->add_evt($events, $i, $monday);
							}
						}
					}
					else {
						for ($j = 0; $j < sizeof($days); $j++) { //POUR TOUT jour FAIRE 
							if($i - 1 - (7 -$month_first_day) == $days[$j]) {//SI le jour corresponds a une repetition ALORS 
								$this->add_evt($events, $i, $monday);
							}
						}
					}
				}
			}
		}

        public function get_n_month(): int  {
			return $this->_n_month;
		}
        public function get_days_to_repeat(bool $want_arr=false): string|array  {
			if($want_arr) {
				$arr= array();
				$exploded_days = explode(";", $this->_days_to_repeat);

				foreach ($exploded_days as $value) {
					array_push($arr,$value);
				}
				return $arr;
			}
			else {
				return $this->_days_to_repeat;
			}
			
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
			$repeater = $this->array_transform_for_construct($repeater);
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

		public function repeat(DateTime $monday, &$events): void {
			$event_year = $this->_event->get_date_begin()->format("m-d");
			$current = clone $monday;
			for($i = 0; $i < 6; $i++) {
				if($event_year === $current->modify("+$i days")->format("m-d")) {
					if($current->format("Y") % $this->_n_year === 0 ) {
						$cloned_evt = clone $this->_event;
						$cloned_evt->set_date_begin($cloned_evt->get_date_begin()->setDate($current->format("Y"), $current->format("m"), $current->format("d")));
						if($this->_event instanceof Event) {
							$cloned_evt->set_date_end($cloned_evt->get_date_end()->modify("+$interval days"));
						}
						array_push($events, $cloned_evt);
					}
				}
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
