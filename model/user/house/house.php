<?php 
    class House {
        private $_users = array(); 
        public function __construct(array $values) {
            if (isset($values["users"])) {
                $this->_user = $values["users"];
            }
        }
        public function get_user(){
                return $this->_user;
        }
    }