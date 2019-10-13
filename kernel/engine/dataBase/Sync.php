<?php
namespace fw_Klipso\kernel\engine\dataBase\Sync;

use fw_Klipso\kernel\engine\dataBase\Migrations;



$path_folder_models = '';
$namespaces = '';

function getInstancModel($model){
    global $namespaces;
    $pos = strpos($model, 'extends');
    $var = substr($model,0,$pos);
    $var = trim(str_replace('class ','',$var));
    return $namespaces . $var;
}

function createTable($app){
    global $path_folder_models;
    if(!file_exists($path_folder_models))
        die('the models folder of the application no exits');
    $dh = opendir($path_folder_models);
    
    /* ejecuta el CREATE TABLE */
    while(($file = readdir($dh)) !== false){
        if(is_file($path_folder_models . '/' . $file)){
            $reader = fopen($path_folder_models . '/' . $file, 'r');

            while(!feof($reader)) {
                $linea = fgets($reader);
                if(preg_match("/class /", $linea)){

                    $class_model = getInstancModel($linea);
                    $obj_model = new $class_model;

                    Migrations::_create($obj_model, $app);

                }

            }
            fclose($reader);
        }
        
    }
    createAlter($app);
}

/* ejecuta el ALTER TABLE para los foren*/
function createAlter($app){
    global $path_folder_models;
    
    $dh2 = opendir($path_folder_models);
    while(($file2 = readdir($dh2)) !== false){
        if(is_file($path_folder_models . '/' . $file2)){
            $reader = fopen($path_folder_models . '/' . $file2, 'r');
            while(!feof($reader)) {
                $linea = fgets($reader);

                if(preg_match("/class /", $linea)){
                    $class_model = getInstancModel($linea);
                    $obj_model = new $class_model;
                    Migrations::_foreignKey($obj_model, $app);
                    
                }

            }
            fclose($reader);
        }
        
    }
}

function SyncApplications($path, $app){
    global $path_folder_models, $namespaces;

    $namespaces = str_replace('/','\\', $path) . '\\' . 'models\\';
    $path_folder_models = BASE_DIR . $path.'/models';
    createTable($app);
}