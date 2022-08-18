<?php 
    require "../../init.php";
    
    include_once "../../user/is-loged.php";

    include_once "../../model/list/type/TypeDB.php";
    include_once "../../model/list/type/Type.php";
    include_once "../../model/list/products/ProductDB.php";
    include_once "../../model/list/products/Product.php";
    include_once "../../model/list/type/Type.php";
    include_once "../../model/user/User.php";
    include_once '../../controlers/guard/rootGuard.php';

    $postdata = file_get_contents("php://input");

    $typeDb = new TypeDB();
    $productDb = new ProductDB();
    $type =  null;

    if(!isset($postdata) || empty($postdata)) { 
        header('HTTP/1.1 400 Bad Request');
        exit; 
    }
    $request = json_decode($postdata);
    // Validate.
    if(
        !isset($request->data->type->id) || //SI une variable dont on a besoin n'existe pas OU
        (int)$request->data->type->id <= 0 //que l'id est négatif OU
        
    ) {
        header('HTTP/1.1 400 Bad Request'); //On renvoie une erreur
        exit; 
    }
    else { //SINON (les données sont valides)
        $type = new Type(array(
            "id" => $request->data->type->id,
            "user" => $current_user
        ));
        $products = $productDb->select_by_type($type);//On récupère les potentiels articles du type qu'on veux supprimer
        if (!$products) {//SI le type n'est contenu dans aucun article ALORS
            if ($typeDb->delete_type($type)) {//SI on arrive à le supprimer dans la base de donnée ALORS
                $output["data"]["status"] = "ok";  
                print_r(json_encode($output));//On renvoie ok
                return http_response_code(200);
            }
            else { //SINON (On n'arrive pas à le supprimer de la base)
                $output["data"]["error"] = "unable to remove type";  
                print_r(json_encode($output));//On renvoie ok
                return http_response_code(200);
            }               
        }            
        else {//SINON
            //On prépare les produits
            for ($i = 0; $i < sizeof($products); $i++) {
                $products[$i] = $products[$i]->to_array();
            }
            $output["data"]["status"] = "type is in one or more product";  //On renvoie la liste de produit dans lequel est le type 
            $output["data"]["products"] = $products;
            print_r(json_encode($output));
            return http_response_code(200);
        }
    }  

  