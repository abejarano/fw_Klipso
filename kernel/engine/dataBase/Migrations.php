<?php
namespace fw_Klipso\kernel\engine\dataBase;

use fw_Klipso\kernel\classes\abstracts\aModels;
use fw_Klipso\kernel\engine\dataBase\Func\Fw_Klipso_Migrations;

class Migrations{
    /**
     * @param $table_name Nombre de la tabla
     * @param array $field array de campos que tendra la tabla
     */
    private static $field = array();
    private static $pk = '';
    private static $uniq = '';
    private static $name_database = '';

    /* Check if a table was created */
    public static function _checkExistsTable($name_table, $conexion){
        $sgdb = Migrations::getTypeDataBase();
        switch ($sgdb){
            case 'pgsql':
                $query = "SELECT * FROM pg_tables WHERE tablename = '".$name_table."' ";
                break;
            case 'mysql':
                $query = "SELECT * 
                          FROM information_schema.tables 
                          WHERE table_name = '".$name_table."' and TABLE_SCHEMA = '".Migrations::$name_database."' ";
                break;
            case 'sqlite':
                $query = "SELECT name FROM sqlite_master WHERE type='table' AND name = '".$name_table."' ";
                break;
            default:
                die('Error, database engine is not defined');
                break;
        }
        $stmt = $stmt = $conexion->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll($conexion->fetch_style('FETCH_BOTH'));
        $stmt->closeCursor();
        if(count($result) > 0){
            return true; #retorna que si existe
        }else{
            return false; #retorna que no existe
        }

    }

    private static function getTypeDataBase(){        
        if(defined('DATABASE')){
            $database = unserialize(DATABASE);
            Migrations::$name_database = $database['NAME'];
            return $database['ENGINE'];
        }else{
            die('Specify the database handler you want to use in the project' . PHP_EOL);
        }

    }

    private static function _unique(Array $field){

        return 'CONSTRAINT '.getNamerandom().'_uniq UNIQUE ('.implode(',', $field).')';

    }
	private static function _primaryKey($field){
        $sgdb = self::getTypeDataBase();        
        switch ($sgdb){
            case 'pgsql':
                $pk = " PRIMARY KEY ($field), ";
                break;
            case 'mysql':
                $pk = " PRIMARY KEY ($field), ";
                break;
            case 'sqlite':
                $pk = " PRIMARY KEY ($field), ";
                break;
            default:
                die('Error, database engine is not defined');
                break;
        }
        return $pk;

    }

