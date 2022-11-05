<?php
class RepeaterDaily extends AbstractRepeater {
        private int $_n_day;

		public static function constructFromArray(array $data): RepeaterDaily {
			static::control_n_day($data["repeat_body"]["n_day"]);

			$rep = new RepeaterDaily();
			$rep->_n_day = $data["repeat_body"]["n_day"];

			parent::constructBaseFromArray($rep, $data);

			return $rep;
			
		}
		private static function control_n_day(int $n_day): bool {
			if(0 < $n_day) {
				return true;
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