<?php

/**
 * Esta clase abstracta se encarga de comunicar con los template
 */
namespace fw_Klipso\kernel\classes\abstracts;


use fw_Klipso\kernel\engine\middleware\Session;
use Twig_Loader_Filesystem;
use Twig_Environment;

abstract class aController{

    private $app;
    private $path_app = '';
    private $dir_template;


    public function __construct($app){
        $this->path_app = $app;
        $this->detectApp($app);


    }

    private function addContextToken($context){
        /* si la cookie de token esta definida la agrega al contexto del template para que pueda ser usada en el template */
        if(isset($_SESSION['csrftoken'])){
            if(!empty($context))
                $context = array_merge($context,array('csrftoken' => $_SESSION['csrftoken'])) ;
            else
                $context = array('csrftoken' => $_SESSION['csrftoken']);
        }
        return $context;
    }
    private function addContextMessage($context){
        /* verifica si hay algun mensaje que enviar al template lo agrega al template */
        if(!empty(MESSAGE_TEMPLATE)){
            $context = array_merge($context, array('message' => MESSAGE_TEMPLATE));
        }

        return $context;
    }
    private function addContextDataSession($context){
        if (!isset($_SESSION["sessionid"]))
            return $context;

        $data = Session::getDataSesion();
        foreach ($data as $key => $val){
            $context = array_merge($context, array($key=>$val));
        }
        return $context;
    }
    private function addContextURLCurrent($context){
        return array_merge($context, array('URL_CURRENT' => URL()));
    }
    private function addContextLanguageSelected($context){
        return array_merge($context, array('LANGUAGE' => LANGUAGE));
    }
    /**
     * @param $template nombre del template que se va a renderizar
     * @param $context array de datos que se pasaran al template
     */
    public function render($template, $context = null){
        /* detectar si se está intentando renderizar un template basando en una peticion POST */
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            redirect($_SERVER['HTTP_ORIGIN']);
        }
        
        /* se identifica el nombre del dominio de la aplicacion web */
        if(!defined('DOMAIN_NAME') ||  empty(DOMAIN_NAME)){
            die('Sorry, in the settings.php file you must define the constant DOMAIN_NAME or give a value to that constant');
        }

        if(DOMAIN_NAME == 'http://localhost'){
            die('For localhost you must complete the url with the name of the folder that contains your project');
        }


        $context = $this->addContextToken($context);
        $context = $this->addContextMessage($context);
        $context = $this->addContextDataSession($context);
        $context = $this->addContextURLCurrent($context);
        $context = $this->addContextLanguageSelected($context);

        #detectar nombre una carpeta que anteponga  el nombre del template que se desea renderizar
        $count_folder = explode('/', $template);
        
        if(count($count_folder) > 1){
            $this->setStartEngineTemplate( BASE_DIR . TEMPLATE_DIR . '/' .$count_folder[0].'/');
            $template = $count_folder[1];
        }else
            $this->setStartEngineTemplate(BASE_DIR . TEMPLATE_DIR);
        

        $twig = new Twig_Environment($this->loader_template);
       # echo $template.'.' . EXT_TEMPLATE;
        if(!empty($context)){
            $tpl = $twig->render($template.'.' . EXT_TEMPLATE, $context);
        }else{
            $tpl = $twig->render($template.'.' . EXT_TEMPLATE);
        }
        $this->setCompleteLinkUrl($tpl);
        

    }
    private function setCompleteLinkUrl($template){
        $url_static_fiel = '/' . STATICFILES_DIRS;

        $tpl = str_replace('{ DOMAIN_NAME }',DOMAIN_NAME,str_replace('{DOMAIN_NAME}', DOMAIN_NAME, $template));
        $tpl = str_replace('{ STATICFILE_DIR }',$url_static_fiel,str_replace('{STATICFILE_DIR}', $url_static_fiel, $tpl));
        echo $tpl;
    }   
    /*public function setPathApplication($path){
        $this->path_app = $path;

    }*/
    private function setStartEngineTemplate($app){

        $this->dir_template = $app . '/';
        #echo $this->dir_template;
        if(!file_exists($this->dir_template)){
            die('El directorio para las vistas no esta creado, consulte el archivo settings.php y cree el directorio por favor');
        }
        $this->loader_template = new Twig_Loader_Filesystem($this->dir_template);

        
    }

    private function detectApp($path_app){
        $app = explode('/', $path_app);
        $this->app = trim($app[count($app) - 2]);
        
    }

    /**
     * @param $model Nombre del modelo
     * @return una instancia del modelo.
     */
    protected function Model($model){
        /* Find model in all applications. */
        $array_apps = unserialize(APP_INSTALL);
        $find = false;
        foreach ($array_apps as $value){
            $path_file_models_app = str_replace('.', '/', $value) . '/models/'.$model;
            /**/
            if(file_exists($path_file_models_app.'.php')){
                $namespace = str_replace('/','\\', $path_file_models_app);
                
                return new $namespace;

                break;
            }
        }
        if(!$find)
            die('Sorry, there is no model that can be instantiated with the '.$model.' name');


    }
}