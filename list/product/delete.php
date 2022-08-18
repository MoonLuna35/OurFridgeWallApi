<?php
/**
 * 08/10 : Zoé
 * 
 */
    require "../../init.php";
    include_once "../../user/is-loged.php";
    include_once "../../model/list/products/ProductDB.php";
    include_once "../../model/list/products/Product.php";
    include_once "../../model/list/list/ListDB.php";
    include_once "../../model/list/list/List.php";
    include_once "../../model/list/type/Type.php";
    include_once "../../model/list/cupboard/CupboardDB.php";
    include_once '../../controlers/guard/rootGuard.php';

    use Firebase\JWT\JWT;
    // Get the posted data.
    $postdata = file_get_contents("php://input");

    $productDB =  new ProductDB(); 
    $cupboardDB = new CupboardDB();
    $listDB = new ListDB();
    $is_in_list = false;
    $is_in_cupboard = false;

    function generate_token(Product|CupboardLine $prdt) {
        $issuedAt   = new DateTimeImmutable();
            $expire     = $issuedAt->modify('+10 minutes')->getTimestamp();

            $privateKey = openssl_pkey_get_private(SECRET_CERTIF_PAGE);
            $payload = array(
                "user" => $prdt->get_user()->get_id(),
                "house" => $prdt->get_user()->get_house(),
                "id" => $prdt->get_id(),
                "iss" => "theFridgeDoor.fr",
                "aud" => "theFridgeDoor.fr",
                "iat" => $issuedAt->getTimestamp(),
                "nbf" => $issuedAt->getTimestamp(),
                "exp" => $expire
            );
            return JWT::encode($payload, $privateKey, 'RS256');
            
    }

    function validate_token(String $token, Product $prdt) {
        $now = new DateTimeImmutable();
        $decoded = JWT::decode($token, PUBLIC_CERTIF_PAGE, array('RS256'));
                if (
                    $decoded->user !== $prdt->get_user()->get_id() ||
                    $decoded->house !== $prdt->get_user()->get_house() ||
                    $decoded->id !== $prdt->get_id() ||
                    $decoded->iss !== "theFridgeDoor.fr" ||
                    $decoded->aud !== "theFridgeDoor.fr" ||
                    $decoded->nbf > $now->getTimestamp() ||
                    $decoded->exp < $now->getTimestamp())
                {
                    return false;
                }
                else { 
                    return true;
                }
    }

    if(isset($postdata) && !empty($postdata)) { 
        $request = json_decode($postdata);
        // Validate.
        if(
            !isset($request->data->product->id) ||
            !isset($request->data->validate_token) ||
            (int)$request->data->product->id < 1)
        {
            header('HTTP/1.1 400 Bad Request');
            exit; 
        }
        else {
            $token =  $request->data->validate_token;
            
            $prdt_cupLine =  new CupboardLine(array( //on crée un produit
                "id" => $request->data->product->id,
                "user" => $current_user
            ));
            $prdt_listLine =  new ListLine(array( //on crée un produit
                "id" => $request->data->product->id,
                "user" => $current_user
            ));
            if($cupboardDB->prdt_is_in_cupboard($prdt_cupLine)) { //SI le produit est à controler dans les palcards ALORS
                if (trim($token) === "") {
                    $output["data"]["status"] = array("product in cupboard"); //on l'indique
                }
                
                $is_in_cupboard = true;
            } 
            $lists = $listDB->select_with_line($prdt_cupLine);
            if($lists !== false) {//SI le produit est contenu dans une liste active 
                if($lists === "forbiden") { //SI l'utlisateur n'a pas les droit sur la liste
                    $output["data"]["error"] = "product is in forbiden list";//On renvoie une erreur au client
                    print_r(json_encode($output));
                    return http_response_code(200);
                }
                else { //sinon
                    if (trim($token) === "" && sizeof($lists) > 0) {
                        $output["data"]["lists"] = array(); //On les insert dans l'output.
                        for ($i = 0; $i < sizeof($lists); $i++) {
                            array_push($output["data"]["lists"], $lists[$i]->to_array());
                        }
                        $is_in_list = true;
                    }
                    
                }
            }
            
            if (
                trim($token) === "" //SI le token est vide
                && //MAIS
                    (
                        $is_in_list //le produit est dans une liste 
                        ||//OU 
                        $is_in_cupboard//dans le placard
                    )
            ) { //ALORS
                $output["data"]["validate_token"] = generate_token($prdt_cupLine);//On lui renvoie un token qu'il renvera, indiquant qu'il est passé par la confirmation
                print_r(json_encode($output));
                return http_response_code(200);
            }
            else if ((trim($token) !== "" && validate_token($token, $prdt_cupLine))) {  //SINON SI le token est valide (on a déjà confirmer la suppréssion) ALORS
                for($i = 0; $i < sizeof($lists); $i++) {//POUR TOUTE liste ou le produit est present FAIRE
                    $lists[$i]->set_lines(array($prdt_listLine));
                    $lists[$i]->set_author($current_user);
                    if(!$listDB->remove_line($lists[$i])) { //On les supprimes le produit de la liste i
                        header('HTTP/1.1 500 Internal Server Error');
                        exit; 
                    } 
                }
                if ($is_in_cupboard && !$cupboardDB->pop_cupboard($prdt_cupLine)) {//SI le produit est present dans le placard MAIS qu'on arrive pas a le supprimer ALORS 
                    header('HTTP/1.1 500 Internal Server Error');
                    exit; 
                }
                if ($productDB->delete($prdt_listLine)) {
                    $output["data"]["status"] = "OK";//On renvoie ok
                    print_r(json_encode($output));
                    return http_response_code(200);
                }
                else {
                    $output["data"]["error"] = "unable to delete";//On renvoie ok
                    print_r(json_encode($output));
                    return http_response_code(200);
                }
            }       
            else { //SINON 
                if ($productDB->delete($prdt_listLine)) {
                    $output["data"]["status"] = "OK";//On renvoie ok
                    print_r(json_encode($output));
                    return http_response_code(200);
                }
                else {
                    $output["data"]["error"] = "unable to delete";//On renvoie ok
                    print_r(json_encode($output));
                    return http_response_code(200);
                }
            }  
        }
    }
    else {
        header('HTTP/1.1 400 Bad Request');
        exit; 
    }