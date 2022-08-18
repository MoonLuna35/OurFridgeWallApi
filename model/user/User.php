<?php 
use Firebase\JWT\JWT;
    class User {
        protected int $_id = -1;
        protected String $_pseudo = "";
        protected bool $_is_using_name = false;
        protected String $_civility = ""; 
        protected String $_name = "";
        protected String $_surname = "";
        protected String $_birthday = "";
        protected ?String $_mail = null ;
        protected ?String $_pass = null;
        protected $_is_double_auth = null;
        protected ?String $_tel = null;
        protected bool $_is_call_by_name = false;
        protected  bool$_is_tu = false;
        protected String $_pronum = "";
        protected String $_talk_about_me = ""; 
        protected bool $_is_plural = false;
        protected ?int $_house = null;
        protected ?bool $_is_root = null;
        protected ?bool  $_is_premium = true;
        protected ?String  $_premium_token  = null;
        protected ?String $_activate = null;
        protected ?bool $_is_activated = null;
        protected ?String $_token = null;
        protected ?String $_refresh_tokens = null;
        protected ?String $_first_log_token = null;
        protected ?String $_device_token = null;

        public function __construct($user_arr) {
            if(isset($user_arr["id"]) && (int) $user_arr["id"] > 0) {
                $this->_id = $user_arr["id"];
            }
            if(isset($user_arr["pseudo"]) && trim($user_arr["pseudo"]) != "") {
                $this->_pseudo = $user_arr["pseudo"];
            }
            if(isset($user_arr["is_using_name"]) && trim($user_arr["is_using_name"]) != "") {
                $this->_is_using_name = $user_arr["is_using_name"];
            }
            if(isset($user_arr["civility"]) && trim($user_arr["civility"]) != "") {
                $this->_civility = $user_arr["civility"];
            }
            if(isset($user_arr["name"]) && trim($user_arr["name"]) != "") {
                $this->_name = $user_arr["name"];
            }
            if(isset($user_arr["surname"]) && trim($user_arr["surname"]) != "") {
                $this->_surname = $user_arr["surname"];
            }
            if(isset($user_arr["birthday"]) && trim($user_arr["birthday"]) != "") {
                $this->_birthday = $user_arr["birthday"];
            }
            if(isset($user_arr["mail"]) && trim($user_arr["mail"]) != "") {
                $this->_mail = $user_arr["mail"];
            }
            if(isset($user_arr["pass"]) && trim($user_arr["pass"]) != "") {
                $this->_pass = $user_arr["pass"];
            }
            if(isset($user_arr["is_double_auth"]) && trim($user_arr["is_double_auth"]) != "") {
                $this->_is_double_auth = $user_arr["is_double_auth"];
            }
            if(isset($user_arr["tel"]) && trim($user_arr["tel"]) != "") {
                $this->_tel = $user_arr["tel"];
            }
            if(isset($user_arr["is_call_by_name"]) && trim($user_arr["is_call_by_name"]) != "") {
                $this->_is_call_by_name = $user_arr["is_call_by_name"];
            }
            if(isset($user_arr["is_tu"]) && trim($user_arr["is_tu"]) != "") {
                $this->_is_tu = $user_arr["is_tu"];
            }
            if(isset($user_arr["pronum"]) && trim($user_arr["pronum"]) != "") {
                $this->_pronum = $user_arr["pronum"];
            }
            if(isset($user_arr["talk_about_me"]) && trim($user_arr["talk_about_me"]) != "") {
                $this->_talk_about_me = $user_arr["talk_about_me"];
            }
            if(isset($user_arr["is_plural"]) && trim($user_arr["is_plural"]) != "") {
                $this->_is_plural = $user_arr["is_plural"];
            }
            if(isset($user_arr["activate"]) && trim($user_arr["activate"]) != "") {
                $this->_activate = $user_arr["activate"];
            }
            if(isset($user_arr["is_activated"]) && trim($user_arr["is_activated"]) != "") {
                $this->_is_activated = $user_arr["is_activated"];
            }
            if(isset($user_arr["token"]) && trim($user_arr["token"]) != "") {
                $this->_token = $user_arr["token"];
            }
            if(isset($user_arr["refresh_token"])) {
                $this->_refresh_tokens = $user_arr["refresh_token"];
            }
            if(isset($user_arr["first_log_token"]) && trim($user_arr["first_log_token"]) != "") {
                $this->_first_log_token = $user_arr["first_log_token"];
            }
            if(isset($user_arr["house"]) && (int) $user_arr["house"] > 0) {
                
                $this->_house = $user_arr["house"];

            }
            /*if(isset($user_arr["is_premium"])) {
                $this->_is_premium = $user_arr["is_premium"];
            }*/
            if(isset($user_arr["is_root"])) {
                $this->_is_root = $user_arr["is_root"];
            }

        }

        public function pass_is_matched($pass) {
            return password_verify($pass, $this->_pass);
        }

        public function generate_token() {
            $issuedAt   = new DateTimeImmutable();
            $expire     = $issuedAt->modify('+5 minutes')->getTimestamp();

            $protectedKey = openssl_pkey_get_private(SECRET_KEY, $_ENV["PRIVATE_PASS"]);
            $payload = array(
                "activate" => $this->_activate,
                "id" => $this->_id,
                "house" => $this->_house,
                "is_root" => $this->_is_root,
                "is_premium" => $this->_is_premium,
                "iss" => "theFridgeDoor.fr",
                "aud" => "theFridgeDoor.fr",
                "iat" => $issuedAt->getTimestamp(),
                "nbf" => $issuedAt->getTimestamp(),
                "exp" => $expire
            );
            $this->_mail = "";
            $this->_activate= "";
            $this->_token = JWT::encode($payload, $protectedKey, 'RS256');
            
           
        }

        public function generate_premium_token() {
            $issuedAt   = new DateTimeImmutable();
            $expire     = $issuedAt->modify('+1 months')->getTimestamp();

            $protectedKey = openssl_pkey_get_private(PREMIUM_SECRET_KEY, $_ENV["PREMIUM_PASS"]);
            $payload = array(
                "house" => $this->_house,
                "iss" => "theFridgeDoor.fr",
                "aud" => "theFridgeDoor.fr",
                "iat" => $issuedAt->getTimestamp(),
                "nbf" => $issuedAt->getTimestamp(),
                "exp" => $expire
            );
            $this->_is_premium = JWT::encode($payload, $protectedKey, 'RS256');  
            return($this->_is_premium);
        }

        public function generate_first_log_token() {
            $issuedAt   = new DateTimeImmutable();
            $expire     = $issuedAt->modify('+1 days')->getTimestamp();

            $protectedKey = openssl_pkey_get_private(SECRET_KEY, $_ENV["PRIVATE_PASS"]);
            $payload = array(
                "activate" => $this->_activate,
                "id" => $this->_id,
                "iss" => "theFridgeDoor.fr",
                "aud" => "theFridgeDoor.fr",
                "iat" => $issuedAt->getTimestamp(),
                "nbf" => $issuedAt->getTimestamp(),
                "exp" => $expire
            );
            $this->_mail = "";
            $this->_activate= "";
            $this->_first_log_token = JWT::encode($payload, $protectedKey, 'RS256');
        }


        public function generate_refresh_token() {
            $issuedAt   = new DateTimeImmutable();
            $expire     = $issuedAt->modify('+3 months')->getTimestamp();

            $protectedKey = openssl_pkey_get_private(SECRET_KEY, $_ENV["PRIVATE_PASS"]);
            $payload = array(
                "token" => $this->_token,
                "id" => $this->_id,
                "iss" => "theFridgeDoor.fr",
                "aud" => "theFridgeDoor.fr",
                "iat" => $issuedAt->getTimestamp(),
                "nbf" => $issuedAt->getTimestamp(),
                "exp" => $expire
            );
            $this->_mail = "";
            $this->_activate= "";
            $this->_refresh_tokens  = JWT::encode($payload, $protectedKey, 'RS256');
            return $this->_refresh_tokens;
        }

        public function generate_device_token() {
            $issuedAt   = new DateTimeImmutable();
            $expire     = $issuedAt->modify('+10 years')->getTimestamp();

            $protectedKey = openssl_pkey_get_private(SECRET_KEY, $_ENV["PRIVATE_PASS"]);
            $payload = array(
                "token" => $this->_token,
                "id" => $this->_id,
                "iss" => "theFridgeDoor.fr",
                "aud" => "theFridgeDoor.fr",
                "iat" => $issuedAt->getTimestamp(),
                "nbf" => $issuedAt->getTimestamp(),
                "exp" => $expire
            );
            $this->_mail = "";
            $this->_activate= "";
            $this->_device_token  = JWT::encode($payload, $protectedKey, 'RS256');
            return $this->_device_token;
        }


        public function to_array(bool $stocked_by_client = false) {


            $this->_is_using_name = $this->_is_using_name < 1 ? false : true;
            $this->_is_call_by_name = $this->_is_call_by_name < 1 ? false : true;
            $this->_is_tu = $this->_is_tu < 1 ? false : true;
            $this->_is_plural = $this->_is_plural < 1 ? false : true;
            if (isset($this->_is_double_auth)) {
                $this->_is_double_auth = $this->_is_double_auth < 1 ? false : true;
            }
            if (isset($this->_is_activated)) {
                $this->_is_activated = $this->_is_activated < 1 ? false : true;
            }
            
            $arr_to_convert = array (
                "id" => (int) $this->_id,
                "pseudo" => $this->_pseudo,
                "is_using_name" => $this->_is_using_name,
                "civility" => $this->_civility,
                "name" => $this->_name,
                "surname" => $this->_surname,
                "birthday" => $this->_birthday,
                "is_call_by_name" => $this->_is_call_by_name,
                "is_tu" => $this->_is_tu,
                "pronum" => $this->_pronum,
                "talk_about_me" => $this->_talk_about_me,
                "is_plural" => $this->_is_plural
            );
            if(trim($this->_mail) != "" && !$stocked_by_client) {
                $arr_to_convert["mail"] = $this->_mail;
            }
            if(is_bool($this->_is_double_auth)) {
                echo $this->_is_double_auth;
                $arr_to_convert["is_double_auth"] = $this->_is_double_auth;
            }
            if(trim($this->_tel) != "" && !$stocked_by_client) {
                $arr_to_convert["tel"] = $this->_tel;
            }
            if(trim($this->_activate) != "") {
                $arr_to_convert["activate"] = $this->_activate;
            }
            if(is_bool($this->_is_activated)) {
                $arr_to_convert["is_activated"] = $this->_is_activated;
            }
            if(trim($this->_token) != "" ) {
                $arr_to_convert["token"] = $this->_token;
            }
            if ($this->_house !== null) {
                $arr_to_convert["house"] = $this->_house;
            }
            if ($this->_is_root !== null) {
                $arr_to_convert["is_root"] = $this->_is_root;
            }
            if ($this->_is_premium !== null) {
                $arr_to_convert["is_premium"] = $this->_is_premium;
            }
            /*if (trim($this->_token) != "") {
                $arr_to_convert["refresh_tokens"] = $this->_refresh_tokens;
            }*/

            if ($stocked_by_client) {
                unset($arr_to_convert["civility"]);
                unset($arr_to_convert["name"]);
                unset($arr_to_convert["birthday"]);
                
            }
            return $arr_to_convert;

        }

        public function get_id() {
            return $this->_id; 
        }

        public function get_house() {
            return $this->_house;
            
        }

        public function token() {
            return $this->_token;
        }

        public function refresh_token() {
            return $this->_refresh_tokens ;
        }
        public function first_log_token() {
            return $this->_first_log_token ;
        }



        public function get_pseudo() {
            return $this->_pseudo;
        }


        public function get_is_using_name() {
            return $this->_is_using_name;
        }


        public function get_civility() {
            return $this->_civility;
        }


        public function get_name() {
            return $this->_name;
        }


        public function get_surname() {
            return $this->_surname;
        }


        public function get_birthday() {
            return $this->_birthday;
        }


        public function get_mail() {
            return $this->_mail;
        }


        public function get_pass() {
            return $this->_pass;
        }


        public function get_is_double_auth() {
            return $this->_is_double_auth;
        }


        public function get_tel() {
            return $this->_tel;
        }


        public function get_is_call_by_name() {
            return $this->_is_call_by_name;
        }


        public function get_is_tu() {
            return $this->_is_tu;
        }


        public function get_pronum() {
            return $this->_pronum;
        }


        public function get_talk_about_me() {
            return $this->_talk_about_me;
        }


        public function get_is_plural() {
            return $this->_is_plural;
        }
        public function get_premium_token() {
            return $this->_premium_token;
        }
        public function get_activate() {
            return $this->_activate;
        }
        public function get_is_root() {
            return $this->_is_root;
        }
        public function get_is_premium() {
            return $this->_is_premium;
        }

        public function get_device_token() {
            return $this->_device_token;
        }



	    public function set_house(int $new_house) {
		    $this->_house = $new_house;
	    }

	    public function set_id(int $new_id) {
		    $this->_id = $new_id;
	    }

        public function set_refresh_token(String $new_token) {
		    $this->_refresh_token = $new_token;
	    }


	


	


	
    }
    
    