    /**
     * @param $table_origin Modelo local
     * @param $local_field Columna del model local
     * @param $table_reference Nombre del model foraneo
     * @param $field_reference Campo del modelo foraneo
     */
    public static function _foreignKey(aModels $tableModel, $app){
        $array_fk = $tableModel->__foreignKey();

        if(empty($array_fk))
            return;

        $nameModel = $tableModel->__getNameModel();

        foreach ($array_fk as $field => $fk){
            $var_fk = trim(str_replace('{origin}', $nameModel,$fk));
            $sql_fk = trim(str_replace('{field_origin}', $field, $var_fk));
            $sql_fk =explode(";", $sql_fk);

            try{
                # 1 obtener el nombre del index;
                $name_index = Migrations::getNameIndex($sql_fk[0]);

                if(!Migrations::checkIndexExists($tableModel, $name_index)){
                    #excuete create index
                    $tableModel->raw($sql_fk[0]);
                    echo 'Index for model: ' . $tableModel->__getNameModel() . ' create finish' . PHP_EOL;
                }
            }catch (\PDOException $e){
                if($e->getMessage() != 'SQLSTATE[HY000]: General error'){
                    echo $e->getMessage() . PHP_EOL;
                    echo $sql_fk[0];
                    die();
                }

            }

            try{
                # 1 obtener el nombre del foreign key
                $name_foreign = Migrations::getNameForeignKey($sql_fk[1]);

                #2 se busca si existe el forieign
                if(!Migrations::checkForeignKey($tableModel, $name_foreign)){
                    #excuete create forenkey
                    $tableModel->raw($sql_fk[1]);
                    echo 'ForeigKey ' . $tableModel->__getNameModel() . ' create finish' . PHP_EOL;
                    Migrations::setRegisterStructureModel($tableModel, $app);
                }
            }catch (\PDOException $q){
                if($q->getMessage() != 'SQLSTATE[HY000]: General error'){
                    echo $q->getMessage() . PHP_EOL;
                    echo $sql_fk[1];
                    die();
                }

            }

        }
    }
    private static function getNameIndex($var){
        $sgdb = Migrations::getTypeDataBase();
        $pos_index = strpos($var,'INDEX') + 6;
        $name_index = trim(substr($var, $pos_index));
        switch ($sgdb){
            case  'mysql':
                $pos_fin = strpos($name_index, ' (');
                break;
            case 'pgsql':
                $pos_fin = strpos($name_index, ' ON');
                break;
        }
        $name_index = trim(substr($var, $pos_index, $pos_fin));
        return $name_index;

    }
    private static function getNameForeignKey($var){
        $pos_constraint = strpos($var,'CONSTRAINT') + 10;

        $name_foreign = trim(substr($var, $pos_constraint));
        $pos_foreign = strpos($name_foreign, 'FOREIGN');
        $name_foreign = trim(substr($var, $pos_constraint, $pos_foreign));

        return $name_foreign;

    }
    private static function checkIndexExists($tableModel, $name_index){
        $sgdb = Migrations::getTypeDataBase();
        switch ($sgdb){
            case 'pgsql':
                $query = "SELECT count(1) as existe
                          FROM pg_class 
                          WHERE relname='".$name_index."' ";
                break;
            case 'mysql':
                $query = "SELECT count(1) as existe 
                          FROM information_schema.statistics 
                          WHERE index_name='".$name_index."'";
                break;
        }
        $data = $tableModel->raw($query);
        if($data['existe'] == 0){
            return false;
        }else{
            return true;
        }
    }
    private static function checkForeignKey($tableModel, $name_foreign){
        $sgdb = Migrations::getTypeDataBase();
        switch ($sgdb){
            case 'pgsql':
                $query = "SELECT count(1) as existe
                          FROM information_schema.table_constraints 
                          WHERE constraint_name='".$name_foreign."' ";
                break;
            case 'mysql':
                $query = "SELECT count(1) as existe 
                          FROM information_schema.TABLE_CONSTRAINTS 
                          WHERE constraint_name='".$name_foreign."'";
                break;
        }


        $data = $tableModel->raw($query);
        if($data['existe'] == 0){
            return false;
        }else{
            return true;
        }
    }
    private static function createTable(aModels $tableModel){
        $create = 'CREATE TABLE '.trim($tableModel->__getNameModel(),'_').' ( ';

        /* coloca los campos en el create table */
        foreach ($tableModel->__fields__()  as $field => $data_type){
            $create .= $field . ' ' . $data_type . ',';

        }

        /* verifica si tiene primary key */
        if(count($tableModel->__setPrimary()) > 0){
            foreach ($tableModel->__setPrimary() as $field_pk){
                $create .= Migrations::_primaryKey($field_pk);
            }
        }else{
            /* si no tiene primary key se le crea uno, primero se agrega el campo */
            $field_pk = 'id';
            $create .= $field_pk . ' ' . DataType::FieldAutoField() . ',';

            /* se crea el pk */
            $create .= Migrations::_primaryKey($field_pk);
        }

        /* verifica si tiene unique */
        
        if( !empty($tableModel->__setUnique()) ){
            $create .= Migrations::_unique( (Array) $tableModel->__setUnique());
        }

        $create = trim($create,', ') . ')';
        try{
            $tableModel->raw($create);
            echo 'table ' . $tableModel->__getNameModel() . ' create finish' . PHP_EOL;
        }catch (\PDOException $e){

            echo $e->getMessage() . PHP_EOL;
        }
    }
    private static function createModelMigrations(){
        $model_migrations = new Fw_Klipso_Migrations();
        Migrations::createTable($model_migrations);
    }
    public static function _create(aModels $tableModel, $app){
        /* verifica si el modelo donde se almacena el historial de las migraciones fue creado */
        if(!Migrations::_checkExistsTable('fw_klipso_migrations', $tableModel->DB())){
            Migrations::createModelMigrations();
        }
        /* se verifica si la tabla existe */

        if(!Migrations::_checkExistsTable($tableModel->__getNameModel(), $tableModel->DB())){
            Migrations::createTable($tableModel);
            return true;
        }else{
            Migrations::alterModel($tableModel, $app);
            return false;
        }
    }

