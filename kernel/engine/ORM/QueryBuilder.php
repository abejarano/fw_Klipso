<?php
/**
 * User: Angel Bejarano
 * Date: 28/10/2019
 * Time: 11:05pm
 */

namespace fw_Klipso\kernel\engine\ORM;

use  fw_Klipso\kernel\engine\dataBase\Func\FindModel;

trait QueryBuilder
{
    protected $_where = "";
    protected $_limit = "";
    protected $_join = [];
    protected $_field_join = [];
    protected $_SQL = "";

    protected function prepareInnerJoin($foreign_model) {
        /* obtiene el array de fk del model principal */
        $class = get_class($this);
        $obj_class = new $class;
        $fk = $obj_class->__foreignKey();

        /* busca e instancia el modelo con el que se desea hacer join */

        try {
            $Find = new FindModel($foreign_model);
            $instance_model = $Find->instanceModelFound();

            /* obtiene los pk's del modelo con que se desea hacer join */
            $pk_model = $instance_model->__setPrimary();
            $pk_model = array_merge($pk_model, $instance_model->__setUnique());

            if (count($pk_model) == 0) {
                $pk_model[] = 'id';
            }

            $field_related_join = ""; #campo donde hace join el model que se desea hacer join
            $field_related_model = ""; #campo con el que hace join el modelo principal

            /* corro los fk del modelo principal en busca de alguna relacion con el modelo que se desea hacer join */
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

        } catch (\Exception $e) {
            pr($e->getMessage());
        }

    }
}