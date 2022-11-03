<?php

require_once "../../init.php";
require_once ROOT_PATH . "user/is-loged.php";
require_once ROOT_PATH . "model/timeTable/event/Event.php";
require_once ROOT_PATH . "model/timeTable/event/EventDb.php";
require_once ROOT_PATH . "controlers/timeTable/event/AddObject.php";
require_once ROOT_PATH . "controlers/timeTable/event/DeleteObject.php";

/*class ModifyTask {
    private ?array $_task_to_edit = array();
    private ?DateTime $_new_date_begin = null;
    private mixed $_repeater = null;
    private int $_racine_id;
    private ?Task $_taskTree;

    public function __construct($current_user) {
        $event_var; 
        $postdata = file_get_contents("php://input");

        if(!isset($postdata) || empty($postdata)) { 
        
            log400(__FILE__, __LINE__); 
        }
        
        else {
            
            $request = json_decode($postdata);
            if(isset($request->data->racine) && $request->data->racine > 0) {
                $this->_racine_id = $request->data->racine;
            }
            else {
                log400(__FILE__, __LINE__);
            }
            if(isset($request->data->modify)){ //Si on modifie les attributs ALORS
                foreach($request->data->modify as $info) {
                    if(isset($info->id)) {
                        array_push($this->_task_to_edit, new Task($info, $current_user, null, true));
                    }
                    else if(isset($info->repeat_patern)) {
                        $this->_repeater = EventBase::instance_repeter($info);
                        
                        //On modifie le repeteur sur la racine
                    }
                    else if(isset($info->date_begin)) {
                        $this->_new_date_begin = new DateTime($info->date_begin . " " . $info->time_begin); //On instancie la date de debut
                    }    
                    //On modifie les dates de debuts
                }
            }
            else {//SINON SI on modifie la structure de l'arbre de tache 
                $del_evt = new DeleteObject($current_user); 
                //$del_evt->delete();
                $add_evt = new AddEvent($current_user);//On suprime l'arbre
                //$add_evt->add();
            }
            
        }
        
    }

    public function update_attr($current_user) {
        $evtDb = new TaskDb();

        if($evtDb->update_leafs(
            $this->_task_to_edit,
            $this->_racine_id,
            $this->_new_date_begin,
            $this->_repeater,
            $current_user
        )){
            print_r("ok");
        }
        else {
            print_r("n_ok");
        }
    }
}

$modify_event = new ModifyTask($current_user);
//$modify_event->update_attr($current_user);
//$foo = $modify_event->update();*/

abstract class abFoo {
    protected ?string $fooAttr = "non instancie"; 

    protected static function fromInsert($foo): Foo {
        $foo->fooAttr = "instancie";
        static::ctrl();
        return $foo;
    }
    protected static function ctrl () {
        print_r("blabla");
    }
}

class Foo extends abFoo {
    protected ?string $barAttr = "non instancie"; 
    
    public static function fromInsert($json): static {
        $new = new static();
        $new->barAttr = "instancie";
        parent::fromInsert($new);
        return $new;
    }
}

$foo = Foo::fromInsert(1);
print_r( $foo); 

