<?php
/**
 * Esta definido todas las funciones que se solicitan ejecutar desde la consola.
 * User: abejarano
 * Date: 25/03/17
 * Time: 12:59 AM
 */
namespace fw_Klipso\kernel\engine;

use fw_Klipso\applications\login\Login;

function startProject($name_project, $base_dir){
    /* Detects if a name for project specified */
    if(!isset($name_project)){
        exit('please specify name for your project' . PHP_EOL);
    }

    require 'kernel/engine/projects/CreateProject.php';
    new \fw_Klipso\kernel\engine\projects\CreateProject($name_project, $base_dir);

}
function startApp($name_app, $base_dir){
    /* Detects if a name for project specified */
    if(!isset($name_app)){
        exit('please specify name for your application' . PHP_EOL);
    }
    require 'kernel/engine/projects/CreateApplications.php';
    new \fw_Klipso\kernel\engine\projects\CreateApplications($name_app, $base_dir);
}
function syncDataBase($app = ""){
    /* synchronized the requested application */
    $array_apps = unserialize(APP_INSTALL);
    require 'kernel/engine/dataBase/Sync.php';

    foreach ($array_apps as $value){

        if(!empty($app)){
            $application = ".".$app;
            if(preg_match("/$application/", $value)){
                $path_file = str_replace('.', '/', $value);
                \fw_Klipso\kernel\engine\dataBase\Sync\SyncApplications($path_file, $app);
            }
        }else{ # synchronized all applications
            $path_file = str_replace('.', '/', $value);
            $app_name = explode('.', $value);
            \fw_Klipso\kernel\engine\dataBase\Sync\SyncApplications($path_file, $app_name[count($app_name) - 1]);
        }

    }
}

function createSuperUser(){
    global  $conexion_pdo;
    $isMail = false;
    while(!$isMail){
        echo "Email: ";
        $email = trim(fgets(STDIN));
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            $isMail = true;
        }
    }

    $isNull = true;
    while($isNull){
        echo "Username: ";
        $username = trim(fgets(STDIN));

        if(!empty(trim($username))){
            $isNull = false;
        }
    }

    $len = 0;
    while($len < 8){
        echo "Passsword: ";
        $pass = trim(fgets(STDIN));
        $len = strlen(trim($pass));
    }



      try{

        $sql = "insert into ".getPrefixModel(USER_MODEL) . '_'.strtolower(USER_MODEL). " 
        (user_login,user_email,user_pass) values ('".$username."', '".$email."', '".Login::getEncryptPass($pass)."')";

        $conexion_pdo->exec($sql);

        echo 'User created successfully' . PHP_EOL;
    }catch (\PDOException $e){
        die($e->getMessage());
    }
}

function createModel($new_model, $name_app, $ruta){

    $array_apps = unserialize(APP_INSTALL);
    $path_file_app = "";
    foreach ($array_apps as $value){
        $application = ".".$name_app;

        if(preg_match("/$application/", $value)){

            $path_file_app = str_replace('.', '/', $value);

        }
    }

    if(empty($path_file_app)){
        die('Sorry for not creating the new model because the application "'.$name_app.'" does not exist or is not present in the APP_INSTALL constant of the settings.php file' . PHP_EOL);
    }
    $file_model = __DIR__ . '/projects/modelProject/model.txt';
    $file_new_model = $ruta . $path_file_app . '/models/'.$new_model.'.php';

    $params = array(
        'app' => $name_app,
        'model' => $new_model,
    );
    /*se almacean las lineas leidas*/
    $line_read = array();

    /*archivo de destino, donde se escribira las lineas del archivo $file_new_model*/
    $new_model = fopen($file_new_model, 'w');

    /* se lee el archivo file_model */
    $reader = fopen($file_model, 'r');
    while(!feof($reader)) {
        $linea = fgets($reader);
        if(!in_array($linea, $line_read, true)){
            foreach ($params as $key => $value) {
                if(preg_match("/$key/", $linea))
                    $linea = str_replace('{%' . $key .'%}', $value, $linea);

            }
        }
        fwrite($new_model, $linea);
        $line_read = array_merge($line_read, array($linea));
    }
    fclose($reader);
}