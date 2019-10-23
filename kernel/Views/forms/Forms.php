<?php

/**
 * User: abejarano
 * Date: 17/10/19
 * Time: 6:30 AM
 */


namespace fw_Klipso\kernel\Views\forms;


class Forms
{
    private static $_path_app = '';
    private static $_model = '';
    private static  $_form = [];
    private static $_fields_model = [];

    public static function setPathApp($path) {
        Forms::$_path_app = $path;
        
    }

    public static function setModel($model) {
        Forms::$_model = ucfirst($model);
        Forms::__loadFieldMoel();
    }

    private static function __loadFieldMoel() {
        # instanciar la clave model
        $path_model = Forms::$_path_app . '\\models\\' .Forms::$_model;

        if (!class_exists($path_model)) {
            pr('El modelo ' . Forms::$_model . ' NO existe en su aplicación');
        }
        
        $objModel = new $path_model;

        Forms::$_fields_model = $objModel->__fields__();

    }
}