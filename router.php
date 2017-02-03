<?php
function call($controller, $action){
    $actionMethod = "action".ucfirst($action);

    switch($controller){
        case "result":
        case "request":
        default: //set default to error
            $controller = new \controller\RequestController();
            break;
    }

    //check if method exists first
    if(method_exists($controller, $actionMethod))
    {
        $controller->{$actionMethod}();
    }
    else{
        $controller = new \controller\Controller();
        $controller->actionError("Method not found");
    }
}
//the $controller variable is passed from index.php
call($controller, $action);