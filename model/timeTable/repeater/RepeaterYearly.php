<?php
class RepeaterYearly extends AbstractRepeater {
        private int $_n_year;

		public function __construct($repeater) {
			$repeater = $this->array_transform_for_construct($repeater);
			$repeater = $this->controlRepeater($repeater);
			parent::__construct($repeater);
			$this->_n_year = $repeater->repeat_body->n_year;
		}



		public static function constructFromArray(array $data): RepeaterYearly {
			static::control_n_year($data["repeat_body"]["n_year"]);

			$rep = new RepeaterYearly();
			$rep->_n_year = $data["repeat_body"]["n_year"];

			parent::constructBaseFromArray($rep, $data);

			return $rep;
			
		}
		private static function control_n_year(int $n_year): bool {
			if (0 < $n_year) {
				return true;
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