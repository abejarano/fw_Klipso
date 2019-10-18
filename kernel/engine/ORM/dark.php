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

    public function with($model){

        if(is_array($model)){
            foreach ($model as $key => $value) {
                $this->prepareInnerJoin($value);    
            }
        }else
            $this->prepareInnerJoin($model);
       
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

    private function prepareInnerJoin($model){
        /* obtiene el array de fk del model principal */
       $fk = $this->fk;

       /* busca e instancia el modelo con el que se desea hacer join */
       $Find = new FindModel($model);
       $instance_model = $Find->instanceModelFound();
       

       /* obtiene los pk's del modelo con que se desea hacer join */
       $pk_model = $instance_model->__setPrimary();

       

       /* corro los fk del modelo principal en busca de alguna relacion con el modelo que se desea hacer join */
       $field_related_join = ""; #campo donde hace join el model que se desea hacer join
       $field_related_model = ""; #campo con el que hace join el modelo principal
       foreach ($fk as $field_related => $val_fk){

           foreach ($pk_model as $pk_model_related){
               if(preg_match('/('.$pk_model_related.')/', $val_fk, $coincidencias, PREG_OFFSET_CAPTURE, 3)){
                   $field_related_join = $instance_model->__getNameModel() . '.' . $pk_model_related;
                   $field_related_model = $this->__getNameModel() . '.' . $field_related;
               }
           }
       }

       if(empty($field_related_model))
           throw new \Exception("The ".$this->__getNameModel()." model has no foreign key with the ".$instance_model->__getNameModel(). " model");

       $join =  ' INNER JOIN ' .$instance_model->__getNameModel() . ' on ' .$field_related_join.' = '.$field_related_model;

       if(count($this->_join) == 0)
           $this->_join[] = " FROM " . $this->__getNameModel() .$join;
       else
           $this->_join[] = $join;

   }

   /**
     * Crea una consulta a la db
     * @param string $field campos que retornara la consulta.
     */
    public function find($field = ""){
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
    protected  function __getStructModel(){
        return $this->structModel;
    }

    /**
     * @return string Retorna los campos separados por coma (,)
     */
    protected function __getFieldsModel(){
        $fields = "";
        foreach (aModels::$fields as $value){
            foreach ($value as $field => $type){
                $fields .= $field.  ', ';
            }
        }
        return trim($fields, ', ');
    }

    public function __getNameModel(){
        return $this->model;
    }

}