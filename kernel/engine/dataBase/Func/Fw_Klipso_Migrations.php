<?php

namespace fw_Klipso\kernel\engine\dataBase\Func;

use fw_Klipso\kernel\classes\abstracts\aModels;
use fw_Klipso\kernel\engine\dataBase\DataType;
use fw_Klipso\kernel\engine\dataBase\TypeFields;

class Fw_Klipso_Migrations extends aModels
{
    private $prefix_model = '';

    public function __fields__()
    {
        $field = [
            'migrations_id' => DataType::FieldAutoField(),
            'app_name' => DataType::FieldString(40, true),
            'model_name' => DataType::FieldString(40, true),
            'structure' => DataType::FieldText(true),
            'date_applied' => DataType::FieldDateTime(true, DefaultDateTimeNow())
        ];
        return $field;
    }

    public function __setPrimary()
    {
        $pk = [
            'migrations_id'
        ];
        return $pk;

    }

    public function __setUnique(){ }

    public function __foreignKey(){ }
    public function __getPrefix()
    {
        return $this->prefix_model;
    }
}