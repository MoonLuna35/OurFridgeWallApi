<?php
require "../../init.php";
include_once "../../user/is-loged.php";
include_once "../../model/list/products/ProductDB.php";
include_once "../../model/list/products/Product.php";
include_once "../../model/list/type/Type.php";
include_once '../../controlers/guard/rootGuard.php';


// Get the posted data.
$postdata = file_get_contents("php://input");

$productDB =  new ProductDB(); 
$prdt;

if(isset($postdata) && !empty($postdata))
{ 
  // Extract the data.
  $request = json_decode($postdata);
	

  // Validate.
  if(
    !isset($request->data->product->label) ||
    trim($request->data->product->label) === '' ||
    preg_match('/[^a-zA-Z_0-9-_äâàèéèëêïîöôùûü ]/',trim($request->data->product->label))
    ) {

    return http_response_code(400);
  }

    $prdt =  new Product(array( //on crée un produit avec la langue
      "label" => $request->data->product->label,
      "user" => new User(array(
          "house" => $current_user->get_house()
      ))
    ));

  

  $prdt_for_client = $productDB->select_by_label($prdt);
  if ($prdt_for_client === "no product") {
    $output["data"]["status"] = "no product";
  }
  else if ($prdt_for_client === "two or more product") { //SI il y a plusieurs résultat ALORS 
    header('HTTP/1.1 500 Internal Server Error');//La base de donnée n'est pas censé autorisé cette situation, une erreur 500 est envoyé
    exit; 
  }
  else {
    $output["data"]["product"] = $prdt_for_client->to_array();
  }
  print_r(json_encode($output));
  return http_response_code(200);
  

}