<?php
require "../../init.php";
include_once "../../user/is-loged.php";
include_once "../../model/list/products/ProductDB.php";
include_once "../../model/list/products/Product.php";
include_once "../../model/list/type/TypeDB.php";
include_once "../../model/user/userDB.php";
include_once '../../controlers/guard/rootGuard.php';

// Get the posted data.
$postdata = file_get_contents("php://input");
$productDB =  new ProductDB(); 
$typeDB = new TypeDB();
$userDB = new UserDB(); 

if(isset($postdata) && !empty($postdata)) { 
  $request = json_decode($postdata);
 

  // Validate.
  if( 
      !isset($request->data->product->label) ||
      !isset($request->data->product->unity) ||
      !isset($request->data->product->type->id) ||
      trim($request->data->product->label) === '' || 
      trim($request->data->product->unity) === '' || 
      preg_match('/[^a-zA-Z_0-9-_äâàèéèëêïîöôùûü ]/',trim($request->data->product->label)) ||
      preg_match('/[^a-zA-Z_0-9-_äâàèéèëêïîöôùûü ]/',trim($request->data->product->unity)) ||
      (int)$request->data->product->type->id <= 0
      )  {
    return http_response_code(400);
  }

  else {

    $prdt =  new Product(array( //on crée un produit avec la langue
        "label" => $request->data->product->label,
        "type" => new Type(array(
            "id" => $request->data->product->type->id,
        )),
        "unity" => $request->data->product->unity, 
        "user" => $current_user
    ));

        
    //On voit si l'article existe déjà avec exactement les mêmes paramêtres
    if ($productDB->prdt_is_existing($prdt)) {
        $output["data"]["error"] = "article dupliced";
        print_r(json_encode($output));
        return http_response_code(200);
    }

    if(!$typeDB->type_is_existing($prdt->get_type(), $current_user)) {
        
        $output["data"]["error"] = "no type"; //On envoie une erreur
        print_r(json_encode($output));
        return http_response_code(200);
    }
    
    if (!$userDB->user_is_exist($prdt->get_user())) {
        header('HTTP/1.1 401 Unauthorized'); //On autorise pas la connexion
        exit;//On renvoie l'utilisateur vers la page de connexion
    }
    
    $prdt->set_id($productDB->insert_new_prdt($prdt));
    $output["data"]["product"] = $prdt->to_array();
    print_r(json_encode($output));
    
    
  }
    

}
