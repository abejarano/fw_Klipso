<?php

/**
 * Es la iniciativa de crear un ORM funcional facil de usar, que cumpla con el principio de portabilidad
 * User: abejarano
 * Date: 17/10/19
 * Time: 6:30 AM
 */
namespace fw_Klipso\kernel\engine\ORM;

use fw_Klipso\kernel\engine\dataBase\DataBase;
use fw_Klipso\kernel\engine\ORM\abstracts\aModels;
use mysql_xdevapi\Exception;

class Dark extends DataBase {
    protected $model;

    public function limit($count, $since){
        $this->_limit = " LIMIT ".$count." offset ".$since." ";
        return $this;
    }

    /**
     * @param $model Nombre del modelo foraneo
     * @param string $type_join Tipo de JOIN que se desa realizar (INNER, LEFT, RIGHT)
     * @throws \Exception
     */
    public function with($model, $type_join = "INNER") {

        if (!is_array($model)) {
            $this->prepareInnerJoin($model, $type_join);
            return $this;
        }

        foreach ($model as $val) {
            $this->prepareInnerJoin($val, $type_join);
        }

        return $this;
    }

    /**
     * Crea una consulta a la db
     * @param string $field campos que retornara la consulta.
     */
    public function find($field = ""){

        # echo get_class($this);
        # die();
        $SELECT = "SELECT ";

        if(!empty($field) && !is_array($field))
            $this->checkFieldExistsModel($field);

        elseif (is_array($field)) 
            $field = $this->getSelectiveFields( (Array) $field);

        else
            $field = $this->__getFieldsModel();

        $SELECT .= $field . ' FROM ' .$this->__getNameModel();

        $this->_SQL = $SELECT;

        return $this;

    }

    /**
     * Extrae los campos de la estructura del model, y los coloca en el array fields siendo el nombre del campo
     * la llave y el valor el tipo de dato
    */
    public function __getStructModel(){
        return $this->structModel;
    }

    /**
     * @return string Retorna los campos separados por coma (,)
     */
    protected function __getFieldsModel(){
        $fields = "";
        foreach ($this->structModel as $field => $value){

            $fields .= $field.  ', ';

        }
        return trim($fields, ', ');
    }

    public function __getNameModel(){
        return $this->model;
    }

    /**
     * @param $field Nombbre del campo del modelo
     * @param array $val Array de valores
     * @return $this
     */
    public function whereIn($field, Array $array_val) {
        $_w = $this->getWhere($field, 'IN');

        foreach ($array_val as $val) {
            $_w .= '?,';
            $this->_prepared_data = array_merge($this->_prepared_data, [$val]);
        }
        $this->_where .= trim($_w, ',') . ')';
        #pr($this->_prepared_data);

        return $this;
    }

    /**
     * Crea un la clausula where
     * @param array $conditions son las condiciones que debe cumplir el query para poder ejecutar una sentencia SQL
     * @return $this
     */
    public function where($field, $value = ''){

        if (!is_array($field)) {
            $this->_where .= $this->getWhere($field, '=') . '?';
            $this->_prepared_data = array_merge($this->_prepared_data, [$value]);
        } else {
            #pr($field);
            foreach ($field as $_field => $val) {
                $this->_where .= $this->getWhere($_field, '=') . '?';
                $this->_prepared_data = array_merge($this->_prepared_data, [$val]);
            }
        }

        return $this;
    }


}