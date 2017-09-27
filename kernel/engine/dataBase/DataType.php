<?php
/**
 * User: abejarano
 * Date: 21/03/17
 * Time: 03:02 PM
 */

namespace fw_Klipso\kernel\engine\dataBase;


class DataType
{
    static function FieldString($max_length, $notNull = false, $default = ''){
        $sgdb = '';
        $campo = '';
        $type = '';
        if(defined('DATABASE')){
            $database = unserialize(DATABASE);
            $sgdb = $database['ENGINE'];
        }
        switch ($sgdb){
            case 'pgsql':
                $campo .= " character varying ($max_length) ";
                break;
            case 'mysql':
                $campo .= " varchar ($max_length)";
                break;
            case 'sqlite':
                $campo .= ' text';
                break;
            default:
                die('Error, database engine is not defined');
                break;
        }
        if ($notNull)
            $campo .= " NOT NULL ";

        if(!empty($default))
            $campo .= " DEFAULT '$default' ";


        return $campo;
    }
    static function FieldText($notNull = false, $default = ''){
        $campo =  ' text';
        if ($notNull)
            $campo .= " NOT NULL ";

        if(!empty($default))
            $campo .= " DEFAULT '$default' ";

        return $campo;
    }
    static function FieldInteger($notNull = false, $default = ''){
        $sgdb = '';
        if(defined('DATABASE')){
            $database = unserialize(DATABASE);
            $sgdb = $database['ENGINE'];
        }

        $campo = '';
        switch ($sgdb){
            case 'pgsql':
                $campo .= ' BIGINT';
                break;
            case 'mysql':
                $campo .= ' BIGINT';
                break;
            case 'sqlite':
                $campo .= ' INTEGER';
                break;
            default:
                die('Error, database engine is not defined');
                break;
        }
        if ($notNull)
            $campo .= " NOT NULL ";

        if( ($default >=0 || $default <=0) && !empty($default))
            $campo .= " DEFAULT $default ";


        return $campo;
    }

    static function FieldFloat($max_digits, $decimal_places, $notNull = false, $default = ''){
        $sgdb = '';
        if(defined('DATABASE')){
            $database = unserialize(DATABASE);
            $sgdb = $database['ENGINE'];
        }
        $campo = '';
        switch ($sgdb){
            case 'pgsql':
                $campo .= " NUMERIC($max_digits, $decimal_places)";
                break;
            case 'mysql':
                $campo .= " DECIMAL($max_digits, $decimal_places)";
                break;
            case 'sqlite':
                $campo .= " REAL";
                break;
            default:
                die('Error, database engine is not defined');
                break;
        }
        if ($notNull)
            $campo .= " NOT NULL ";

        if(!empty($default))
            $campo .= " DEFAULT $default ";

        return $campo;
    }
    static function FieldBoolean($notNull = false, $default = ''){
        $sgdb = '';
        if(defined('DATABASE')){
            $database = unserialize(DATABASE);
            $sgdb = $database['ENGINE'];
        }
        $campo = '';
        switch ($sgdb){
            case 'pgsql':
                $campo = ' boolean';
                break;
            case 'mysql':
                $campo = ' bool';
                break;
            case 'sqlite':
                $campo = ' INTEGER';
                break;
            default:
                die('Error, database engine is not defined');
                break;
        }
        if ($notNull)
            $campo .= " NOT NULL ";

        if(!empty($default)){
            if($default)
                $campo .= " DEFAULT true ";
            else
                $campo .= " DEFAULT false ";
        }
            

        return $campo;
    }
    static function FieldChar($notNull = false, $default = ''){
        $sgdb = '';
        if(defined('DATABASE')){
            $database = unserialize(DATABASE);
            $sgdb = $database['ENGINE'];
        }
        $campo = '';
        switch ($sgdb){
            case 'pgsql':
                $campo .= ' "char"';
                break;
            case 'mysql':
                $campo .=  "char";
                break;
            case 'sqlite':
                $campo .= ' text';
                break;
            default:
                die('Error, database engine is not defined');
                break;
        }
        if ($notNull)
            $campo .= " NOT NULL ";

        if(!empty($default))
            $campo .= " DEFAULT '$default' ";

        return $campo;
    }
    static function FieldDate($notNull = false, $default = ''){
        $campo = ' date ';

        if ($notNull)
            $campo .= " NOT NULL ";

        if(!empty($default))
            $campo .= " DEFAULT $default ";

        return $campo;
    }
    static function FieldDateTime($notNull = false, $default = ''){
        $sgdb = '';
        if(defined('DATABASE')){
            $database = unserialize(DATABASE);
            $sgdb = $database['ENGINE'];
        }
        $campo = '';
        switch ($sgdb){
            case 'pgsql':
                $campo .= ' timestamp without time zone';
                break;
            case 'mysql':
                $campo .= ' datetime';
                break;
            case 'sqlite':
                $campo .= ' datetime';
                break;
            default:
                die('Error, database engine is not defined');
                break;
        }
        if ($notNull)
            $campo .= " NOT NULL ";

        if(!empty($default))
            $campo .= " DEFAULT $default ";


        return $campo;
    }
    static function FieldAutoField(){
        $sgdb = '';
        $campo = '';

        if(defined('DATABASE')){
            $database = unserialize(DATABASE);
            $sgdb = $database['ENGINE'];
        }
        switch ($sgdb){
            case 'pgsql':
                $campo .= ' serial';
                break;
            case 'mysql':
                $campo .= ' BIGINT NOT NULL AUTO_INCREMENT ';
                break;
            case 'sqlite':
                $campo .= ' AUTO INCREMENT';
                break;
            default:
                die('Error, database engine is not defined');
                break;
        }


        return $campo;
    }
}