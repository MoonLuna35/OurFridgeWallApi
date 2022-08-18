<?php 

    define('LIST_DB_PATH', dirname(dirname(__FILE__)));
    include_once LIST_DB_PATH.'/../connect.php';
    include_once LIST_DB_PATH.'/../user/User.php';
    include_once LIST_DB_PATH.'/list/ListLine.php';
/* 20/10/21 : Zoe 
*
*/
    class ListDB extends DB {
        //Liste L :

        //la liste existe elle ? 
        public function list_is_existing(ShopList $list) {
            $query = $this->_db->prepare("
                SELECT id FROM lists WHERE id = :id AND house = :house

            ");
            $query->execute(array(
                ":id" => $list->get_id(),
                ":house" => $list->get_author()->get_house(),
            ));
            if ($query->rowCount() === 1) {
                return true;
            }
            else {
                return false;
            }  
            
        }
        public function select_author(Shoplist $list) {
            $query = $this->_db->prepare("
                SELECT house, author FROM lists WHERE id = :list
            "); 
            $query->execute(array(
                ":list" => $list->get_id()
            ));
            if ($query->rowCount() !== 1) {
                return false;
            }
            else {
                $rep = $query->fetch(PDO::FETCH_ASSOC);
                $outList = clone $list;
                $outList->set_author(new User(array(
                    "id" => $rep["author"],
                    "house" => $rep["house"] 
                )));
                return $outList;
            }

        }

        //nouvelles listes 
        public function new_list(ShopList $list, int $h=0) {
            $query = $this->_db->prepare("
                INSERT INTO 
                    lists (
                        house, 
                        author, 
                        name,
                        description,
                        is_protected 
                    ) 
                VALUES(
                    :house, 
                    :author, 
                    :name,
                    :description,
                    :is_protected
                )
                ON DUPLICATE KEY UPDATE id = id   
            ");
            $query->execute(array(
                ":house" => $list->get_author()->get_house(),
                ":author" => $list->get_author()->get_id(),
                ":name" => $list->get_name(),
                ":description" => $list->get_desc(),
                ":is_protected" => $list->get_is_protected()? 1 : 0             
            ));
            if ($query->rowCount() === 1) {
                return $this->_db->lastInsertId();                
            } 
            else {
                return false;
            }
        }
        //nouvelle liste quand l'utilisateur cree sa maison
        public function new_list_house_create(ShopList $list) {
            $query = $this->_db->prepare("
                INSERT INTO lists (house, author, name) 
                VALUES(:house, :author, 'first_list')    
            ");
            $query->execute(array(
                ":house" => $list->get_author()->get_house(),
                ":author" => $list->get_author()->get_id()
                
            ));
            if ($query->rowCount() === 1) {
                return true;                
            } 
            else {
                return false;
            }
        }
        //archiver
        public function archive(ShopList $list) {
            $query = $this->_db->prepare("
                UPDATE lists 
                SET is_archived = 1
                WHERE id = :id
                AND house = :house
            ");
            $query->execute(array(
                ":id" => $list->get_id(),
                ":house" => $list->get_author()->get_house()            
            ));
            if ($query->rowCount() !== 1) {
                $query = $this->_db->prepare("
                    SELECT id
                    FROM lists 
                    WHERE 
                            id = :id
                        AND 
                            house = :house
                        AND 
                            is_archived = 1
                ");
                $query->execute(array(
                    ":id" => $list->get_id(),
                    ":house" => $list->get_author()->get_house(),           
                ));
                if ($query->rowCount() !== 0) {
                    return "list already archived";
                }
                else {
                    return false;
                }    
            }
            else {
                return true;
            }
        }
        //desarchiver
        public function unarchive(ShopList $list) {
            $query = $this->_db->prepare("
                UPDATE lists 
                SET is_archived = 0
                WHERE id = :id
                AND house = :house
            ");
            $query->execute(array(
                ":id" => $list->get_id(),
                ":house" => $list->get_author()->get_house()            
            ));
            if ($query->rowCount() !== 1) {
                $query = $this->_db->prepare("
                    SELECT id
                    FROM lists 
                    WHERE 
                        id = :id
                    AND 
                        house = :house
                    AND 
                        is_archived = 0
                ");
                $query->execute(array(
                    ":id" => $list->get_id(),
                    ":house" => $list->get_author()->get_house(),           
                ));
                if ($query->rowCount() !== 0) {
                    return "list already unarchived";
                }
                else {
                    return false;
                }    
            }
            else {
                return true;    
            }
        }
        //selectioner la liste l 
        public function select_list(ShopList $list) {
            $list_arr = array();
            $lines = array();
            $loaded_list = null; 

            //On recupere les info de la liste
            $query_list = $this->_db->prepare(" 
                SELECT 
                    l.id,
                    l.name, 
                    l.description,
                    u.id AS uid,
                    u.pseudo,
                    u.name AS uname,
                    u.civility,
                    u.surname,
                    u.is_call_by_name,
                    u.is_using_name, 
                    l.date_create, 
                    l.is_protected, 
                    l.is_archived 
                FROM 
                    lists AS l
                INNER JOIN 
                    users AS u
                    ON
                    u.id = l.author

                WHERE  
                    l.id = :id
            
            ");
            $query_list->execute(array(
                ":id" => $list->get_id()
            ));
            if ($query_list->rowCount() !== 1) { //SI il n'y a aucune liste ALORS
                return false; //On renvoie false
            }
            else { //SINON 
                //SI la liste est protegee ALORS
                    //On recupere les utilisateurs authorises
                //On recupere les lignes
                $list_arr = $query_list->fetch(PDO::FETCH_ASSOC);
                $list_arr["author"] = new User(array( //typage de author
                    "id" => $list_arr["uid"],
                    "pseudo" => $list_arr["pseudo"],
                    "name" => $list_arr["uname"],
                    "civility" => $list_arr["civility"],
                    "surname" => $list_arr["surname"],
                    "is_call_by_name" => $list_arr["is_call_by_name"],
                    "is_using_name" => $list_arr["is_using_name"], 
                ));
                $query_lines = $this->_db->prepare(" 
                    SELECT 
                        p.id, 
                        p.label, 
                        qte, 
                        is_striked,
                        is_from_cupboard, 
                        unity, 
                        t.name AS type, 
                        t.logo_patern,
                        t.logo_color
                    FROM list_lines AS l 
                    INNER JOIN list_products AS p 
                        ON p.id = l.prdt
                    INNER JOIN list_product_types AS t
                        ON t.id = p.type
                    WHERE list = :id 
                    ORDER BY 
                        p.label
                ");
                $query_lines->execute(array(
                    ":id" => $list->get_id(),
                ));
                $lines_arr = $query_lines->fetchAll(PDO::FETCH_ASSOC);
                for ($i = 0; $i < sizeof($lines_arr); $i++) { //On instancie les lignes
                    array_push($lines, new ListLine(array(
                        "id" => $lines_arr[$i]["id"],
                        "label" => $lines_arr[$i]["label"],
                        "qte" => $lines_arr[$i]["qte"],
                        "unity" => $lines_arr[$i]["unity"],
                        "is_striked" => $lines_arr[$i]["is_striked"],
                        "is_from_cupboard" => $lines_arr[$i]["is_from_cupboard"],
                        "type" => new Type(array(
                            "name" => $lines_arr[$i]["type"],
                            "logo_patern" => $lines_arr[$i]["logo_patern"],
                            "logo_color" => $lines_arr[$i]["logo_color"],
                        ))
                    )));
                }
                $list_arr["lines"] = $lines;
                return new ShopList($list_arr);
            }
        }


        //supprimer 
        public function delete(ShopList $list, int $h=0)  {
            //On supprime les lignes 
            $query = $this->_db->prepare("
                DELETE FROM 
                    list_lines
                WHERE 
                    list = :list
            ");
            $query->execute(array(
                ":list" => $list->get_id()
            ));
            
            //On supprime les authorisation
            $query = $this->_db->prepare("
                DELETE FROM 
                    list_auths
                WHERE 
                    list = :list
            ");
            $query->execute(array(
                ":list" => $list->get_id()
            ));
            //On supprime la liste
            $query = $this->_db->prepare("
                DELETE FROM 
                    lists
                WHERE 
                    id = :list
            ");
            $query->execute(array(
                ":list" => $list->get_id()
            ));
            if($query->rowCount() === 1) {
                return true;
            }
            return false;
        }
        //renomer
        public function rename(ShopList $list) {
            $query =  $this->_db->prepare("
                UPDATE 
                    lists 
                SET
                    name =  :name
                WHERE 
                    id = :id 
                    AND
                    house = :house
            ");
            $query->execute(array(
                ":name" => $list->get_name(),
                ":id" => $list->get_id(),
                ":house" => $list->get_author()->get_house()
            ));
            if ($query->rowCount() !== 1) {
                return false;
            }
            return true; 
        }
        /*
        *   Cette fonction est appellee quand l'utilisateur veux rendre prive sa liste. 
        *    les admins pourront toujours voir
        *   l'utilisateur auteur de la liste poura choisir les utilisateurs ou groupe de sa maison 
        *   qui auront acces a la liste
        *
        */ 
        

        /*-------------------*/
        //Auth
        public function to_private(ShopList $list, User $current_user) { 
            $users = $list->get_users_auth();
            $query = $this->_db->prepare("
                UPDATE 
                    lists
                SET
                    is_protected = true
                WHERE 
                    id = :list
                    AND 
                    author = :author
                    AND 
                    house = :house

            "); 
            $query->execute(array(
                ":author" => $list->get_author()->get_id(),
                ":list" => $list->get_id(),
                ":house" => $list->get_author()->get_house()
            ));
            if ($query->rowCount() !== 1) {
                return false;
            }
            for ($i = 0; $i  < sizeof($users); $i++) {  
                $query = $this->_db->prepare("
                    INSERT INTO 
                        list_auths(
                            user, 
                            list
                        )
                    VALUES(
                        :user, 
                        :list
                    )
                    ON DUPLICATE KEY UPDATE user = user
                ");
                $query->execute(array(
                    ":user" => $users[$i]->get_id(),
                    ":list" => $list->get_id()
                ));
                if ($query->rowCount() !== 1) {
                    return false;
                }
            }
            
            return true ;   
            
        }
        //rendre public 
        public function to_public(ShopList $list) {
            $query = $this->_db->prepare("
                UPDATE 
                    lists
                SET
                    is_protected = false
                WHERE 
                    id = :list
                    AND 
                    author = :author
                    AND 
                    house = :house

            "); 
            $query->execute(array(
                ":author" =>$list->get_author()->get_id(),
                ":list" => $list->get_id(),
                ":house" => $list->get_author()->get_house()
            ));
            if ($query->rowCount() !== 1) {
                return false;
            }
            $query = $this->_db->prepare("
                DELETE FROM
                    list_auths
                WHERE 
                    list = :list
            ");
            $query->execute(array(
                ":list" => $list->get_id()
            ));
            
            return true;
        }

        public function is_private(ShopList $list) {
            $query = $this->_db->prepare("
                SELECT 
                    author 
                FROM 
                    lists 
                WHERE 
                        id = :id
                    AND  
                        is_protected = true
            ");
            $query->execute(array(
                ":id" => $list->get_id()
            ));
            if ($query->rowCount() === 1) {
                $rep = $query->fetch(PDO::FETCH_ASSOC);
                $list_out = clone $list;
                $list_out->set_author(new User(array(
                    "id" => $rep["author"]
                ))); 
                return $list_out;
            }
            return false;
        }

        //ajouter des utilisateurs authorisés quand la liste est privée 
        public function modify_auth(ShopList $list) {
            $query = $this->_db->prepare("
                UPDATE 
                    lists
                SET
                    is_protected = true
                WHERE 
                    id = :list
                    AND 
                    author = :author
                    AND 
                    house = :house

            "); 
            $query->execute(array(
                ":author" =>$list->get_author()->get_id(),
                ":list" => $list->get_id(),
                ":house" => $list->get_author()->get_house()
            ));
            $query = $this->_db->prepare("
                DELETE FROM
                    list_auths
                WHERE 
                    list = :list
            ");
            $query->execute(array(
                ":list" => $list->get_id()
            ));
            $users = $list->get_users_auth();
            for ($i = 0; $i  < sizeof($users); $i++) {  
                $query = $this->_db->prepare("
                    INSERT INTO 
                        list_auths(
                            user, 
                            list
                        )
                    VALUES(
                        :user, 
                        :list
                    )
                    ON DUPLICATE KEY UPDATE user = user
                ");
                $query->execute(array(
                    ":user" => $users[$i]->get_id(),
                    ":list" => $list->get_id()
                ));
                if ($query->rowCount() !== 1) {
                    return false;
                }
            }
            return true;
        }
        
        //voir si l'utilisateur est authjorise a acceder a la liste l 
        public function is_auth(ShopList $list, User $current_user) {
            $query = $this->_db->prepare("
                SELECT * FROM list_auths WHERE user = :user AND list = :list
            ");
            $query->execute(array(
                ":user" => $current_user->get_id(),
                ":list" => $list->get_id()
            ));
            if ($query->rowCount() !== 1) {
                return false;
            }
            return true;
        }
        
        /*----------------------*/
        //Liste line 
        public function update_qte(ShopList $list) {
            if (sizeof($list->get_lines()) !== 1) {
                return false;
            }
            $query = $this->_db->prepare("
                UPDATE list_lines 
                SET 
                    qte = :qte
                WHERE
                    prdt = :prdt 
                    AND 
                    list = :list
            ");
            $query->execute(array(
                ":qte" => $list->get_lines()[0]->get_qte(),
                ":prdt" => $list->get_lines()[0]->get_id(),
                ":list" => $list->get_id()
            ));
            if ($query->rowCount() === 1) {
                return true;
            }
            return false;
        }

        public function striker_unstriker(ShopList $list) {
            if (sizeof($list->get_lines()) !== 1) {
                return false;
            }
            $query = $this->_db->prepare("
                UPDATE 
                    list_lines 
                SET 
                    is_striked = :is_striked
                WHERE
                    prdt = :prdt 
                    AND 
                    list = :list
            ");
            $query->execute(array(
                ":is_striked" => (int)$list->get_lines()[0]->get_is_striked(),
                ":prdt" => $list->get_lines()[0]->get_id(),
                ":list" => $list->get_id()
            ));
            if ($query->rowCount() === 1) {
                return true;
            }
            return false;
        }

        public function push(ShopList $list) {
            if (sizeof($list->get_lines()) !== 1) {
                return false;
            }
            //On insert la ligne
            $query = $this->_db->prepare("
                INSERT INTO
                    list_lines(
                        list, 
                        prdt,
                        house
                    )
                VALUE(
                    :list, 
                    :prdt, 
                    :house
                )
                ON DUPLICATE KEY UPDATE list =  list
            ");
            $query->execute(array(
                ":list" => $list->get_id(),
                ":prdt" => $list->get_lines()[0]->get_id(),
                ":house" => $list->get_author()->get_house()
            ));
            if ($query->rowCount() === 1) {
                return true;
            }
            return false;

        }
        public function remove_line(ShopList $list) {
            if (sizeof($list->get_lines()) !== 1) {
                return false;
            }
            $query = $this->_db->prepare("
                DELETE FROM 
                    list_lines 
                WHERE
                    prdt = :prdt 
                    AND
                    list = :list
                    AND 
                    house = :house
            ");
            $query->execute(array(
                ":prdt" => $list->get_lines()[0]->get_id(),
                ":list" => $list->get_id(),
                ":house" => $list->get_author()->get_house()
            ));
            if ($query->rowCount() === 1) {
                return true;
            }
            return false;
        }   

        /*----------------------*/

        //liste de listes
        public function has_toomany_list(User $user) {
            $query = $this->_db->prepare("
                SELECT count(id) FROM lists WHERE house = :house AND author = :author
            ");
            $query->execute(array(
                ":house" => $user->get_house(),
                ":author" => $user->get_id()
            ));
            if ($query->rowCount() === 0) {
                return false;            
            } 
            else if ($query->rowCount() > 1){
                return true;
            }
            else {
                $arr_rep = $query->fetchAll();
                if ($arr_rep[0][0] > $_ENV["MAX_LIST"]) {
                    return true;
                } 
                else {
                    return false;
                }
            }
            
        }
        /*Dans cette fonction on selectionne les listes archivees que l'utilisateur a le droit d'acceder
        *   
        *   
        */
       
        public function select_lists(User $user, int $alreardy_printed, bool $is_archived = null, int $h=0) {
            if ($h === $_ENV["MAX_TRY"]) {
                header('HTTP 503 Service Unavailable');
                exit; 
            }
            try {
                $this->_db->beginTransaction();
                $lists = array(); 
                if ($is_archived !== null) {
                    $query = $this->_db->prepare("
                        SELECT 
                            l.id,
                            l.name,
                            l.description, 
                            l.date_create,
                            l.is_archived,
                            u.pseudo, 
                            u.surname,
                            u.is_call_by_name
                        FROM
                            lists AS l
                        INNER JOIN 
                            users AS u
                        ON 
                            l.author = u.id
                        WHERE
                            is_archived = :is_archived
                            AND
                            (
                                (
                                    l.is_protected = false
                                    AND 
                                    l.house = :house
                                )
                                OR
                                (
                                    l.is_protected = true
                                    AND 
                                    l.house = :house 
                                    AND
                                    (
                                        author = :user
                                        OR 
                                        :user IN(SELECT user FROM list_auths WHERE list=l.id)
                                        OR 
                                        :user IN (SELECT id FROM users WHERE id=:user AND is_root = true)
                                    )
                                )
                            )
                        LIMIT 50 OFFSET $alreardy_printed       
                    ");
                    
                    $query->execute(array(
                        ":user" => $user->get_id(),
                        ":house" => $user->get_house(),
                        ":is_archived" => $is_archived
                    ));
                    $this->_db->commit();
                }
                else {
                    $query = $this->_db->prepare("
                        SELECT 
                            l.id,
                            l.name,
                            l.description, 
                            l.date_create,
                            l.is_archived,
                            u.pseudo, 
                            u.surname,
                            u.is_call_by_name
                        FROM
                            lists AS l
                        INNER JOIN 
                            users AS u
                        ON 
                            l.author = u.id
                        WHERE
                            (
                                (
                                    l.is_protected = false
                                    AND 
                                    l.house = :house
                                )
                                OR
                                (
                                    l.is_protected = true
                                    AND 
                                    l.house = :house 
                                    AND
                                    (
                                        l.author = :user
                                        OR 
                                        :user IN(SELECT user FROM list_auths WHERE list=l.id)
                                        OR 
                                        :user IN (SELECT id FROM users WHERE id=:user AND is_root = true)
                                    )
                                )
                            )
                        LIMIT 50 OFFSET $alreardy_printed       
                    ");
                    
                    $query->execute(array(
                        ":user" => $user->get_id(),
                        ":house" => $user->get_house(),
                        ":is_archived" => $is_archived
                    ));
                    $this->_db->commit();
                }
                
                $rep = $query->fetchAll(PDO::FETCH_ASSOC);
                for($i = 0; $i < sizeof($rep); $i++) {
                    $count_query = $this->_db->prepare("
                        SELECT 
                            COUNT(prdt) AS c
                        FROM list_lines
                        WHERE 
                            list = :list
                    "); 
                    $count_query->execute(array(
                        ":list" => $rep[$i]["id"]
                    ));
                    if($count_query->rowCount() !== 1) {
                        return $lists;    
                    }
                    $count = $count_query->fetch(PDO::FETCH_ASSOC);
                    array_push($lists, new ShopList(array(
                        "id" => $rep[$i]["id"],
                        "name" => $rep[$i]["name"],
                        "description" => $rep[$i]["description"],
                        "date_create" => $rep[$i]["date_create"],
                        "is_archived" => $rep[$i]["is_archived"],
                        "author" => new User(array(
                            "pseudo" => $rep[$i]["pseudo"],
                            "surname" =>  $rep[$i]["surname"],
                            "is_call_by_name" => $rep[$i]["is_call_by_name"]
                        )),
                        "linesCount" =>$count["c"]
                    )));
                }
                return $lists; 
            }
            catch(Exeption $e) {
                $this->select_lists($user, $alreardy_printed, $is_archived, $h+1); 
            }
        }

        public function select_with_line(Product $prdt) {
            $lists = array();
            $query = $this->_db->prepare("
                SELECT 
                    l.id,
                    l.name,
                    CASE is_protected 
                        WHEN false THEN false
                        WHEN 
                            true
                            AND NOT(
                                author = :user
                                OR 
                                :user IN(SELECT user FROM list_auths WHERE list=l.id)
                                OR 
                                :user IN (SELECT id FROM users WHERE :user AND is_root = true)
                            )
                        THEN true 
                        ELSE false
                    END as forbiden
                FROM
                    list_lines AS p
                INNER JOIN 
                    lists AS l 
                    ON 
                        l.id = p.list
                WHERE
                    l.house = :house
                    AND 
                    prdt = :prdt
            ");
            $query->execute(array(
                ":user" => $prdt->get_user()->get_id(), 
                ":house" => $prdt->get_user()->get_house(),
                ":prdt" => $prdt->get_id()
            ));
            $list_arr = $query->fetchAll(PDO::FETCH_ASSOC);
            for($i = 0; $i < sizeof($list_arr); $i++ ) {
                if($list_arr[$i]["forbiden"]){
                    return "forbiden";
                }
                else {
                    array_push($lists, new ShopList(array(
                        "id" => $list_arr[$i]["id"],
                        "name" => $list_arr[$i]["name"]
                    )));
                }
            }
            
            return $lists;
        }
    }