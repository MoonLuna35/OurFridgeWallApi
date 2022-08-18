<?php 
    define('USER_PATH', dirname(dirname(__FILE__)));

    require USER_PATH.'/connect.php';
    require USER_PATH.'/user/User.php';
    require USER_PATH.'/user/UserForAuth.php';
    require USER_PATH.'/list/list/List.php';

    class UserDb extends DB {
        public function generate_ativation_code($user) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ&-_@$';
            $charactersLength = strlen($characters);
            $code = '';
            for ($i = 0; $i < 50; $i++) {
                $code .= $characters[rand(0, $charactersLength - 1)];
            }
            $user->data->activate = $code;
        
        }
        public function user_is_exist(User $user) {
            $req = $this->_db->prepare("SELECT id FROM users WHERE id=:user");
            $req->execute(array(
                ":user" => $user->get_id()
            ));
            if ($req->rowCount() === 0 ) { //SI aucun utilisateur ne corresponds ALORS 
                return false;
            }
            return true;
        }

       public function log_user($mail, $pass) {
            $query = $this->_db->prepare("
                SELECT  
                    u.id AS id,
                    pseudo,
                    is_using_name, 
                    civility,
                    name, 
                    surname,
                    birthday,
                    mail,
                    pass,
                    is_call_by_name,
                    is_tu,
                    pronum, 
                    talk_about_me,
                    is_plural,
                    activate,
                    is_root,
                    house
                FROM 
                    users AS u
                WHERE 
                        mail=:mail
                    AND 
                        is_activated=true
            "); 
            $query->execute(array(
                ":mail" => $mail,
            ));
            if ($query->rowCount() != 1 ) {
                return "NO USER";
            }
            else {
                $user_arr = $query->fetch(PDO::FETCH_ASSOC);
                return new User($user_arr);
            }

       }

       public function is_membership_of_house(User $user, bool $is_root = false) {
           
            $query = $this->_db->prepare("
                SELECT 
                    id
                FROM
                    users
                WHERE
                    id = :user
                    AND 
                    house = :house
                    AND 
                    is_root = :is_root
            ");
            $query->execute(array(
                ":user" => $user->get_id(), 
                ":house" => $user->get_house(), 
                ":is_root" => $is_root
            ));
            if ($query->rowCount() === 1) {
                return true;
            }
            return false;
       }

        public function get_user_by_refresh($refresh, $id) {
            $query = $this->_db->prepare("
                SELECT  
                    u.id AS id,
                    pseudo,
                    is_using_name, 
                    civility,
                    name, 
                    surname,
                    birthday,
                    is_call_by_name,
                    is_tu,
                    pronum, 
                    talk_about_me,
                    is_plural,
                    activate,
                    is_root,
                    true AS is_premium,
                    house
                FROM 
                    users as u
                INNER JOIN refresh_tokens as rt
                    ON rt.user = u.id
                WHERE 
                    u.id = :id
                AND 
                    token  = :token
                AND 
                    is_activated=true
            "); 
            $query->execute(array(
                ":id" => $id,
                ":token" => $refresh
            ));
            if ($query->rowCount() != 1 ) {
                print_r($refresh);
                return "NO USER";
            }
            else {
                $user_arr = $query->fetch(PDO::FETCH_ASSOC);
                return new User($user_arr);
            }
        }
   
        public function get_user_by_first_log($id, $st_log_token) {
            $query = $this->_db->prepare("
                SELECT  
                    u.id,
                    pseudo,
                    is_using_name, 
                    civility,
                    name, 
                    surname,
                    birthday,
                    is_call_by_name,
                    is_tu,
                    pronum, 
                    talk_about_me,
                    is_plural,
                    is_root,
                    true AS is_premium,
                    activate
                FROM 
                    users as u
                INNER JOIN 
                    user_new_log as nl
                ON 
                    nl.user = u.id
                WHERE 
                    u.id = :id
                AND 
                    token  = :token
                AND 
                    is_activated=true
            "); 
            $query->execute(array(
                ":id" => $id,
                ":token" => $st_log_token
            ));
            if ($query->rowCount() != 1 ) {
                 return "NO USER";
            }
            else {
                $user_arr = $query->fetch(PDO::FETCH_ASSOC);
                return new User($user_arr);
            }
        }
        
        public function select_house_to_auth_lsit(ShopList $list, User $user) {
            $users_to_return =  array();
            $query = $this->_db->prepare("
                SELECT  
                    id,
                    pseudo,
                    is_using_name, 
                    name, 
                    surname,
                    pronum, 
                    talk_about_me,
                    is_root AS is_grey,
                    CASE 
                        WHEN id IN (
                            SELECT 
                                user 
                            FROM
                                list_auths
                            WHERE
                                user = id
                                AND 
                                list = :list
                            ) 
                        THEN 1
                        WHEN is_root = true THEN 1
                        ELSE 0
                    END AS is_checked
                FROM 
                    users
                WHERE 
                    house  = :house
                    AND 
                    id != :user
            ");
            $query->execute(array(
                ":house" => $user->get_house(),
                ":user" => $user->get_id(),
                ":list" => $list->get_id()
            ));
            if ($query->rowCount() === 0 ) {
                return false;
            }
           else {
                $user_arr = $query->fetchAll(PDO::FETCH_ASSOC);
                for ($i = 0; $i < sizeof($user_arr); $i++) {
                    array_push($users_to_return, new UserForAuth($user_arr[$i]));
                }
                
                return $users_to_return;
           }
           return false;
           
        }

        public function select_by_id(int $id, int $h=0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("
                    SELECT  
                        id,
                        pseudo,
                        is_using_name, 
                        civility,
                        name, 
                        surname,
                        birthday,
                        is_call_by_name,
                        is_tu,
                        pronum, 
                        talk_about_me,
                        is_plural,
                        is_root,
                        true AS is_premium,
                        house
                    FROM 
                        users as u
                    WHERE 
                        u.id = :id
                    AND 
                        is_activated=true
                "); 
                $query->execute(array(
                    ":id" => $id
                ));
                $this->_db->commit(); 
                if ($query->rowCount() != 1 ) {
                    return "NO USER";
                }
                else {
                    $user_arr = $query->fetch(PDO::FETCH_ASSOC);
                    return new User($user_arr);
                }
            }
            catch(Exeption $e) {
                $this->select_by_idc($type, $h+1); 
            }
        } 

        public function delete_first_log_token($user, $token) {
            $query = $this->_db->prepare ("DELETE FROM user_new_log WHERE token = :token AND user = :id"); 
            $query->execute(array(
                ":token" => $token,
                ":id" => $user->get_id()
            ));
        }

       public function add_new_refresh_token ($old_token, $token, $user, int $h=0) {
        if ($h === $_ENV["MAX_TRY"]) {
            header('HTTP 503 Service Unavailable');
            exit; 
        }
            if (isset($_COOKIE["user" . $user->get_id()])) {
                $device_token = $_COOKIE["user" . $user->get_id()];
            }
            else {
                $device_token = $user->get_device_token();
            }

            $now   = new DateTime();
            $expire = $now->modify($_ENV["REFRESH_STAY_ALIVE"]);
            $now  = new DateTime();
            $used_expire = $now->modify("+ 2minutes");

            try {
                $this->_db->beginTransaction();
                $is_in_db_query =  $this->_db->prepare("
                    DELETE FROM 
                        refresh_tokens
                    WHERE 
                        user=:user
                        AND 
                        DATEDIFF(NOW(), expire_at) >= 0

                ");
                $is_in_db_query->execute(array(
                    ":user" => $user->get_id()
                ));

                $is_in_db_query =  $this->_db->prepare("SELECT expire_at FROM refresh_tokens WHERE user = :user AND token = :token");
                $is_in_db_query->execute(array(
                    ":user" => $user->get_id(),
                    ":token" => $old_token
                ));
                if ($is_in_db_query->rowCount() === 1) { //SI le token est dans la base de donnée 
                        $update_query = $this->_db->prepare(" 
                            UPDATE 
                                refresh_tokens 
                            SET 
                                expire_at = :expire_at, 
                                device = :device_token
                            WHERE 
                                    user = :user 
                                AND 
                                    token = :old_token"
                        );
                        $update_query->execute(array(
                            ":user" => $user->get_id(),
                            ":expire_at" => $used_expire->format('Y-m-d H:i:s'),
                            ":old_token" => $old_token,
                            ":device_token" => $device_token
                        ));
                }
                
                
                $insert_query =  $this->_db->prepare("
                    INSERT INTO refresh_tokens (token, device, user, expire_at)
                    VALUES (
                        :token,
                        :device_token,
                        :user,
                        :expire_at 
                        )
                        ON DUPLICATE KEY UPDATE token = token 
                ");
                $insert_query->execute(array(
                    ":user" => $user->get_id(),
                    ":token" => $token,
                    ":expire_at" => $expire->format('Y-m-d H:i:s'),
                    ":device_token" => $device_token
                )); 
                $this->_db->commit();   
            }
            catch(Exeption $e) {
                $this->add_new_refresh_token ($old_token, $token, $user, $h+1);
            }
                
            
       }

       public function revoke_refreshs_token(User $user, int $h=0) {
        if ($h === $_ENV["MAX_TRY"]) {
            header('HTTP 503 Service Unavailable');
            exit; 
        }
        try {
            $this->_db->beginTransaction();
            $device = $_COOKIE["user" . $user->get_id()];
            $is_in_db_query =  $this->_db->prepare("
                        DELETE FROM 
                            refresh_tokens
                        WHERE 
                            user=:user
                            AND 
                            device=:device_token

                ");
                $is_in_db_query->execute(array(
                    ":user" => $user->get_id(),
                     ":device_token" => $device
                ));
                $this->_db->commit(); 
            }
            catch(Exeption $e) {
                $this->revoke_refreshs_token($user, $h+1);
            }
       }

       public function add_new_first_log_token($user) {
            $now   = new DateTime();
            $expire = $now->modify("+30 minutes");

            $query =  $this->_db->prepare("INSERT INTO user_new_log(token, user, expire_at) VALUE(:token, :user, :expire_at)"); 
            $query->execute(array(
                ":token" => $user->first_log_token(),
                ":user" => $user->get_id(),
                ":expire_at" => $expire->format('Y-m-d H:i:s')

            ));
       } 
    }