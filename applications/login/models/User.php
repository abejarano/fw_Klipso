<?php

namespace fw_Klipso\applications\login\models;

use fw_Klipso\kernel\engine\dataBase\DataType;
use fw_Klipso\kernel\engine\dataBase\TypeFields;

class User extends aModels
{
    private $prefix_model = 'fw_klipso';
    public function __fields__()
    {
        /*
        * Create a variable that stores an array with the fields that your model will have. Then returns that variable
        *
        * $field = [
        *    'campo1' => DataType::FieldString(200, true),
        *    'campo2' => DataType::FieldString(200, true),
        * ];
        *
        * return $field;
        *
        */
        $field = [
            'user_id' => DataType::FieldAutoField(),
            'user_name' => DataType::FieldString(200,false),
            'user_email' => DataType::FieldString(200,true),
            'user_login' => DataType::FieldString(100,true),
            'user_pass' => DataType::FieldString(100, true),
            'user_is_superuser' => DataType::FieldString(1, true, 'N'),
            'user_is_staff' => DataType::FieldString(1, true, 'N'),
            'user_active' => DataType::FieldBoolean(true,true),
        ];
        return $field;
    }

    public function __setPrimary()
    {
        /* Create the primary key of your model by creating a variable that stores the field that will be PK. for example.
         * Then returns that variable
         *
         * $pk = [
         *     'campo1'
         * ];
         *
         * return $pk;
         *
         */
        $pk = [
            'user_id'
        ];
        return $pk;

    }

    public function __setUnique()
    {
        /* Create unique fields for your model by creating a variable that stores those cmpos. for example.
         * Then returns that variable
         *
         * $uniq = [
         *     'campo1'
         * ];
         *
         * return $uniq;
         *
         */
        $uniq = [
            'user_login'
        ];
        return $uniq;
    }

    public function __foreignKey()
    {
        /* It creates foreign keys, storing in an array variable each field that has relation to foreign models in
         * the following way.
         *
         * $fk = [
         *     'campo1' => Constrainst::ForeignKey('Name_of_foreign_model','Relational_field_of_the_foreign_model',Constrainst::on_delete(false)),
         *     'campo2' => Constrainst::ForeignKey('Name_of_foreign_model','Relational_field_of_the_foreign_model')
         * ];
         * return $fk;
         *
         */


    }

    public function __getPrefix()
    {
        return $this->prefix_model;
    }
}