<?php

namespace fw_Klipso\kernel\templates;

use fw_Klipso\kernel\classes\abstracts\aController;
use fw_Klipso\kernel\engine\dataBase\ConexionDataBase;
use fw_Klipso\kernel\engine\middleware\Request;

class TemplateList {
    private $count_rows = 10;
    private $page_current = 1;
    private $obj_controller;
    private $obj_model;

    private $pages = null;
    private $rows = 0;
    private $previous = null;
    private $current = null;
    private $next = null;
    private $rs = null;


    public function __construct(Request $request){
        $this->request = $request;

        /* detecta si existe la variable page por el metodo GET */
        if(empty($this->request->_get('page'))){
            $this->page_current = 1;
        }else{
            $this->page_current = $this->request->_get('page');
        }
    }
    private function createQuery(Array $paramets){
        $query = '';

        if(isset($paramets['filter']) && count($paramets['filter']) > 0)
            $filter = ' WHERE '.implode($paramets['filter'], ' AND ');
        else
            $filter = '';

        $filter = trim($filter, ' AND ');

        /* determinar la cantidad de registro */
        $sql = "SELECT count(*) as total 
                FROM ".$paramets['models']->__getNameModel().' '.$filter;

        $var = $this->obj_model->raw($sql);
        $this->rows = $var['total'];
        $this->pages = ceil($this->rows  / $this->count_rows);
        $desde = ($this->page_current - 1) * $this->count_rows;
        $limit = " LIMIT ".$this->count_rows." offset ".$desde." ";


        $models = 'SELECT '.$paramets['fields'];
        $fields = ' FROM '.$paramets['models']->__getNameModel();
        $query = $models . $fields . ' '.$filter .  " ORDER BY 1 desc " . $limit ;

        try{
            $rs = $this->obj_model->raw($query);

            if(empty($rs))
                return;
            $this->rs = $rs;
        }catch (\PDOException $e){
            die($e->getMessage());
        }
    }
    public function setConf(Array $paramets_query, $count_rows = 10)
    {

        if ($count_rows != 10)
            $this->count_rows = $count_rows;
        if(isset($paramets_query["models"])){
            $this->obj_model = $paramets_query["models"];
            $this->createQuery($paramets_query);
            return;
        }

        if(!isset($paramets_query["query_set"])){
            echo "Unexpected error, must be defined the parameter query_set or parameter models";
            return;
        }
        if(isset($paramets_query["order"]))
            $this->execQuery_set($paramets_query["query_set"], $paramets_query['fields'], $paramets_query['filter'],$paramets_query["order"]);
        else
            $this->execQuery_set($paramets_query["query_set"], $paramets_query['fields'], $paramets_query['filter']);

    }
    private function execQuery_set($query_set, $fields, $filter = [], $order = []){
        if(!is_array($fields))
            $fields = explode(",", $fields);

        if(count($filter) > 0)
            $query_set = $query_set->where($filter);

        $this->rows = $query_set->count();
        $this->pages = ceil($this->rows  / $this->count_rows);
        $desde = ($this->page_current - 1) * $this->count_rows;



        try{
            $rs = $query_set->limit($this->count_rows, $desde)->find($fields, $order);

            if(empty($rs))
                return;
            $this->rs = $rs;
        }catch (\PDOException $e){
            die($e->getMessage());
        }

    }
    /*private function execPaginator(Array $rs){
        echo $this->page_current;
    }*/
    private function getPagination(){
        if($this->pages == 1){
            $this->next = 0;
            $this->previous = 0;
        }else if($this->pages < $this->page_current){

            $this->next = $this->page_current + 1;
            if($this->next > $this->pages)
                $this->next = $this->pages;

            if($this->page_current == 1)
                $this->previous = 0;
        }else{
            $this->previous = $this->page_current - 1;
            $this->next = $this->page_current + 1;
            if($this->next > $this->pages)
                $this->next = 0;
        }
        $data = [

        ];
        if(!isset($this->rs[0])){
            if(count($this->rs) > 0)
                $data[0] = $this->rs;
        }else{
            $data = $this->rs;
        }
        return [
            'count_page' => $this->pages,
            'previous_page' => $this->previous,
            'current_page' => $this->page_current,
            'next_page' => $this->next,
            'object_list' => $data,
            'rows' => $this->rows,
            'url' => DOMAIN_NAME. '/'.Request::$current_url

        ];
    }
    public function getPaginator(Array $context = []){

        $context_local = $this->getPagination();
        if(count($context) > 0){
            $context_local = array_merge($context_local,$context) ;
        }
        return $context_local;
    }
}