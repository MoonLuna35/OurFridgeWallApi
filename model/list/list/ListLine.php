<?php 
    define('LIST_LINE_PATH', dirname(dirname(__FILE__)));
    include_once LIST_LINE_PATH . '/products/Product.php';
    class ListLine extends Product{
        protected ?ShopList $_list = null; 
        protected bool $_is_striked = false;
        protected bool $_is_from_cupboard = false;
        protected int $_qte = -1;

        public function __construct(Array $values) {
            parent::__construct($values);
            if(isset($values["list"])) {
                $this->_list = $values["list"];
            }
            if (isset($values["is_striked"])) {
                $this->_is_striked = $values["is_striked"];
            }
            if (isset($values["is_from_cupboard"])) {
                $this->_is_from_cupboard = $values["is_from_cupboard"];
            }
            if (isset($values["qte"])) {
                $this->_qte = $values["qte"];
            }

        }

        public function get_list() {
            return $this->_list;
        }
        public function get_is_striked() {
            return $this->_is_striked;
        }
        public function get_qte() {
            return $this->_qte;
        }

        public function to_array() {
            $arr = parent::to_array();
            $arr["is_striked"] = $this->_is_striked;
            $arr["is_from_cupboard"] = $this->_is_from_cupboard;
            $arr["qte"] = $this->_qte;
            if ($this->_list !== null) {
                $arr["list"] = $this->_list;
            }
            return $arr;
        }
	    public function set_qte(int $new_qte) {
		    $this->_qte = $new_qte;
	    }
    }