<?php

/**
 *   14/02/2014
 *   autor: Angel Bejarano
 *   Driver para conexiones de base de datos mysql y postgres
 *   Vr 2 Simplificada;
 */


namespace fw_Klipso\kernel\engine\dataBase;
use PDO;

class ConexionPDO extends PDO {
    public function __construct(){
        $params = unserialize(DATABASE);

        $driver = $params['ENGINE'];

        if ($driver == 'pgsql' || $driver == 'mysql') {
            $db = $params['NAME'];
            $host = $params['HOST'];
            $port = $params['PORT'];
            $user = $params['USER'];
            $pass = $params['PASSWORD'];

            $dsn = "$driver:dbname=$db;host=$host;port=$port";
            if ($driver == 'pgsql') {
                $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_PERSISTENT => false
                );
            } else {
                $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_PERSISTENT => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                );
            }
        } elseif ($driver == 'sqlite') {
            $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => false
            );
            $db = $params['ROUTE_DB'];
            $dsn = "sqlite:$db";
            $user = null;
            $pass = null;
        }
        try {
            parent::__construct($dsn, $user, $pass, $options);

            restore_exception_handler();

            if (!empty($params['SHEMA'])) {
                $this->setSchema($params['SHEMA'], $driver);
            }
            $this->driver = $driver;
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }
    /** manejo de transacciones **/
    public function setBeginTrans(){
        parent::beginTransaction();
    }
    public function setCommit($commit){
        if($commit){
            parent::commit();
        }else{
            parent::rollBack();
        }
    }
    private function setSchema($schema, $driver)
    {
        switch ($driver) {
            case 'mysql':
                //$stmt->query('use '.$schema);
                break;
            case 'pgsql':
                $stmt = parent::prepare('set search_path to ' . $schema);
                $stmt->execute();
                break;
            default:
                # code...
                break;
        }
    }
    public function fetch_style($type){
        switch ($type){
            case 'FETCH_BOTH':
                return PDO::FETCH_BOTH;
            case 'FETCH_ASSOC':
                return PDO::FETCH_ASSOC;
            case 'FETCH_BOUND':
                return PDO::FETCH_BOUND;
            case 'FETCH_CLASS':
                return PDO::FETCH_CLASS;
            case 'FETCH_CLASSTYPE':
                return PDO::FETCH_CLASSTYPE;
            case 'FETCH_INTO':
                return PDO::FETCH_INTO;
            case 'FETCH_LAZY':
                return PDO::FETCH_LAZY;
            case 'FETCH_NAMED':
                return PDO::FETCH_NAMED;
            case 'FETCH_NUM':
                return PDO::FETCH_NUM;
            case 'FETCH_OBJ':
                return PDO::FETCH_OBJ;
            case 'FETCH_PROPS_LATE':
                return PDO::FETCH_PROPS_LATE;
        }

    }
    public function getTypeDriver(){
        return parent::ATTR_DRIVER_NAME;
    }
}
?>