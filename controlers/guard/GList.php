<?php 

function guard($shopList, $current_user, $root_only = false) {
    $listDb = new ListDB();
    $shopList = $listDb->select_author($shopList);
    if ($shopList === false) {
        header('HTTP/1.1 404 Not Found');
        exit;
    }
    $private = $listDb->is_private($shopList); 
    if (
        (
            $shopList->get_author()->get_house() === $current_user->get_house() &&
            $private !== false && //SI la liste est prive 
            !$listDb->is_auth($shopList, $current_user) && //MAIS aue l'utilisateur n'est pas authorise
            !$current_user->get_is_root() && //NI root
            $current_user->get_id() !== $shopList->get_author()->get_id()//NI l'auteur ALORS 
        ) 
        ||
            $shopList->get_author()->get_house() !== $current_user->get_house()  
        ||
        (
            $shopList->get_author()->get_house() === $current_user->get_house()
            &&
            $root_only
            &&
            !$current_user->get_is_root()
            &&
            $current_user->get_id() !== $shopList->get_author()->get_id()
        )
    ) { 
        header('HTTP/1.1 403 Forbidden');
        exit;
    } 
}