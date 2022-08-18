<?php 
    define('CUPBOARD_PATH', dirname(dirname(__FILE__)));

    include_once CUPBOARD_PATH.'/../connect.php';
    include_once CUPBOARD_PATH.'/../user/User.php';
    include_once CUPBOARD_PATH.'/../list/cupboard/Cupboard.php';
    include_once CUPBOARD_PATH.'/../list/cupboard/CupboardLine.php';
    include_once CUPBOARD_PATH.'/../list/products/Product.php';
    include_once CUPBOARD_PATH.'/../list/type/Type.php';

    class CupboardDB extends DB { 

        public function prdt_is_in_cupboard (CupboardLine $prdt, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit; 
            }
            try {
                $this->_db->beginTransaction();
                $query = $this->_db->prepare("SELECT * FROM list_cupboard WHERE product=:product");
                $query->execute(array(":product" => $prdt->get_id()));
                $this->_db->commit();
                if ($query->rowCount() === 0) {
                    return false;
                }
                else {
                    
                    return true;
                }
            }
            catch(Exeption $e) {
                $this->prdt_is_in_cupboard($prdt, $h+1); 
            }
        }

        public function select_cupboar_of_user(User $user, $order="label") {
            $cupboard = new Cupboard();
            if (!($order === "label" || $order === "id")) {
                return false;
            }
            $query = $this->_db->prepare("
                SELECT  
                    p.id, 
                    p.label, 
                    p.unity, 
                    t.name, 
                    t.logo_color,
                    t.logo_patern,  
                    c.qte
                FROM 
                    list_products AS p, 
                    list_cupboard AS c,
                    list_product_types AS t 
                WHERE 
                    p.type = t.id 
                AND 
                    c.product = p.id
                AND
                    p.house = :house 
                ORDER BY 
                    $order
            ");
            $query->execute(array(
                ":house" => $user->get_house()
            ));

                $rep = $query->fetchAll(PDO::FETCH_ASSOC);
                
                for($i = 0; $i < sizeof($rep); $i++) {
                    $cupboard->add_product(new CupboardLine(array(
                        "id" => $rep[$i]["id"],
                        "label" => $rep[$i]["label"],
                        "type" => new Type(array(
                            "name" => $rep[$i]["name"],
                            "logo_patern" => $rep[$i]["logo_patern"],
                            "logo_color" => $rep[$i]["logo_color"]
                        )),
                        "unity" => $rep[$i]["unity"],
                        "qte" => $rep[$i]["qte"]
                    )));
                }
            
            return $cupboard;
        }

        public function push_cupboard(CupboardLine $prdt) {
            $query_is_already_in = $this->_db->prepare("SELECT product FROM list_cupboard WHERE product=:product");
            $query_is_already_in->execute(array(":product" => $prdt->get_id()));
            if ($query_is_already_in->rowCount() !== 0) {
                return false;
            }

            $query = $this->_db->prepare("INSERT INTO list_cupboard(product) VALUES(:product)");
            $query->execute(array(":product" => $prdt->get_id()));

            return true;

        }

        public function pop_cupboard(CupboardLine $prdt) {
            $query = $this->_db->prepare("
                DELETE 
                    c 
                FROM 
                    list_cupboard AS c 
                INNER JOIN list_products AS p 
                    ON c.product = p.id  
                WHERE 
                    c.product=:product 
                    AND 
                    p.house=:house"); 
            $query->execute(array(
                ":product" => $prdt->get_id(),
                ":house" => $prdt->get_user()->get_house()
            ));
            if ($query->rowCount() === 1) {
                return true;
            }
            else {
                return false;
            }
            
        }

        public function update_qte(CupboardLine $prdt) {
            $query = $this->_db->prepare("
                UPDATE 
                    list_cupboard AS c 
                INNER JOIN list_products as p 
                    ON c.product = p.id 
                SET c.qte=:qte  
                WHERE 
                    c.product=:product 
                AND 
                    p.house=:house"); 
            $query->execute(array(
                ":qte" => $prdt->get_qte(),
                ":product" => $prdt->get_id(),
                ":house" => $prdt->get_user()->get_house()
            ));
            if($query->rowCount() === 1) {
                return true;
            }
            return false;
        }
        /* 
        * cette fonction est appelée quand l'utilisateur veux fusionner la list l 
        * ou modifie la dite fusion
        * Elle permet de savoir 
        *   si une ligne sera grisee : 
        *       si il elle est dans la list mais ajoutées par l'utilisateur
        *   ou cochee : 
                si elle est dans la liste mais vient d'une precedente fusion
        *CupboardLine[]
       * retourne un cupboard.
        */
        public function select_from_list(ShopList $list) {
            $cupboard = new Cupboard();
            //Recuperation des lignes contenues a la fois dans cupboard et dans la liste l
            $query = $this->_db->prepare("
                SELECT
                    product_id,
                    status,
                    qte
                FROM
                    list_cupboard_from_list
                WHERE  
                    house = :house 
                    AND 
                    list = :list 
                ORDER BY product_id 
            ");
            $query->execute(array(
                ":house" => $list->get_author()->get_house(), 
                ":list" => $list->get_id()
            ));
            $rep = $query->fetchAll(PDO::FETCH_ASSOC);
            //Recuperation de cupboard
            $all_cup = $this->select_cupboar_of_user($list->get_author(), "id")->get_products();
            $j = 0;    
            for($i = 0; $i < sizeof($all_cup); $i++) { //POUR TOUTE ligne du placard FAIRE
                $cupboardLine_arr = array( //On instancie la ligne
                    "id" => $all_cup[$i]->get_id(),
                    "label" => $all_cup[$i]->get_label(),
                    "type" => new Type(array(
                        "name" => $all_cup[$i]->get_type()->get_name(),
                        "logo_color" => $all_cup[$i]->get_type()->get_logo_color(),
                        "logo_patern" => $all_cup[$i]->get_type()->get_logo_patern(),
                    )),
                    "unity" => $all_cup[$i]->get_unity(),
                    "qte" => $all_cup[$i]->get_qte()
                );
                if (
                    $j < sizeof($rep) &&
                    $all_cup[$i]->get_id() === (int)$rep[$j]["product_id"]
                ) { //SI elle est deja dans la liste ALORS  
                    if ($rep [$j]["status"] === "grey") { //SI elle a ete ajoutee par l'utilisateur ALORS 
                        $cupboardLine_arr["is_grey"] = true; //elle sera grisee
                    }
                    else if ($rep [$j]["status"] === "checked") { //SINON SI elle a ete precedement ajoutee ALORS 
                        $cupboardLine_arr["is_checked"] = true; //elle sera checkee
                    }
                    $cupboardLine_arr["qte"] = $rep [$j]["qte"];

                    $j++;
                }
                
                $cupboard->add_product(new CupboardLine($cupboardLine_arr));
            }
            return $cupboard;
        }
        //fusionner avec des element de cupboard
        public function fuse(ShopList $list, $cupboard, int $h=0) {
            
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit; 
            }
            try {
                $this->_db->beginTransaction();
                
                for ($i = 0; $i < sizeof($cupboard); $i++) {
                    $query =  $this->_db->prepare("
                        INSERT INTO 
                            list_lines(
                                list,
                                prdt,
                                qte,
                                is_from_cupboard,
                                house
                            )
                        VALUES(
                            :list, 
                            :prdt,
                            :qte,
                            true,
                            :house
                        )
                    ");
                    $query->execute(array(
                        ":list" => $list->get_id(),
                        ":prdt" => $cupboard[$i]->get_id(),
                        ":qte" => $cupboard[$i]->get_qte(),
                        ":house" => $list->get_author()->get_house()
                    ));
                    if ($query->rowCount() !== 1) {
                        
                        return false;
                    }
                    
                }
                $this->_db->commit();
                return true;
            }
            catch(Exeption $e) {
                $this->fusion_remover($list, $h+1); 
            }
            
        }

        public function fusion_remover(ShopList $list, int $h = 0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit; 
            }
            try {
                $this->_db->beginTransaction();
                $query =  $this->_db->prepare("
                    DELETE FROM
                        list_lines
                    WHERE
                        list = :list 
                        AND
                        is_from_cupboard = true
                        AND
                        house = :house
                ");
                $query->execute(array(
                    ":list" => $list->get_id(),
                    ":house" => $list->get_author()->get_house()
                ));
                $query_ctrl =  $this->_db->prepare("
                    SELECT 
                        * 
                    FROM
                        list_lines
                    WHERE
                        list = :list 
                        AND
                        is_from_cupboard = true
                        AND
                        house = :house
                ");
                $query_ctrl->execute(array(
                    ":list" => $list->get_id(),
                    ":house" => $list->get_author()->get_house()
                ));
                $this->_db->commit();
                if($query_ctrl->rowCount() === 0) {
                    return true;
                }
                else {
                    return false;
                }
                
                
                
            }
            catch(Exeption $e) {
                $this->fusion_remover($list, $h+1); 
            }
        }
    }
            