    /**
     * Executes an alter table for each new field added or each field deleted
     * @param aModels $tableModel Name of the model that is currently running
     */
    private static function  alterModel(aModels $tableModel, $app){
        $field = (Array) $tableModel->__fields__();
        $pk = $tableModel->__setPrimary();
        $uniq = $tableModel->__setUnique();
        $fk = $tableModel->__foreignKey();

        $table_name = $tableModel->__getNameModel();
        /* Search for the current model structure */
        $sql = "select structure
                from fw_klipso_migrations 
                where app_name = '".$app."' and model_name = '".$table_name."'
                group by date_applied desc 
                limit 1";

        #echo $table_name;

        $current_struct_model = $tableModel->raw($sql);
        $current_struct_model = json_decode(json_encode($current_struct_model), True);
        if(count($current_struct_model) > 0)
            $current_struct_model = unserialize($current_struct_model['structure']);
        else
            $current_struct_model = [];

        if (!empty($current_struct_model)) { 
            #se compra la estructura de campos
            $field_diff = array_diff( $field, $current_struct_model['fields']);
            
            if (count($field_diff) > 0){
                Migrations::setApplyAlterFieldModel($tableModel, $table_name, $field_diff);
                Migrations::setRegisterStructureModel($tableModel, $app);
            }
        }
            
    }

    private static function setApplyAlterFieldModel(aModels $instance_model, $name_model, Array $field){
        foreach ($field as $key => $value) {           
            $alter = "ALTER TABLE ".$name_model." add ".$key." ".$value;
                    
            $instance_model->raw($alter);
            echo 'Alter apply column '.$key . PHP_EOL;
        }
        
    }
    /**
     * It registers in the history the modification or the structure of the model
     * @param array $struct structure and foreign keys, primary key, unique, and others
     * @param $name_model model name
     */
    #private static function setRegisterMigrations(aModels $tableModel, $type_structure, $app, $model, Array $struct){
    public static function setRegisterStructureModel(aModels $tableModel, $applications_name, $debug=false){

        $struct_model = [
            'fields' => $tableModel->__fields__(),
            'primary_key' => $tableModel->__setPrimary(),
            'uniq' => $tableModel->__setUnique(),
            'foreign_key' => $tableModel->__foreignKey()
        ];

        if($debug) {
            print_r($struct_model);
            die();
        }
        $query = "insert into fw_klipso_migrations(app_name,model_name, structure)values(?,?,?)";
        $data = [
            $applications_name,
            $tableModel->__getNameModel(),
            serialize($struct_model)

        ];
        $tableModel->raw($query, $data, false);
    }
}



function action_update($bool = false){
    if(!$bool)
        return 'NO ACTION';
    else
        return 'CASCADE';
}
function action_delete($bool = false){
    if(!$bool)
        return 'NO ACTION';
    else
        return 'CASCADE';
}

function action_delete_restrict(){
    return 'RESTRICT';
}

function getNamerandom(){
    $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"; //posibles caracteres a usar
    $numerodeletras=4; //numero de letras para generar el texto
    $cadena = ""; //variable para almacenar la cadena generada
    for($i=0;$i<$numerodeletras;$i++){
        $cadena .= substr($caracteres,rand(0,strlen($caracteres)),1); /*Extraemos 1 caracter de los caracteres
			entre el rango 0 a Numero de letras que tiene la cadena */
    }
    return $cadena;
}