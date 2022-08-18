<?php 
    define('PRODUCT_PATH', dirname(dirname(__FILE__)));
    include_once PRODUCT_PATH . '/type/Type.php';
    class Product {
        private int $_id = -1;
        private String $_label = "";
        private Type $_type;
        private String $_unity = "";
        private ?bool $_is_suggested = null;
        private ?User $_user =  null;

        public function __construct($product_arr) { 
            if (isset($product_arr["id"])) {
                $this->_id = $product_arr["id"];
            }
            if (isset($product_arr["label"])) {
                $this->_label = $product_arr["label"];
            }
            if (isset($product_arr["type"])) {
                $this->_type = $product_arr["type"];
            }
            else {
                $this->_type = new Type(array());
            }
            if (isset($product_arr["unity"])) {
                $this->_unity = $product_arr["unity"];
            }
            if (isset($product_arr["is_suggested"])) {
                $this->_is_suggested = $product_arr["is_suggested"];
            }
            if (isset($product_arr["user"])) {
                $this->_user = $product_arr["user"];
            }
        }

        public function get_id() {
            return $this->_id;
        }
        public function get_label() {
            return $this->_label;
        }
        public function get_type() {
            return $this->_type;
        }
        public function get_unity() {
            return $this->_unity;
        }
        public function get_is_suggested() {
            return $this->_is_suggested;
        }
        public function get_user() {
            return $this->_user;
        }

        public function set_id(int $new_id) {
            $this->_id = $new_id;
        }


        public function to_array() {
            /*
        private int $_id = -1;
        private String $_label = "";
        private Type $_type;
        private String $_unity = "";
        private ?bool $_is_suggested = null;
        private ?User $_user =  null;
        private ?int $_qte = null;
            */ 
            $arr = array(
                "id" => $this->_id,
                "label" => $this->_label,
                "type" => $this->_type->to_array(),
                "unity" => $this->_unity,
            );
            if ($this->_is_suggested !== null) {
                $arr["is_suggested"] = $this->_is_suggested;
            }
            if ($this->_user !== null) {
                $arr["user"] = $this->_user->to_array(true);
            }
            return $arr;
        }
    }