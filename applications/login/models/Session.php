<?php

namespace fw_Klipso\applications\login\models;

//include BASE_DIR . '/fw_klipso/kernel/engine/dataBase/TypeFields.php';
use fw_Klipso\kernel\classes\abstracts\aModels;
use fw_Klipso\kernel\engine\dataBase\DataType;
use fw_Klipso\kernel\engine\dataBase\TypeFields;


class Session extends aModels{
    private $prefix_model = 'fw_klipso';

    public function __fields__()
    {
        $field = [
            'session_id' => DataType::FieldString(200, true),
            'session_data' => DataType::FieldText(true),
            'expire_date' => DataType::FieldDateTime(true),
            'status' => DataType::FieldChar(true, 'A')
        ];
        return $field;
    }

    public function __setPrimary()
    {
        $pk = ['session_id'];
        return $pk;
    }

    public function __setUnique()
    {
        // TODO: Implement __setUnique() method.
    }

    public function __foreignKey()
    {
        // TODO: Implement __foreignKey() method.
    }
    public function __getPrefix()
    {
        return $this->prefix_model;
    }
}