<?php

abstract class AbstractRepeater {
    protected ?DateTime $_date_end;
    protected ?bool $_for_ever; 
    protected Event|Message|Task $_event;
    
    //constructeurs
    protected static function constructBaseFromArray(
        RepeaterDaily| RepeaterWeekly | RepeaterMonthly | RepeaterYearly &$repeater, 
        array $data): void  {
            if(isset($data["for_ever"])) {
                static::control_for_ever($data["for_ever"]);
                $repeater->_for_ever = $data["for_ever"];
            }
            else { 
                static::control_date_end($data["date_end"]);
                $repeater->_date_end = new DateTime($data["date_end"]);
            }
    }

    /**
     * revoie true, leve un fatal si forEver est nul ou non bool
     * 
     */
    private static function control_for_ever(bool $forEver): bool {
        return true;
    }

    private static function control_date_end(string $date_end): bool {
        if(validateDateTime($date_end)) {
            return true;
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