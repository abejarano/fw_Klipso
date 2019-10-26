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
    private static $_html = '';

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

    public static function setField(Array $array_fields) {
        $html = '';

        foreach ($array_fields as $field) {
            $html .= '<div class "row">';
            if (is_array($field)) {

                $cols = 12 / count($array_fields);
                if ($cols < 1) {
                    $cols = 1;
                }
                foreach ($field as $_f) {
                    $html .= '<div class = "col col-'.$cols.'">';
                    $html .= Forms::setHtmlForm($_f);
                    $html .= '</div>';
                }
            }

            $html .= '</div>';
        }
        return $html;
    }

    private static function setHtmlForm($nam_field) {
        $data_field = Forms::getDataField($nam_field);
        $type = $data_field[0];
        $label = $data_field[1];
        $required = $data_field[2];
        if (empty($type)) {
            pr('Field not found ' . $nam_field);
        }
        if(preg_match('/BIGINT/', $type) ||
            preg_match('/INTEGER/', $type) ||
            preg_match('/NUMERIC/', $type) ||
            preg_match('/REAL/', $type) ||
            preg_match('/REAL/', $type) ||
            preg_match('/serial/', $type) ||
            preg_match('/AUTO INCREMENT/', $type) ||
            preg_match('/DECIMAL/', $type)
        ){

            return '<div class="form-group">
                        <label for="id_'.$nam_field.'">'.$label.'</label>
                        <input type = "number" name = "'.$nam_field.'" class = "form-control" 
                        id="id_'.$nam_field.' ">
                   </div>';

        }


    }

    private static function getDataField($field) {
        foreach (Forms::$_fields_model as $model_field => $type) {
            if ($field == $model_field) {

                return $type;
            }
        }

        return '';
    }
}