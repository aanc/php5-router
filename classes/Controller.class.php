<?php

class Controller
{

    /*
     * Constructor
     */
	public function __construct($router)
	{
        
        // Session initialization
        $user = null;   // User class deleted for simplification of the demo
        $debug = true; //

        // Routes initialization
        $router->addRoute(array('pattern' => '', 'name' => 'home'));
        $router->addRoute(array('pattern' => '/home', 'name' => 'home'));

        $router->addRoute(array('pattern' => '/debug/md5/{string}', 'name' => 'debug-md5'));
        
        // Route conditions:
        // If condition is respected, route is triggered, otherwise fallback route is triggered
        // Examples:

        // All routes under /debug are activated if $debug=true
        $router->addCondition(array('route' => '/debug', 'condition' => ($debug == true), 'fallbackRoute' => 'forbidden'));

        // All routes under /admin are accessible if user->getAdmin() returns >0, and a special section is accessible if >1
        $router->addCondition(array('route' => '/admin', 'condition' => ($user != null && $user->getAdmin() > 0), 'fallbackRoute' => 'forbidden'));
        $router->addCondition(array('route' => '/admin/specialSection', 'condition' => ($user != null && $user->getAdmin() > 1), 'fallbackRoute' => 'forbidden'));

        $rendering="";
        switch ($router->run()) {
            case 'home':
                $rendering="Hello ?";
                break;

            case 'debug-md5':
                echo "<pre>";
                $rendering=md5($router->getArgument('string'));
                break;


            // ---- ERRORS -------------------------------------------
            case 'forbidden':
                $rendering="403: Forbidden. (too bad ...)";
                break;

            default:
                $rendering="404: Not found. (your custom 404 page here !)";
                break;
        }

        echo $rendering;
    }
}