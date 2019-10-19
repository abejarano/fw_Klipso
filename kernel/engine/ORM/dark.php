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
     * Crea un la clausula where
     * @param array $conditions son las condiciones que debe cumplir el query para poder ejecutar una sentencia SQL
     * @return $this
     */
    public function where(Array $conditions){
        if(count($conditions) > 0)
            $this->_where = $this->renderWhere($conditions);

        return $this;
    }

    /**
     * Verifica que los campos involucrados en la condicion existen en el modelo
     * @param $conditions array de condiciones que se colocan en la clausula where del query
     * @return Retorna el where listo para ser usado por el query
     */
    private function renderWhere($conditions) {
        $where = " WHERE ";
        $simbolo = "";
        foreach ($conditions as $key => $val){
            # obtener nombre del campo
            if(preg_match('/{/', $key)){
                $pos_field = strpos($key,'{');
                $_field = substr($key,0,$pos_field);
            }else{
                $_field = $key;
            }

            if(preg_match('/{=}/', $key)){
                $simbolo = ' = ';
            }

            /* si tiene un or, in, not in elimina el signo de llaves */
            if(preg_match('/{or}/', $key))
                $simbolo =  str_replace('{or}', 'OR', $val);
            else if(preg_match('/{in}/', $key))
                $simbolo =  str_replace('{in}', 'IN', $val);
            else if(preg_match('/{not in}/', $key))
                $simbolo =  str_replace('{in}', 'NOT IN', $val);

            $type = aModels::findFieldModel(strtolower($_field));
            $value = strtolower((strip_tags($val)));

            if($type == 'STRING')
                $value = "'".$value."'";
            
            if(!empty($simbolo))
                $where .= $_field . $simbolo . " $value and ";
            else
                $where .= $_field . " = $value and ";
        }
        $where = trim($where,' and ');

        if ($where == " where ")
            return "";
        else
            return " ".$where;

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

}