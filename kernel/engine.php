<?php
namespace fw_Klipso\kernel;

require 'engine/functions.php';

$message = (isset($_GET['message']) ? strip_tags(addslashes($_GET['message']))  : '');

define('MESSAGE_TEMPLATE', $message);

use fw_Klipso\kernel\engine\dataBase\ConexionPDO;
use fw_Klipso\kernel\engine\middleware\Request;




spl_autoload_register(function ($nombre_clase) {
	$file = BASE_DIR . str_replace('\\','/',$nombre_clase)  . '.php';
    if(file_exists($file)){
        include $file;
    }

});
/* include vendor composer */

require BASE_DIR . '/fw_Klipso/vendor/autoload.php';



if(defined('DATABASE'))
    $conexion_pdo = new ConexionPDO();

/* Set cookies for the session */
$session = new \fw_Klipso\kernel\engine\middleware\Session();

if(!isset($_SESSION['csrftoken'])){
    $session->setSession('csrftoken', SECRET_KEY);

}


$session->checkSessionActive();

//$session->destroy();


