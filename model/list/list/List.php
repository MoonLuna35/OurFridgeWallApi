<?php
    define('LIST_PATH', dirname(dirname(__FILE__)));
    include_once LIST_PATH.'/../user/User.php';
    class ShopList {
        protected int $_id = -1;
        protected ?User $_author = null;
        protected String $_name = ""; 
        protected String $_desc = "";
        protected DateTime $_date_create;
        protected ?DateTime $_date_update = null; 
        protected bool $_is_protected = false; 
        protected bool $_is_archived = false;
        protected Array $_lines = array();
        protected ?int $_linesCount = null;
        protected ?Array $_users_auth = null;

        public function __construct(Array $value) {
            if(isset($value["id"]) && $value["id"] > 0) {
                $this->_id = $value["id"];   
            }
            if(isset($value["author"])) {
                $this->_author = $value["author"];   
            }
            if(isset($value["name"]) && trim($value["name"]) !== "") {
                $this->_name = $value["name"];   
            }
            if(isset($value["description"])) {
                $this->_desc = $value["description"];   
            }
            if(isset($value["date_create"])) {
                $this->_date_create = new DateTime($value["date_create"]);   
            }
            else {
                $this->_date_create = new DateTime();
            }
            if(isset($value["date_last_update"])) {
                $this->_date_update = $value["date_last_update"];   
            }
            if(isset($value["is_protected"])) {
                $this->_is_protected = $value["is_protected"];   
            }
            if(isset($value["is_archived"])) {
                $this->_is_archived = $value["is_archived"];   
            }
            if(isset($value["lines"])) {
                $has_only_lines = true;
                for ($i = 0; $i < sizeof($value["lines"]) && $has_only_lines; $i++) {
                    if(!$value["lines"][$i] instanceof ListLine) {
                        $has_only_lines = false;
                    }
                }
                if ($has_only_lines) {
                    $this->_lines = $value["lines"];
                }
            }
            if(isset($value["users_auth"])) {
                $has_only_user = true;
                for ($i = 0; $i < sizeof($value["users_auth"]) && $has_only_user; $i++) {
                    if(!$value["users_auth"][$i] instanceof User) {
                        $has_only_user = false;
                    }
                }
                if ($has_only_user) {
                    $this->_users_auth = $value["users_auth"];
                }
            }
            if(isset($value["linesCount"])) {
                $this->_linesCount = $value["linesCount"];   
            }
        }

        public function get_id() {
            return $this->_id;
        }
        public function get_author() {
            return $this->_author;
        }
        public function get_name() {
            return $this->_name;
        }
        public function get_desc() {
            return $this->_desc;
        }
        public function get_date() {
            return $this->_date;
        }
        public function get_is_protected() {
            return $this->_is_protected;
        }
        public function get_is_archived() {
            return $this->_is_archived;
        }
        public function get_lines() {
            return $this->_lines;
        }
        public function get_linesCount() {
            return $this->_linesCount;
        }
        public function get_users_auth() {
            return $this->_users_auth;
        }

        public function to_array() {
            $arr =  array(
                "id" => $this->_id,
                "name" => $this->_name,
                "desc" => $this->_desc,
                "date_create" => $this->_date_create->format("F d, Y"),
                "is_protected" => $this->_is_protected,
                "is_archived" => $this->_is_archived,
                "lines" => array()
            );
            for ($i = 0; $i < sizeof($this->_lines); $i++) {
                array_push($arr["lines"], $this->_lines[$i]->to_array());
            }
            if ($this->_author !== null) {
                
                $arr["author"] = $this->_author->to_array();
            }
            if ($this->_date_update !== null) {
                $arr["date_update"] = $this->_date_update->format("d-m-Y H:i:s");
            }
            if ($this->_linesCount !== null) {
                $arr["linesCount"] = $this->_linesCount;
            }
            if ($this->_users_auth !== null) {
                $arr["users_auth"] = array();
                for ($i = 0; $i < sizeof($this->_users_auth); $i++) {
                    array_push($arr["users_auth"], $this->_users_auth[$i]->to_array());
                }
            }
            return $arr;

        }


        public function set_id(int $new_id) {
            $this->_id = $new_id;
        }
        public function set_author(User $new_author) {
            $this->_author = $new_author;
        }

	    public function set_lines(Array $new_lines) {
		    $this->_lines = $new_lines;
	    }
        public function set_users_auth(Array $new_users_auth) {
            $all_are_user = true;
            for ($i = 0; $i < sizeof($new_users_auth) && $all_are_user; $i++) {
                if (!$new_users_auth[$i] instanceof User) {
                    $all_are_user = false;
                }
            }
            if($all_are_user) {
                $this->_users_auth = $new_users_auth;
            }
        }
	}