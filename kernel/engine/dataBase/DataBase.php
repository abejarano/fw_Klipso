<?php


namespace fw_Klipso\kernel\engine\dataBase;

use fw_Klipso\kernel\classes\abstracts\aModels;



abstract class DataBase
{
    private $db = "";
    protected $_where = "";
    protected $_limit = "";
    protected $_join = array();
    protected $_field_join = array();

    abstract public function __getNameModel();
    abstract protected function __getFieldsModel();

    public function DB(){
        global $conexion_pdo;
        return $conexion_pdo;
    }

    private function getSelectiveFields(Array $fields){
        $select = "";
        
        foreach ($fields as $model => $value) {
            /* creo un array de campos para el modelo que indica la variable $model */
            $array_field = explode(',', $value);
            
            foreach ($array_field as $field) {
                $var =  trim($field);
                $var = $this->checkFieldExistsModel($var);
                if(!empty($var))
                    $select .= $var . ",";
            }
        }
        
        return trim($select,',');
    }

    private function createJoinForDeleteOrUpdate(){

        $delete_join = explode('on', str_replace('FROM '.$this->__getNameModel(),'',str_replace('INNER JOIN','',$this->getInnerJoin())));

        $campo_enlace = explode('=', $delete_join[1]);
        //$campo_enlace = $campo_enlace[1];

        return ' ' . $this->_where . ' and ' . str_replace($this->__getNameModel().'.','',$campo_enlace[1]) . ' in ( select '.str_replace(trim($delete_join[0]).'.','',$campo_enlace[0]) . ' from ' . trim($delete_join[0]) . ' )';

    }

    /**
     * Performs a data insertion in the indicated model
     * @param array $values Is an array where the index is the field of the field and the value is the value that will
     * be saved in that field
     */
    public function save(Array $values){
        $var = [];
        $x_var = "";
        $Query = "INSERT INTO " . $this->__getNameModel() . "(";

        foreach ($values as $key => $val){
            $Query .= $key . ",";
            $x_var .= "?,";
            $var[] = $val;
        }
        $Query = trim($Query,',') . ') VALUES (' . trim($x_var,',') . ')';

        return $this->raw($Query,$var);
    }
    /**
     * Ejecuta un delete;
     */
    public function delete(){

        if(count($this->_join) > 0){
            $join_where  = $this->createJoinForDeleteOrUpdate();
            $Query = "DELETE FROM ".$this->__getNameModel() . $join_where;

        }else
            $Query = "DELETE FROM " . $this->__getNameModel() . " " . $this->_where;

        return $this->raw($Query);

    }
      /**
     * Ejecuta un update;
     */
    public function update(Array $value){        
        #$Query = "UPDATE " . $this->__getNameModel() . " set  ";

        
        foreach ($value as $key => $val){
            $Query .= $key . "=?,";
            $var[] = $val;
        }
        $Query =  "UPDATE " . $this->__getNameModel() . " set  " . trim($Query,',') .' '. $this->_where;

        
        #$Query = trim($Query,',') . ') VALUES (' . trim($x_var,',') . ')';
        try {

            return $this->raw($Query,$var );
        } catch (\Exception $e) {

            echo '<pre>';
            echo $e->getMessage() . PHP_EOL;
            print_r($e->getTraceAsString());
            die();
        }
    }
    public function count(){
        $SELECT = "SELECT count(*) as cantidad ";
        # si e mayor a cero entonces la consulta es un inner join
        if(count($this->_join) > 0)
            $SELECT .= $this->getInnerJoin() .' '. $this->_where;

        else
            $SELECT .= ' FROM ' .$this->__getNameModel().' '. $this->_where;
        try {

            $rs =  $this->raw($SELECT);
            return $rs["cantidad"];
        } catch (\Exception $e) {

            echo '<pre>';
            echo $e->getMessage() . PHP_EOL;
            print_r($e->getTraceAsString());
            die();
        }
    }
    /**
     * Crea una consulta a la db
     * @param string $field campos que retornara la consulta.
     * @return array Arrray con el resultado de la consulta.
     */
    public function find($field = "", $order_by = ""){
        $SELECT = "SELECT ";

        if(!empty($field) && !is_array($field))
            $this->checkFieldExistsModel($field);

        elseif (is_array($field)) 
            $field = $this->getSelectiveFields($field);

        else
            $field = $this->__getFieldsModel();
   
        # si e mayor a cero entonces la consulta es un inner join
        if(count($this->_join) > 0)
            $SELECT .= $field . $this->getInnerJoin() .' '. $this->_where;
            
        else
            $SELECT .= $field . ' FROM ' .$this->__getNameModel().' '. $this->_where;

        if(!empty($order_by)){

            $array_order = explode(",", $order_by);

            if ($array_order[0] == '-')
                $SELECT .= " ORDER BY ".$array_order[1] . " desc ";
            else
                $SELECT .= " ORDER BY ".$array_order[1] . " asc ";
        }


        if(!empty($this->_limit))
            $SELECT .= $this->_limit;

        try {
            
            return $this->raw($SELECT);
        } catch (\Exception $e) {

            echo '<pre>';
            echo $e->getMessage() . PHP_EOL;
            print_r($e->getTraceAsString());
            die();
        }



    }
    private function getInnerJoin(){
        /*print_r($this->_join);
        die();*/
        $inner = "";
        foreach ($this->_join as $key => $value) {
            $inner .= " ".$value;
        }
        return $inner;
    }
    private function checkFieldExistsModel($field){
        $array_field = explode(',', $field);
        foreach ($array_field as $value){
            return aModels::findFieldModel($value, false);
        }
        return '';
    }

