<?php
namespace fw_Klipso\kernel\engine\ORM\abstracts;

use fw_Klipso\kernel\engine\ORM\Dark;

abstract class aModels extends Dark {
    protected static $fields = [];
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

        $this->structModel = $this->__fields__();

        # coloca a disposicion cada campo del modelo como un atributo de la clase
        foreach ($this->structModel as $key => $val) {
            $this->$key = '';
        }

        $this->uniq = $this->__setUnique();
        $this->pk = $this->__setPrimary();
        $this->fk = $this->__foreignKey();

        $this->extractFields();
        
    }
    private function detectNameModel(){
        
        $name_model = explode('\\',get_class($this));
        #print_r($name_model).PHP_EOL;
        $model_name = trim(strtolower($name_model[count($name_model) -1]));
        
        $this->setTable(strtolower($model_name));
        

        return strtolower($model_name);
    }
    private function extractFields(){

        if(count($this->structModel) == 0)
            die('Not defined the structure of the model '.$this->__getNameModel().' is possibly not returning the fields, foreign keys and unique 
            fields when you typed the model.' .  PHP_EOL);
        # pr(count($this->structModel));
        foreach ($this->structModel as $key => $value) {
            /* obtiene el tipo de dato, simplicandolos a solo numericos y de cadena */
            $val = $value[0];

            if(preg_match('/BIGINT/', $val) ||
                preg_match('/INTEGER/', $val) ||
                preg_match('/NUMERIC/', $val) ||
                preg_match('/REAL/', $val) ||
                preg_match('/REAL/', $val) ||
                preg_match('/serial/', $val) ||
                preg_match('/AUTO INCREMENT/', $val) ||
                preg_match('/bool/', $val) ||
                preg_match('/boolean/', $val) ||
                preg_match('/DECIMAL/', $val)
            ){

                $tipo_dato = 'NUMERIC';
            }
            if(preg_match('/char/', $val) ||
                preg_match('/text/', $val) ||
                preg_match('/datetime/', $val) ||
                preg_match('/timestamp without time zone/', $val) ||
                preg_match('/date/', $val) ||
                preg_match('/character varying/', $val) ||
                preg_match('/varchar/', $val)
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

    /**
     * busca si un campo pasado por pasametro es realmente campo del model
     * @param $name_field nombre del campo que se desea buscar
     * @return bool True si el campo pertenece al modelo y False y no pertenece
     */
    public static function findFieldModel($name_field, $return_type = true){
        if ($name_field == 'id') {
            return 'NUMBER';
        }
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
        throw new \Exception("The field $name_field does not exist in the model " . aModels::$_model);
    }

}