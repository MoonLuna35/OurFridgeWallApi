<?php 
    class Cupboard {
        private  $_products = array();

        public function add_product(CupboardLine $prdt) {
            array_push($this->_products, $prdt);
        }

        public function get_products() {
            return $this->_products;
        }
        public function is_empty() {
            if (sizeof($this->_products) > 0) {
                return false;
            }
            else {
                return true;
            }
            
        }

        public function to_array() {
            $arr = array();
            for ($i = 0; $i < sizeof($this->_products); $i++) {
                array_push($arr, $this->_products[$i]->to_array());
            }
            return $arr;
        }

    } 