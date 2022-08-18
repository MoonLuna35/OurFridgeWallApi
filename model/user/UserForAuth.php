<?php
    class UserForAuth extends User {
        protected bool $_is_grey = false;
        protected bool $_is_checked = false;

        public function __construct(array $values) {
            parent::__construct($values);
            if (isset($values["is_grey"])) {
                $this->_is_grey = $values["is_grey"];
            }
            if (isset($values["is_checked"])) {
                $this->_is_checked = $values["is_checked"];
            }
        }
        public function get_is_grey() {
            return $this->_is_grey;
        }
        public function get_is_checked() {
            return $this->_is_checked;
        }

        public function to_array(bool $stocked_by_client = false) {
            $arr = parent::to_array();
            $arr["is_grey"] = $this->_is_grey;
            $arr["is_checked"] = $this->_is_checked;

            return $arr;
        }
    }