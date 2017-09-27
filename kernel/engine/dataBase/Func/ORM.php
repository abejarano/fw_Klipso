<?php
/**
 * Construye los operados y clausulas usadas en los query
 * User: abejarano
 * Date: 21/03/17
 * Time: 10:19 PM
 */

namespace fw_Klipso\kernel\engine\dataBase\Func;


use fw_Klipso\kernel\classes\abstracts\aModels;

class ORM
{
    /**
     * Crea el operador OR
     * @param $params1
     * @param $params2
     * @return string
     * @throws \Exception
     */
    static function _OR($params1, $params2){
        /* del parametro 1 separa el campo del valor */
        $params_1 = explode('=', $params1);
        $campo1 = trim($params_1[0]);
        $var1 = trim($params_1[1]);

        /* del parametro 2 separa el campo del valor */
        $params_2 = explode('=', $params2);
        $campo2 = trim($params_2[0]);
        $var2 = trim($params_2[1]);

        if($campo1 != $campo2)
            throw new \Exception('Unexpected error creating OR clause. The fields involved should be the same.');

        /* busca si existe el campo en el modelo en cuestion */
        if(aModels::findFieldModel($campo1) == 'STRING'){
            return "(". $campo1 . "= '" .(strip_tags($var1)). "' {or} ". $campo2 . "= '" .(strip_tags($var2)) ."')";
        }else
            return '('. $params1 . ' {or} '. $params2 .')';
    }

    /**
     * Crea el operador IN
     * @param $field Campo del modelo
     * @param array $values Valores que tendra dicho campo
     * @return string El operador ya construido
     */
    static function _IN($field, Array $values){
        $var = "";
        /* busca si existe el campo en el modelo en cuestion */
        if(aModels::findFieldModel($field) == 'STRING'){
            foreach ($values as $val){
                # le coloca las comillas simples al valor
                $var .= "'".(strip_tags($val)) ."', ";
            }
            $var = trim($var,', ');
        }else{
            $var = implode(", ", $values);
        }
        return $field . " {in} ($var)  ";
    }
    /**
     * Crea el operador NOT IN
     * @param $field Campo del modelo
     * @param array $values Valores que tendra dicho campo
     * @return string El operador ya construido
     */
    static function _NOT_IN($field, Array $values){
        $var = "";
        /* busca si existe el campo en el modelo en cuestion */
        if(aModels::findFieldModel($field) == 'STRING'){
            foreach ($values as $val){
                # le coloca las comillas simples al valor
                $var .= "'".(strip_tags($val)) ."', ";
            }
            $var = trim($var,', ');
        }else{
            $var = implode(", ", $values);
        }
        return $field . " {not in} ($var)  ";
    }

}