<?php

class RepeaterMonthly extends AbstractRepeater {
        private int $_n_month;
        private string $_days_to_repeat;
        private bool $_is_by_monthDay;



		public static function constructFromArray(array $data): RepeaterMonthly  { 
			$body = $data["repeat_body"];
			static::control_n_month($body["n_month"]);
			static::control_days_to_repeat($body["days_to_repeat"], $body["is_by_monthDay"]);
			
			$rep = new RepeaterMonthly();
			$rep->_n_month = $body["n_month"];
			$rep->_days_to_repeat = $body["days_to_repeat"];
			$rep->_is_by_monthDay = $body["is_by_monthDay"];

			parent::constructBaseFromArray($rep, $data);

			return $rep;
		}

		private static function control_n_month(int $n_month): bool {
			if (0 < $n_month) {
				return true;
			}
			else {
				log400(__FILE__, __LINE__);
			}
		}

		private static function control_days_to_repeat(string $days_to_repeat, bool $is_by_monthDay): bool {
			if (preg_match("/^(\d\d;)*$/", $days_to_repeat)) {
				$exploded_days = explode(";", $days_to_repeat);

				foreach ($exploded_days as $value) {
					if($is_by_monthDay) {
						if($value < 1 && $value > 31) {
							log400(__FILE__, __LINE__);
						}
						else {
							return true;	
						}
					}
					else {
						if($value < 1 && $value > 35) {
							log400(__FILE__, __LINE__);
						}
						else {
							return true;	
						}
					}
				}
			}
			else {
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