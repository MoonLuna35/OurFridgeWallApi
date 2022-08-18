<?php
    require "../../init.php";
    include_once '../../user/is-loged.php';
    include_once "../../model/list/list/List.php";
    include_once "../../model/list/list/ListDB.php";
    include_once "../../model/list/products/ProductDB.php";
    include_once "../../model/list/products/Product.php";
    include_once "../../model/user/User.php";
    include_once '../../controlers/guard/GList.php';


    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        // The request is using the POST method
        return http_response_code(200);
    
    }

    $postdata = file_get_contents("php://input");
    
    $list_db = new ListDB();
    $product_db = new ProductDB();
    $list = null;

    if(!isset($postdata) || empty($postdata)) { 
        header('HTTP/1.1 400 Bad Request');
        exit;   
    }
    else {
        $request = json_decode($postdata);
        if (
            !isset($request->data->list->product->label) ||
            !isset($request->data->list->id) ||
            (int)$request->data->list->id  <= 0 ||
            trim($request->data->list->product->label) === "" || 
            preg_match('/[^a-zA-Z_0-9-_äâàèéèëêïîöôùûü ]/',trim($request->data->list->product->label))
        ) {
            header('HTTP/1.1 400 Bad Request');
            exit;   
        }
        else {
            $list = new ShopList (array(
                "id" => $request->data->list->id,
                "lines" => array(
                    new ListLine(array(
                        "label" => $request->data->list->product->label,
                    ))),
                "author" => new User(array(
                    "house" => $current_user->get_house()
                ))
            ));
            guard($list, $current_user, true);
            $line = new Product(array(
                "label" => $list->get_lines()[0]->get_label(),
                "user" => new User(array(
                    "house" => $list->get_author()->get_house()
                ))
            )); //on change la classe de la ligne 

            $line_in_prdt = $product_db->select_by_label($line);
            if(!is_string($line_in_prdt)) {//SI le produit existe ALORS
                $list->set_lines(array(new ListLine(array(
                    "id" => $line_in_prdt->get_id(),
                    "label" => $line_in_prdt->get_label(), 
                    "type" => new Type(array(
                        "name" => $line_in_prdt->get_type()->get_name(),
                        "logo_patern" => $line_in_prdt->get_type()->get_logo_patern(),
                        "logo_color" => $line_in_prdt->get_type()->get_logo_color (),
                    )),
                    "unity" => $line_in_prdt->get_unity(),
                )))); 
                $added_line = $list_db->push($list);
                if($added_line !== false) { //SI on arrive a l'ajouter ALORS
                    $output["data"]["line"] = $list->get_lines()[0]->to_array(); //On renvoie la ligne au client
                    print_r(json_encode($output));
                    return http_response_code(200);
                }
                else { //SINON
                    $output["data"]["error"] = "unable to add the line"; //On renvoie une erreur
                    print_r(json_encode($output));
                    return http_response_code(200);
                }
            }
            else { //SINON (le produit n'existe pas)
                $output["data"]["error"] = "product not exist"; //On renvoie une erreur
                print_r(json_encode($output));
                return http_response_code(200);
            }
            
                //On renvoie une erreur*/
        }
    }