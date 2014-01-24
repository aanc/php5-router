<?php
/*
 * Author: Adrien Anceau <adrien.anceau@hp.com>
 */

// Start session
session_start();

// Autoload classes
function myAutoload($class){
	if (is_file(realpath(dirname(__FILE__)."/classes/$class.class.php")))
		include_once(realpath(dirname(__FILE__)."/classes/$class.class.php"));
}

spl_autoload_register('myAutoload');

// Exceptions handling
function exception_handler($exception) {
  echo "<strong>".$exception->getMessage()."</strong><br/>\n";
}
set_exception_handler('exception_handler');

// Allow upload of big files
ini_set('upload_max_filesize','7M');


$router=new Router(array(
    'basePath' => $_SERVER['SCRIPT_NAME']
));
new Controller($router);

?>
