<?php

/**
 * User: abejarano
 * Date: 17/10/19
 * Time: 6:30 AM
 */

namespace fw_Klipso\kernel\Views;


use fw_Klipso\kernel\classes\abstracts\aController;
use fw_Klipso\kernel\engine\middleware\Request;
use fw_Klipso\kernel\Views\interfaces\ViewInterface;

abstract class CreateView extends aController implements ViewInterface
{
    public $model_name = '';
    public $template_name = '';
    public $redirect = '';
    private $path_app = '';

    public function __construct($app)
    {
        parent::__construct($app);
        $this->path_app = $app;
        if(empty($this->redirect)) {
            pr('Define a redirect URL using the redirect attribute');
        }
        if(empty($this->model_name)) {
            pr('You must define the name of the model that will be used to generate the form');
        }

        Forms::setModel($this->model_name);

        $this->get();
    }

    private function get() {

    }

    public function save_post(Request $request)
    {
        // TODO: Implement save_post() method.
    }

    public function update_post(Request $request)
    {
        // TODO: Implement update_post() method.
    }

    public function get_paginate(Request $request)
    {
        // TODO: Implement get_paginate() method.
    }

}