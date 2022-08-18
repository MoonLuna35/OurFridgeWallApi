<?php

require "../../init.php";
include_once '../../user/is-loged.php';
include_once '../../controlers/guard/GList.php';
include_once "../../model/list/list/List.php";
include_once "../../model/list/list/ListDB.php";
include_once "../../model/user/userDB.php";
include_once "../../model/user/UserForAuth.php";
use Firebase\JWT\JWT;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // The request is using the POST method
    return http_response_code(200);

}
// Get the posted data.
$postdata = file_get_contents("php://input"); 

$listDb = new ListDB();
$users = array();
$users_array = array();
$shopList = null; 


function generate_token(ShopList $list, User $current_user) {
    $issuedAt   = new DateTimeImmutable();
        $expire     = $issuedAt->modify('+10 minutes')->getTimestamp();

        $privateKey = openssl_pkey_get_private(SECRET_CERTIF_PAGE);
        $payload = array(
            "user" => $current_user->get_id(),
            "house" => $current_user->get_house(),
            "id" => $list->get_id(),
            "iss" => "theFridgeDoor.fr",
            "aud" => "theFridgeDoor.fr",
            "iat" => $issuedAt->getTimestamp(),
            "nbf" => $issuedAt->getTimestamp(),
            "exp" => $expire
        );
        return JWT::encode($payload, $privateKey, 'RS256');
        
}

function validate_token(String $token, ShopList $list, User $current_user) {
    $now = new DateTimeImmutable();
    $decoded = JWT::decode($token, PUBLIC_CERTIF_PAGE, array('RS256'));
            if (
                $decoded->user !== $current_user->get_id() ||
                $decoded->house !== $current_user->get_house() ||
                $decoded->id !== $list->get_id() ||
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

if(!isset($postdata) || empty($postdata)) { 
    
    header('HTTP/1.1 400 Bad Request');
    exit;   
}
else {
    $request = json_decode($postdata);
    if (
        !isset($request->data->list->id) ||
        !isset($request->data->token) ||
        (int)$request->data->list->id <= 0 
        ) {
        header('HTTP/1.1 400 Bad Request');
        exit;   
    }
    else {
        $shopList = new ShopList(array( //Om instancie la liste
            "id" => $request->data->list->id,
        ));//On creer la liste
        guard($shopList, $current_user, true); 
        if($request->data->token === "") {
            $output["data"]["token"] = generate_token($shopList, $current_user);//On l'indique au client
            print_r(json_encode($output));
            return http_response_code(200);
        }
        else if (validate_token($request->data->token, $shopList, $current_user)){
            if($listDb->delete($shopList)) {
                $output["data"]["status"] = "ok";//On l'indique au client
                print_r(json_encode($output));
                return http_response_code(200);
            }
            else {
                $output["data"]["error"] = "unable to delete";//On l'indique au client
                print_r(json_encode($output));
                return http_response_code(200);
            }
        }
        else {
            $output["data"]["error"] = "invalid token";//On l'indique au client
            print_r(json_encode($output));
            return http_response_code(200);
        }
        
    }
}