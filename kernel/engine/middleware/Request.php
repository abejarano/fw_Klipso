<?php

namespace fw_Klipso\kernel\engine\middleware;

class Request{
    private static $post = array();
    private static $files = array();
    private static $get = array();
    private static $type_request = '';
    private static $excepCSRFTOKEN = array();
    public static $current_url;
    
    public function __construct()
    {
        setLanguage();
        $validate_toke = false;

        $basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
        Request::$current_url = substr($_SERVER['REQUEST_URI'], strlen($basepath));
       

        /* detecta si se hizo algun request */
        if ((count($_GET) > 0)){
            Request::$type_request = 'GET';

        }
        if ((count($_POST) > 0)){
            Request::$type_request = 'POST';
            $validate_toke = true;
        }

        if($validate_toke)
            if(!Request::checkCSRFTOKEN()){
                return;
            }

        if(isset($_GET) && count($_GET) > 0){
            $this->setGet();
        }
        if(isset($_POST) && count($_POST) > 0)
            $this->setPost();

        if(isset($_FILES) && count($_FILES) > 0)
            $this->setFiles();

    }

    private static function checkCSRFTOKEN(){
        $uri = Request::$current_url;
        if (strstr($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));
        $uri = '/' . trim($uri, '/');


        if(in_array(trim($uri, '/'), Request::$excepCSRFTOKEN)){
            return true;
        }
        if(!isset($_REQUEST['csrftoken']) || $_REQUEST['csrftoken'] != $_SESSION['csrftoken']){
            die('
                    <h1>forbidden 403</h1>
                    <p>CSRFTOKEN in '.$uri.'</p>
                ');
        }

        return true;
    }
    public function isPost(){
        if(Request::$type_request == 'POST')
            return true;
        else
            return false;
    }
    public function _post($key){
        if(isset(Request::$post[$key]))
            return Request::$post[$key];
        else
            return false;

    }
    public function _postAll(){

        return Request::$post;

    }
    public function _get($key){
        if(isset(Request::$get[$key]))
            return Request::$get[$key];
        else
            return false;

    }
    public function _getAll(){
        return Request::$get;

    }
    public function _files($key){
        if(isset(Request::$files[$key]))
            return Request::$files[$key];
        else
            return null;

    }
    public function _filesAll(){
        return Request::$files;

    }
    private function setFiles(){
        foreach ($_FILES as $key => $value){
            Request::$files[$key] = $value;
            unset($_FILES[$key]);
        }
    }
    private function setPost(){
        foreach ($_POST as $key => $value){
            Request::$post[$key] = $value;
            unset($_POST[$key]);
            unset($_REQUEST[$key]);
        }
    }
    private function setGet(){
        foreach ($_GET as $key => $value){
            Request::$get[$key] = $value;
            unset($_GET[$key]);
            unset($_REQUEST[$key]);
        }
    }
    public static function excepCSRFTOKEN($url_exception){
        self::$excepCSRFTOKEN[] = $url_exception;
    }
}
