<?php


namespace fw_Klipso\kernel\Views\interfaces;


use fw_Klipso\kernel\engine\middleware\Request;


interface ViewInterface
{
    public function save_post(Request $request);
    public function update_post(Request $request);
    public function get_paginate(Request $request);
    public function make();

}