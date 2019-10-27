<?php

/**
 * User: abejarano
 * Date: 27/10/19
 * Time: 9:30 AM
 */

namespace fw_Klipso\kernel\Views\forms;


class ValidateForms
{
    private $_path_app;
    private $_list_fields;

    public function __construct($app)
    {
        $this->_path_app = $app;
    }

    public function __loadFieldMoel($_model) {
        # instanciar la clave model
        $path_model = $this->_path_app . '\\models\\' .$_model;

        if (!class_exists($path_model)) {
            pr('El modelo ' . $_model . ' NO existe en su aplicación');
        }

        $objModel = new $path_model;

        $this->_list_fields = $objModel->__fields__();

    }

    public function fieldRequired(Array $sendData)  {

        foreach ($this->_list_fields as $field => $attributes) {
            # si el atributo 2 es verdadero entonces es requerido
            if ($attributes[2]) {
                # checar si este campo esta presente en la data enviada
                if(key_exists($field, $sendData)) {
                    if (trim($sendData[$field]) != '') {

                        return [
                            'message' => 'El campo ' . $attributes[1] . ' es requerido.',
                            'status' => 'failed'
                        ];
                    }
                }
            }

        }

        return [
            'status' => 'ok'
        ];
    }

}

