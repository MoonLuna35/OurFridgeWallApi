<?php

    define('CUPBOARD_LINE_PATH', dirname(dirname(__FILE__)));
    include_once CUPBOARD_LINE_PATH.'/../list/products/Product.php';

    class CupboardLine extends Product {
        protected int $_qte = -1;
        protected bool $_is_grey = false;
        protected bool $_is_checked = false;

        public function __construct(Array $values) {
            parent::__construct($values);
            if (isset($values["qte"])) {
                $this->_qte = $values["qte"];
            }
            if (isset($values["is_grey"])) {
                $this->_is_grey = $values["is_grey"];
            }
            if (isset($values["is_checked"])) {
                $this->_is_checked = $values["is_checked"];
            }
        }


        public function get_qte() {
            return $this->_qte;
        }
        public function get_is_grey() {
            return $this->_is_grey;
        }
        public function get_is_checked() {
            return $this->_is_checked;
        }

        public function to_array() {
            $arr = parent::to_array();

            $arr["qte"] = $this->_qte;  
            $arr["is_disabled"] = $this->_is_grey; 
            $arr["is_checked"] = $this->_is_checked;      
            
            return $arr;
        }
    }