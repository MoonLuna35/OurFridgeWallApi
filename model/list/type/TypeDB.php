<?php 

    define('TYPE_PATH', dirname(dirname(__FILE__)));
    include_once TYPE_PATH.'/../connect.php';
    include_once TYPE_PATH.'/../user/User.php';
    include_once TYPE_PATH.'/../list/type/Type.php';

    class TypeDB extends DB {
        public function type_is_deletable(Type $type, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("
                    SELECT 
                        id
                    FROM list_products 
                    WHERE 
                        type = :type"
                );
                $query->execute(array(
                    ":type"=>$type->get_id()
                ));
                $this->_db->commit();
                if($query->rowCount() === 0) {
                    
                    return true;
                }
                else {
                    return false;
                }
            
            }
            catch(Exeption $e) {
                $this->type_is_deletable($type, $h+1); 
            }
        }

        public function select_img_by_id(Type $type) {
            $product_type_db = "list_product_types";
            if ($type->get_lang() !== null) {
                
                $product_type_db .= "_gen";
            }
            $req = $this->_db->prepare("SELECT id FROM $product_type_db WHERE id=:id");//On regarde dans la table type
            $req->execute(array(
                ":id" => $type->get_id()
            ));
            $r = $req->fetchAll(PDO::FETCH_ASSOC);
            if (isset($r[0]["id"])) {
                return new Type(array("id" => $r[0]["id"]));
            }
            return false;
        }

        public function type_is_existing(Type $type, User $user, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("SELECT * FROM list_product_types WHERE id = :id AND house = :house");
                $query->execute(array(
                    ":id" => $type->get_id(),
                    ":house" => $user->get_house()
                ));
                $this->_db->commit();
                if($query->rowCount() !== 0) {
                    return true;
                } 
                else {
                    return false;
                }
            }
            catch(Exeption $e) {
                $this->type_is_existing($type, $user, $h+1); 
            }
                
            
            
        }

        public function add_type(Type &$type) {
            $query = $this->_db->prepare("
                INSERT INTO list_product_types(
                    name, 
                    house,
                    logo_patern,
                    logo_color 
                ) 
                VALUES(
                    :name,
                    :house,
                    :logo_patern,
                    :logo_color 
                )
                ON DUPLICATE KEY UPDATE list_product_types.id=list_product_types.id"
            );
            $query->execute(array(
                ":name" => $type->get_name(),
                ":house" => $type->get_user()->get_house(), 
                ":logo_patern" => $type->get_logo_patern(),
                ":logo_color" => $type->get_logo_color(),
                
            ));
            if ($query->rowCount() === 1) {
                $type->set_id($this->_db->lastInsertId());
                return $type;
            }
            return false;
        }

        public function delete_type(Type $type, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("
                    DELETE FROM list_product_types 
                    WHERE id = :id 
                    AND house=:house"
                );
                $query->execute(array(
                    ":id" => $type->get_id(),
                    ":house" => $type->get_user()->get_house()
                ));
                $this->_db->commit();
                if ($query->rowCount() === 1) {
                    return true;
                }
                return false;
            }
            catch(Exeption $e) {
                delete_type($type, $h+1);
            }
        }

        public function is_duplicate_type(Type $type) {
            $query = $this->_db->prepare("
                SELECT id 
                FROM list_product_types
                WHERE id = :id AND name = :name AND house = :house"
            );
            $query->execute(array(
                ":name" => $type->get_name(),
                ":id" => $type->get_id(),
                ":house" => $type->get_user()->get_house()
            ));
            if ($query->rowCount() === 0) {
                return false;
            }
            return true;
        }

        public function update_type(Type $type, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit; 
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("
                    UPDATE list_product_types 
                    SET 
                        name=:name,
                        logo_patern=:logo_patern,
                        logo_color=:logo_color
                    WHERE id = :id AND house = :house"
                );
                $query->execute(array(
                    ":name" => $type->get_name(),
                    ":logo_patern" => $type->get_logo_patern(),
                    ":logo_color" => $type->get_logo_color(),
                    ":id" => $type->get_id(),
                    ":house" => $type->get_user()->get_house()
                ));
                $this->_db->commit();
                if ($query->rowCount() === 0) {
                    return false;
                }
                return true;
            }
            catch(Exeption $e) {
                $this-> update_type($type, $h+1); 
            }
           
        }

        public function type_is_the_same(Type $type, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit; 
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("
                    SELECT 
                        id
                    FROM
                        list_product_types
                    WHERE 
                        id = :id 
                        AND
                        name = :name
                        AND
                        house = :house 
                        AND 
                        logo_patern = :logo_patern
                        AND 
                        logo_color = :logo_color
                ");
                $query->execute(array(
                    ":id"=>$type->get_id(),
                    ":name"=>$type->get_name(),
                    ":house"=>$type->get_user()->get_house(),
                    ":logo_patern"=>$type->get_logo_patern(),
                    ":logo_color"=>$type->get_logo_color()
                ));
                $this->_db->commit();
                if ($query->rowCount() === 1) {
                    return true;
                }
                else {
                    return false;
                }
            }
            catch(Exeption $e) {
                $this->type_is_the_same($type, $user, $h+1); 
            }
        }

        public function select_all_custom_types(User $user, int $already_printed) {
            $types = array();
            $query = $this->_db->prepare("
                SELECT id, name, logo_patern, logo_color 
                FROM list_product_types
                WHERE house = :house 
                ORDER BY name
                LIMIT 50 OFFSET :already_printed

            ");
            $query->bindValue(':house', $user->get_house());
            $query->bindValue(':already_printed', $already_printed, PDO::PARAM_INT);
            $query->execute();

            if($query->rowCount() > 0) {
                $arr_rep = $query->fetchAll(PDO::FETCH_ASSOC);

                for ($i = 0; $i < sizeof($arr_rep); $i++) {
                    array_push($types, new Type($arr_rep[$i]));
                }
                return $types;
            }
            return false;
        }

        public function select_custom_type_by_id(Type $type) {
            $query = $this->_db->prepare("
                SELECT name, logo_patern, logo_color
                FROM list_product_types
                WHERE house = :house AND id=:id
            ");
            
            $query->execute(array(
                ":house" => $type->get_user()->get_house(),
                ":id" => $type->get_id()
            ));
            if ($query->rowCount() === 1) {
                $arr_rep = $query->fetchAll(PDO::FETCH_ASSOC);
                if (isset($arr_rep[0]["name"])) {
                    $type->set_name($arr_rep[0]["name"]);
                    $type->set_logo_patern($arr_rep[0]["logo_patern"]);
                    $type->set_logo_color($arr_rep[0]["logo_color"]);
                    return $type;
                }
            }
            return false;
        }

        public function search(Type $tye, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit; 
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("
                    SELECT name, logo_patern, logo_color
                    FROM list_product_types
                    WHERE house = :house 
                    AND label LIKE ':label%'
                ");
                
                $query->execute(array(
                    ":house" => $type->get_user()->get_house(),
                    ":label" => $type->get_label()
                ));
                $this->_db->commit();
                if ($query->rowCount() === 1) {
                    $arr_rep = $query->fetchAll(PDO::FETCH_ASSOC);
                    if (isset($arr_rep[0]["name"])) {
                        $type->set_name($arr_rep[0]["name"]);
                        $type->set_logo_patern($arr_rep[0]["logo_patern"]);
                        $type->set_logo_color($arr_rep[0]["logo_color"]);
                        return $type;
                    }
                }
                return false;
            }
            catch(Exeption $e) {
                $this->type_is_the_same($type, $user, $h+1); 
            }
        }
     } 