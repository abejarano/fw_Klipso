<?php

/**
 * User: Angel Bejarano
 * Date: 28/10/2019
 * Time: 9:56pm
 */
namespace fw_Klipso\kernel\engine\ORM;


trait DinamyORM
{
    protected $data = [];

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

     public function __get($name)
     {

         if (array_key_exists($name, $this->data)) {
             return $this->data[$name];
         }

         $trace = debug_backtrace();
         trigger_error(
             'Propiedad indefinida mediante __get(): ' . $name .
             ' en ' . $trace[0]['file'] .
             ' en la línea ' . $trace[0]['line'],
             E_USER_NOTICE);
         return null;
     }

     public function getAttributes() {
        return $this->data;
     }
}