<?php 
    class Type {
        private int $_id = -1;
        private String $_name = "";
        private ?User $_user = null;
        private ?String $_lang = null;
        private ?int $_logo_patern = null;
        private ?int $_logo_color = null;
        private ?bool $_is_deletable = false;

        public function __construct($arr_type) {
            if (isset($arr_type["id"])) {
                $this->_id =  $arr_type["id"];
            }
            if (isset($arr_type["name"])) {
                $this->_name =  $arr_type["name"];
            }
            if (isset($arr_type["user"])) {
                $this->_user =  $arr_type["user"];
            }
            if (isset($arr_type["lang"])) {
                $this->_lang =  $arr_type["lang"];
            }
            if (isset($arr_type["logo_patern"])) {
                $this->_logo_patern =  $arr_type["logo_patern"];
            }
            if (isset($arr_type["logo_color"])) {
                $this->_logo_color =  $arr_type["logo_color"];
            }
            if (isset($arr_type["is_deletable"])) {
                $this->_is_deletable =  $arr_type["is_deletable"];
            }

        }

        public function get_id() {
            return $this->_id;
        }
        public function get_name() {
            return $this->_name;
        }
        public function get_lang() {
            return $this->_lang;
        }
        public function get_user() {
            return $this->_user;
        }
        public function get_logo_patern() {
            return $this->_logo_patern;
        }
        public function get_logo_color() {
            return $this->_logo_color;
        }
        public function get_is_deletable() {
            return $this->_is_deletable;
        }

        public function set_id(String $new_id) {
            $this->_id = $new_id;
        }
        public function set_name(String $new_name) {
            $this->_name = $new_name;
        }
        public function set_logo_patern(String $new_logo_patern) {
            $this->_logo_patern = $new_logo_patern;
        }
        public function set_logo_color(String $new_logo_color) {
            $this->_logo_color = $new_logo_color;
        }
        public function set_is_deletable(String $new_is_deletable) {
            $this->_is_deletable = $new_is_deletable;
        }

        public function to_array() {
            /*
             private int $_id = -1;
        private String $_name = "";
        private ?User $_user = null;
        private ?String $lang = null;
        private String $_img_url = "";
        */ 
            $arr = array(
                "id" => $this->_id,
                "name" => $this->_name,
                
            );
            if ($this->_user !== null) {
                $arr["user"] = $this->_user->to_array(true);
            }
            if ($this->_lang  !== null) {
                $arr["lang "] = $this->_lang;
            }
            if ($this->_logo_color !== null) {
                $arr["logo_color"] = $this->_logo_color;
            }
            if ($this->_logo_patern !== null) {
                $arr["logo_patern"] = $this->_logo_patern;
            }
            
            if ($this->_is_deletable !== null) {
                $arr["is_deletable"] = $this->_is_deletable;
            }
            return $arr;
        }
    }