<?php
    require "../../init.php";
    include_once '../../user/is-loged.php';
    include_once "../../model/list/list/List.php";
    include_once "../../model/list/list/ListDB.php";
    include_once "../../model/list/list/ListLine.php";
    include_once "../../model/user/User.php";
    include_once "../../model/user/userDB.php";
    include_once '../../controlers/guard/rootGuard.php';

    $postdata = file_get_contents("php://input");
    
    $list_db = new ListDB();
    $user_db = new UserDb();
    $list = null;

    if(!isset($postdata) || empty($postdata)) { 
        header('HTTP/1.1 400 Bad Request');
        exit;   
    }
    else {
        $request = json_decode($postdata);
        if (
            !isset($request->data->list->name) ||
            !isset($request->data->list->users_auth) ||
            !isset($request->data->list->desc) ||
            !isset($request->data->list->is_private) ||
            trim($request->data->list->name) === "" || 
            trim($request->data->list->name) === "first_list" ||
            preg_match('/[^a-zA-Z_0-9-_äâàèéèëêïîöôùûü\/ ]/',trim($request->data->list->name)) ||
            !is_bool($request->data->list->is_private) ||
            !is_array($request->data->list->users_auth)
        ) {
            header('HTTP/1.1 400 Bad Request');
            exit;   
        }
        else {
            $secure_desc = htmlentities($request->data->list->desc);
            $users_auth = array();
            $current_user_extended = $user_db->select_by_id($current_user->get_house());
            for ($i = 0; $i < sizeof($request->data->list->users_auth); $i++) {//POUR TOUT id FAIRE 
                array_push($users_auth, new User(array(
                    "id" => $request->data->list->users_auth[$i]->id,
                    "house" => $current_user->get_house()
                )));//On creer les user auth
                if(!$user_db->is_membership_of_house($users_auth[sizeof($users_auth)-1])) { //SI l'utilisateur qu'on veux authoriser ne fait pas parti de la maison de l'utilisateur courant ALORS
                    header('HTTP/1.1 400 Bad Request'); //On renvoie une erreur 400 
                    exit;
                }
            }
            $list = new ShopList (array(
                "name" => $request->data->list->name,
                "description" => $secure_desc,
                "author" => $current_user_extended,
                "is_protected" => $request->data->list->is_private,
                "users_auth" => $users_auth
            ));
            $has_too_many = $list_db->has_toomany_list($list->get_author());
            if (!$has_too_many) {
                $list_id = $list_db->new_list($list);
                if ($list_id) {
                    $list->set_id($list_id);
                    if(
                        $list->get_is_protected() //SI la liste est privee
                        &&
                        sizeof($list->get_users_auth()) > 0 // ET qu'il y a des utilisateur a ajouter ALORS 
                    ) {
                        
                        //On essaye d'ajouter les auth
                        if ($list_db->modify_auth($list)) { //SI on y arrive ALORS
                            $output["data"]["status"] = "ok";
                            $output["data"]["list"] = $list->to_array();
                            print_r(json_encode($output));
                            return http_response_code(200);
                        }
                        else { //SINON
                            //On supprime la liste
                            if ($list_db->delete($list)) { //SI on y arrive ALORS
                                $output["data"]["error"] = "unable to add authorization"; //On renvoie une erreur 
                                
                                print_r(json_encode($output));
                                return http_response_code(200);
                            }
                            else { //SINON
                                return http_response_code(500);//On renvoie une erreur 500
                                //On log l'erreur
                            }       
                        }
                    }
                    else { //SINON (la liste est public ou elle est privee mais il n'y a pas d'utilisateur a ajouter)
                        $output["data"]["status"] = "ok";
                        $output["data"]["list"] = $list->to_array();
                        print_r(json_encode($output));
                        return http_response_code(200);
                    }
                    
                        
                }
                else {
                    $output["data"]["error"] = "unable to create a new list";
                    print_r(json_encode($output));
                    return http_response_code(200);
                }

            }
            else{
                $output["data"]["error"] = "toomany lists for user";
                print_r(json_encode($output));
                return http_response_code(200);
            }
            
        }
    }

    header('HTTP/1.1 500 Internal Server Error');
    exit; 