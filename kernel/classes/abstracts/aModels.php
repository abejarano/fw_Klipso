<?php
namespace fw_Klipso\kernel\classes\abstracts;

use fw_Klipso\kernel\engine\dataBase\DataBase;
use fw_Klipso\kernel\engine\dataBase\Func\FindModel;

abstract class aModels extends DataBase {
    private $structModel = array();
    private static $fields = array();
    private $model;
    private static $_model = '';
    private $uniq;
    private $pk;
    private $fk;


    abstract public function __fields__();
    abstract public function __setPrimary();
    abstract public function __setUnique();
    abstract public function __foreignKey();
    abstract public function __getPrefix();

    public function __construct($search_prefix = false)
    {
        aModels::$_model = $this->detectNameModel();
        #aModels::$fields = array();
        if(!$search_prefix){

            $this->structModel = $this->__fields__();

            $this->uniq = $this->__setUnique();
            $this->pk = $this->__setPrimary();
            $this->fk = $this->__foreignKey();

            $this->extractFields();
        }
        #

        
        
    }
    private function detectNameModel(){
        
        $name_model = explode('\\',get_class($this));
        #print_r($name_model).PHP_EOL;
        $model_name = trim(strtolower($name_model[count($name_model) -1]));
        
        $this->setTable(strtolower($model_name));
        

        return strtolower($model_name);
    }

    /**
     * Extrae los campos de la estructura del model, y los coloca en el array fields siendo el nombre del campo
     * la llave y el valor el tipo de dato
     */
    protected  function __getStructModel(){
        return $this->structModel;
    }
    private function extractFields(){
        if(count($this->structModel) == 0)
            die('Not defined the structure of the model '.$this->__getNameModel().' is possibly not returning the fields, foreign keys and unique 
            fields when you typed the model.' .  PHP_EOL);

        foreach ($this->structModel as $key => $value){
            /* obtiene el tipo de dato, simplicandolos a solo numericos y de cadena */

            if(preg_match('/BIGINT/', $value) ||
                preg_match('/INTEGER/', $value) ||
                preg_match('/NUMERIC/', $value) ||
                preg_match('/REAL/', $value) ||
                preg_match('/REAL/', $value) ||
                preg_match('/serial/', $value) ||
                preg_match('/AUTO INCREMENT/', $value) ||
                preg_match('/bool/', $value) ||
                preg_match('/boolean/', $value) ||
                preg_match('/DECIMAL/', $value)
            ){

                $tipo_dato = 'NUMERIC';
            }
            if(preg_match('/char/', $value) ||
                preg_match('/text/', $value) ||
                preg_match('/datetime/', $value) ||
                preg_match('/timestamp without time zone/', $value) ||
                preg_match('/date/', $value) ||
                preg_match('/character varying/', $value) ||
                preg_match('/varchar/', $value)
            ){
                $tipo_dato = 'STRING';
            }
            aModels::$fields[] = [ $this->model . '.' . trim($key) => $tipo_dato];
            #$this->fields[] = trim($key);
        }
        
    }   
    private function setTable($table){
        if(!empty($this->__getPrefix()))
            $this->model = $this->__getPrefix() .  '_' . $table;
        else
            $this->model = $table;
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
    private function renderWhere($conditions){
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
#echo  $where . PHP_EOL;
        if ($where == " where ")
            return "";
        else
            return $where;

    }

    /**
     * busca si un campo pasado por pasametro es realmente campo del model
     * @param $name_field nombre del campo que se desea buscar
     * @return bool True si el campo pertenece al modelo y False y no pertenece
     */
    static function findFieldModel($name_field, $return_type = true){
        
        foreach (aModels::$fields as $value){
            foreach ($value as $field => $type){
                $field_array = explode(".",$field);

                if($name_field == $field_array[1]){
                    if($return_type)
                        return $type;
                    else
                        return $field;
                }
            }
        }
        throw new \Exception("The field $name_field does not exist in the model ");
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