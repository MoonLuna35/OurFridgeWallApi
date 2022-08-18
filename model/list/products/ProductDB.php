<?php 
    define('PRODUCT_DB_PATH', dirname(dirname(__FILE__)));

    include_once PRODUCT_DB_PATH.'/../connect.php';
    include_once PRODUCT_DB_PATH.'/../user/User.php';
    include_once PRODUCT_DB_PATH.'/../list/products/Product.php';
    include_once PRODUCT_DB_PATH.'/../list/type/Type.php';


    class ProductDB extends DB {

        public function select_by_house(User $user, int $offset, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $producs = array();
                $query = $this->_db->prepare("
                    SELECT 
                        p.id, 
                        p.label, 
                        p.unity,
                        t.name, 
                        t.logo_patern,
                        t.logo_color
                    FROM 
                        list_products AS p 
                    INNER JOIN 
                        list_product_types AS t 
                    ON
                        p.type = t.id 
                    WHERE 
                        p.house=:house
                    ORDER BY
                        label
                    LIMIT 10 OFFSET :offset"
                    
                );
                $query->bindValue(':house', $user->get_house());
                $query->bindValue(':offset', $offset, PDO::PARAM_INT);
                $query->execute();
                $this->_db->commit();

                $rep = $query->fetchAll(PDO::FETCH_ASSOC);
                if (sizeof($rep) > 0) {
                    for ($i = 0; $i < sizeof($rep); $i++) {
                        array_push($producs, new Product(array(
                            "id" => $rep[$i]["id"],
                            "label" => $rep[$i]["label"],
                            "type" => new Type(array(
                                "name" => $rep[$i]["name"],
                                "logo_color" =>$rep[$i]["logo_color"],
                                "logo_patern" =>$rep[$i]["logo_patern"]
                            )), 
                            "unity" => $rep[$i]["unity"]
                        )));
                    }
                    return $producs;
                }
                return false;
            }
            catch(Exeption $e) {
                $this->select_by_house($user, $offset, $h+1); 
            }
        }

        public function select_by_label(Product|ListLine $prdt, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("
                    SELECT 
                        p.id, 
                        p.label, 
                        p.unity,
                        t.name, 
                        t.logo_patern,
                        t.logo_color
                    FROM 
                        list_products AS p 
                    INNER JOIN 
                        list_product_types AS t 
                    ON
                        p.type = t.id 
                    WHERE 
                        p.house=:house
                    AND 
                        p.label=:label "
                );
                $query->execute(array(
                    ":label" => $prdt->get_label(), 
                    ":house" => $prdt->get_user()->get_house()
                ));
                $this->_db->commit();
                if ($query->rowCount() === 1 ) {
                    $rep = $query->fetchAll(PDO::FETCH_ASSOC);

                    return new Product(array(
                        "id" => $rep[0]["id"],
                        "label" => $rep[0]["label"],
                        "type" => new Type(array(
                            "name" => $rep[0]["name"],
                            "logo_color" =>$rep[0]["logo_color"],
                            "logo_patern" =>$rep[0]["logo_patern"]
                        )), 
                        "unity" => $rep[0]["unity"]
                    ));
                }
                else if ($query->rowCount() === 0) {
                    return "no product";
                }
                else {
                    return "two or more product";
                }
            }
            catch(Exeption $e) {
                $this->select_by_label($prdt, $h+1); 
            }
        }

        public function select_by_id(Product|ListLine $prdt, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("
                    SELECT 
                        p.id,
                        t.id AS type, 
                        p.label, 
                        p.unity,
                        t.name, 
                        t.logo_patern,
                        t.logo_color
                    FROM 
                        list_products AS p 
                    INNER JOIN 
                        list_product_types AS t 
                    ON
                        p.type = t.id 
                    WHERE 
                        p.house=:house
                    AND 
                        p.id=:id "
                );
                $query->execute(array(
                    ":id" => $prdt->get_id(), 
                    ":house" => $prdt->get_user()->get_house()
                ));
                $this->_db->commit();
                if ($query->rowCount() === 1 ) {
                    $rep = $query->fetchAll(PDO::FETCH_ASSOC);
                    
                    $prdt = new Product(array(
                        "id" => $rep[0]["id"],
                        "label" => $rep[0]["label"],
                        "type" => new Type(array(
                            "id" => $rep[0]["type"],
                            "name" => $rep[0]["name"],
                            "logo_color" =>$rep[0]["logo_color"],
                            "logo_patern" =>$rep[0]["logo_patern"]
                        )), 
                        "unity" => $rep[0]["unity"]
                    ));
                    
                    return $prdt;
                }
                else if ($query->rowCount() === 0) {
                    return "no product";
                }
                else {
                    return "two or more proprdt_is_existing(Product $prdtduct";
                }
            }
            catch(Exeption $e) {
                $this->select_by_id($prdt, $h+1); 
            }
        }

        public function select_by_type(Type $type, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $products = array();
                $query = $this->_db->prepare("
                    SELECT id, label, unity 
                    FROM list_products 
                    WHERE type=:type AND house=:house");
                $query->execute(array(
                    ":type" => $type->get_id(),
                    ":house" => $type->get_user()->get_house()
                ));
                $this->_db->commit();

                if ($query->rowCount() === 0) {
                    return false;
                }
                else {
                    $rep = $query->fetchAll(PDO::FETCH_ASSOC);
                    for ($i = 0; $i < sizeof($rep); $i++) {
                        array_push($products, new Product(array(
                            "id" => $rep[$i]["id"],
                            "label" => $rep[$i]["label"],
                            "type" => $type,
                            "unity" => $rep[$i]["unity"]
                        ))); 
                    }
                    return $products;
                }
            }
            catch(Exeption $e) {
                $this->select_by_type($type, $h+1); 
            }
        } 
        
        public function search(Product $product, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $producs = array();
                $query = $this->_db->prepare("
                    SELECT 
                        p.id, 
                        p.label, 
                        p.unity,
                        t.name, 
                        t.logo_patern,
                        t.logo_color
                    FROM 
                        list_products AS p 
                    INNER JOIN 
                        list_product_types AS t 
                    ON
                        p.type = t.id 
                    WHERE 
                            p.house=:house
                        AND
                            p.label LIKE :label
                    ORDER BY
                        label
                    LIMIT 10"
                    
                );
                $query->execute(array(
                    ':house' => $product->get_user()->get_house(),
                    ':label' => $product->get_label() . "%"
                ));
                $this->_db->commit();

                $rep = $query->fetchAll(PDO::FETCH_ASSOC);
                if (sizeof($rep) > 0) {
                    for ($i = 0; $i < sizeof($rep); $i++) {
                        array_push($producs, new Product(array(
                            "id" => $rep[$i]["id"],
                            "label" => $rep[$i]["label"],
                            "type" => new Type(array(
                                "name" => $rep[$i]["name"],
                                "logo_color" =>$rep[$i]["logo_color"],
                                "logo_patern" =>$rep[$i]["logo_patern"]
                            )), 
                            "unity" => $rep[$i]["unity"]
                        )));
                    }
                    return $producs;
                }
                return false;
            }
            catch(Exeption $e) {
                $this->select_by_house($user, $offset, $h+1); 
            }
        }

        public function insert_new_prdt(Product $prdt, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $req = $this->_db->prepare("
                    INSERT INTO 
                        list_products( 
                            label, 
                            type, 
                            unity, 
                            house)
                    SELECT
                        :label,
                        :type,
                        :unity,
                        :house
                    FROM list_product_types AS t 
                    WHERE t.id = :type
                    ON DUPLICATE KEY UPDATE list_products.id=list_products.id");
                $req->execute(array(
                    ":label" => $prdt->get_label(),
                    ":type"=> $prdt->get_type()->get_id(),
                    ":unity" => $prdt->get_unity(),
                    ":house" => $prdt->get_user()->get_house()
                )); 
                $this->_db->commit();   
                $inserted_id = $this->_db->lastInsertId();
                return $inserted_id;     
            }   
            catch(Exeption $e) {
                $this->insert_new_prdt($prdt, $h+1); 
            }
        }

        public function edit(Product $prdt, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                    $query = $this->_db->prepare("
                        UPDATE
                            list_products 
                        SET
                            label = :label, 
                            type = :type,
                            unity = :unity
                        WHERE 
                            id = :id
                        AND 
                            house = :house
                        AND 
                            EXISTS(SELECT 1 FROM list_product_types AS t WHERE t.id = :type) 
                        ");
                    $query->execute(array(
                        ":label" => $prdt->get_label(),
                        ":type"=> $prdt->get_type()->get_id(),
                        ":unity" => $prdt->get_unity(),
                        ":id" => $prdt->get_id(),
                        ":house" => $prdt->get_user()->get_house()
                    ));
                    $this->_db->commit(); 
                

                    if ($query->rowCount() === 1 ) {
                        return true;
                    }
                    else {
                        return false;
                    }
            }   
            catch(Exeption $e) {
                $this->edit($prdt, $h+1); 
            }
        }

        public function edit_type(Product $prdt, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                    $query = $this->_db->prepare("
                        UPDATE
                            list_products 
                        SET
                            type = :type,
                            unity = :unity
                        WHERE 
                            id = :id
                        AND 
                            house = :house
                        AND 
                            EXISTS(SELECT 1 FROM list_product_types AS t WHERE t.id = :type) 
                        ");
                    $query->execute(array(
                        ":type"=> $prdt->get_type()->get_id(),
                        ":unity" => $prdt->get_unity(),
                        ":id" => $prdt->get_id(),
                        ":house" => $prdt->get_user()->get_house()
                    ));
                    $this->_db->commit(); 
                

                    if ($query->rowCount() === 1 ) {
                        return true;
                    }
                    else {
                        return false;
                    }
            }   
            catch(Exeption $e) {
                $this->edit($prdt, $h+1); 
            }
        }

        public function delete(Product|ListLine $prdt, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("DELETE FROM list_products WHERE id = :id AND house = :house");
                $query->execute(array(
                    ":id" => $prdt->get_id(),
                    ":house" => $prdt->get_user()->get_house()
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
                $this->delete($prdt, $h+1); 
            }
        }

        public function prdt_is_existing(Product $prdt, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $type_db = "type";
                $product_type_db = "products_type";

                if ($prdt->get_type()->get_lang() !== "") { //SI le produit à un type généraliste (il contiens une langue) ALORS
                    $type_db .= "_gen";//on inserera l'id du type dans "type_gen"
                    $product_type_db .= "_gen";
                }
                $req = $this->_db->prepare("SELECT id FROM list_products WHERE label=:label AND house=:house");
                
                $req->execute(array(
                    ":label" => $prdt->get_label(),
                    ":house" => $prdt->get_user()->get_house()
                ));
                $this->_db->commit(); 
                if ($req->rowCount() > 0 ) { //SI l'article est dupliqué ALORS
                    return true;
                }
                return false; 
            }
            catch(PDOException $e) {
                $this->prdt_is_existing($prdt, $h+1); 
            }
        }

        public function prdt_is_existing_by_id(Product $prdt,int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("SELECT id FROM list_products WHERE id=:id AND house=:house");
                $query->execute(array(
                    ":id" => $prdt->get_id(), 
                    ":house" => $prdt->get_user()->get_house()
                ));
                $this->_db->commit(); 
                //SI le produit n'existe pas ou ne corresponds pas à l'utilisateur
                if ($query->rowCount() === 0 ) {
                    return false;
                }
                return true;
            }
            catch(Exeption $e) {
                $this->prdt_is_existing_by_id($prdt, $h+1); 
            }
        }

        public function count_by_house(User $user, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit;
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("SELECT count(id) AS qte_prdt FROM list_products WHERE house = :house");
                $query->execute(array(
                    ":house" => $user->get_house()
                ));
                $this->_db->commit(); 
                $rep = $query->fetchAll(PDO::FETCH_ASSOC);
                return $rep[0]["qte_prdt"];
            }
            catch(Exeption $e) {
                $this->prdt_is_existing_by_id($prdt, $h+1); 
            }
        }

        
    }