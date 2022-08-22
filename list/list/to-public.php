<?php 

/*
*   l'utilisateur doit etre root ou auteur de la liste l appartenant a sa maison
*   pour la rendre a nouveau publique.  
*   Il vas rendre publique puis supprimer les authorisation
*
*/ 


require "../../init.php";
include_once '../../user/is-loged.php';
include_once '../../controlers/guard/GList.php';
include_once "../../model/list/list/List.php";
include_once "../../model/list/list/ListDB.php";
include_once "../../model/user/userDB.php";
include_once "../../model/user/UserForAuth.php";
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // The request is using the POST method
    return http_response_code(200);

}

// Get the posted data.
$postdata = file_get_contents("php://input"); 

$listDb = new ListDB();
$userDb = new UserDB();
$users = array();
$users_array = array();
$shopList = null; 


if(!isset($postdata) || empty($postdata)) { 
    
    log400(__FILE__, __LINE__); 
}
else {
    $request = json_decode($postdata);
    if (
        !isset($request->data->list->id) ||
        (int)$request->data->list->id <= 0 
        ) {
        log400(__FILE__, __LINE__); 
    }
    else {
        $shopList = new ShopList(array( //Om instancie la liste
            "id" => $request->data->list->id
        ));//On creer la liste
        guard($shopList, $current_user);
        $shopList = $listDb->select_author($shopList); //On recupere l'auteur
        if(
            !$shopList || //SI on peut pas obtenir l'auteur
            $shopList->get_author()->get_house() !== $current_user->get_house() || //OU que la liste n'appartient pas a la maison de l'utilisateur courant
            $current_user->get_is_root() === false && $current_user->get_id() !==  $shopList->get_author()->get_id() ||//OU que l'utilisateur courant n'est ni root, ni l'auteur
            !$listDb->list_is_existing($shopList)  //OU que la liste n'existe pas
             
        ) { 
            log400(__FILE__, __LINE__); 
        }
        else {
            if($listDb->to_public($shopList)) {
                $output["data"]["status"] = "ok";//On l'indique au client
                print_r(json_encode($output));
                return http_response_code(200);//On renvoie au client
            } 
            else {
                $output["data"]["error"] = "unable to set the list to public";//On l'indique au client
                print_r(json_encode($output));
                return http_response_code(200);//On renvoie au client
            }
        }
    }
}