    /**
     * Ejecuta sentencias SQL cudras, ejemplo: select nombre from usuario where id_usuario = ?
     * @param $sql La sentencia SQL como tal
     * @param array $data son los valores que tendra los parametros del SQL, ejemplo $var = [1]
     * @return un ResultSet de la base de datos.
     */
    public function raw($sql, $data = array(), $return = true){
        $stmt = $this->DB()->prepare($sql);

        if(empty($data))
            $stmt->execute();
        else
            $stmt->execute($data);

        /* verifica si es una consulta lo que se ejecuto: insert, update, select, delete*/

        $tipo_sentencia = substr($sql, 0,6);

        if(preg_match('/select/', strtolower($tipo_sentencia))){
            $result = $stmt->fetchAll($this->DB()->fetch_style('FETCH_ASSOC'));

            $stmt->closeCursor();
            if(count($result) == 1)
                return $result[0];
            else
                return $result;

        }else if(preg_match('/delete/', strtolower($tipo_sentencia)) || preg_match('/update/', strtolower($tipo_sentencia))){
            $afectados = $stmt->rowCount();
            $stmt->closeCursor();
            return $afectados;

        }else if(preg_match('/insert/', strtolower($tipo_sentencia))){
            $lastInsertId = '';
            if(!$return)
                return '';

            if ( $this->DB()->getAttribute($this->DB()->getTypeDriver()) == 'mysql')                
                    $lastInsertId = $this->DB()->lastInsertId();

            elseif ( $this->DB()->getAttribute($this->DB()->getTypeDriver()) == 'pgsql'){
                $model_name = $this->__getNameModel();
                $field_serial  = $this->getSerialfield();
                $seq = $model_name . "_" . $field_serial . "_" . "seq";

                #checa si la secuencia existe
                $sql = "select relname from pg_class where relname = '".$seq."'";
                $stmt2 = $this->DB()->prepare($sql);
                $stmt2->execute();
                $result = $stmt2->fetchAll($this->DB()->fetch_style('FETCH_ASSOC'));
                if(!empty($result[0]["relname"])){
                    $lastInsertId = $this->DB()->lastInsertId($result[0]["relname"]);
                }
                $stmt2->closeCursor();
            }


            $stmt->closeCursor();
            return $lastInsertId;

        }

    }
    private function getSerialfield(){
        foreach ($this->__getStructModel() as $key => $val){
            if(preg_match("/serial/", $val)){
                return $key;
            }
        }
    }
}