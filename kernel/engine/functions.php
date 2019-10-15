<?php
use fw_Klipso\kernel\engine\middleware\Request;
use fw_Klipso\kernel\engine\middleware\Session;
use Slug\Slugifier;
/** Para rediccionar a una url
 * @param $url
 */
 function redirect($url, $message = ""){
     $redirec = trim(DOMAIN_NAME,'/') .'/'. trim($url,'/');
     if(empty($message))
         header('Location: ' . $url);
     else
        header('Location: ' . $url . '?message='.$message);

 }

function slug($string){
    $slug = new Slugifier();
    return  $slug->slugify($string);
}
 function getPrefixModel($model){
     $array_apps = unserialize(APP_INSTALL);

     $find = false;
     foreach ($array_apps as $value){
         $path_file_models_app =  str_replace('.', '/', $value) . '/models/'.$model;

         /**/
         if(file_exists(BASE_DIR . $path_file_models_app.'.php')){
             $namespace = str_replace('/','\\', $path_file_models_app);
             $instanceModel = new $namespace(true);
             $find = true;
             return $instanceModel->__getPrefix();
         }
     }
     if(!$find)
         die('Sorry, there is no model that can be instantiated with the '.$model.' name');
 }
function response_json(Array $response, $code = 200){
    switch ($code) {
        case 100: $text = 'Continue'; break;
        case 101: $text = 'Switching Protocols'; break;
        case 200: $text = 'OK'; break;
        case 201: $text = 'Created'; break;
        case 202: $text = 'Accepted'; break;
        case 203: $text = 'Non-Authoritative Information'; break;
        case 204: $text = 'No Content'; break;
        case 205: $text = 'Reset Content'; break;
        case 206: $text = 'Partial Content'; break;
        case 300: $text = 'Multiple Choices'; break;
        case 301: $text = 'Moved Permanently'; break;
        case 302: $text = 'Moved Temporarily'; break;
        case 303: $text = 'See Other'; break;
        case 304: $text = 'Not Modified'; break;
        case 305: $text = 'Use Proxy'; break;
        case 400: $text = 'Bad Request'; break;
        case 401: $text = 'Unauthorized'; break;
        case 402: $text = 'Payment Required'; break;
        case 403: $text = 'Forbidden'; break;
        case 404: $text = 'Not Found'; break;
        case 405: $text = 'Method Not Allowed'; break;
        case 406: $text = 'Not Acceptable'; break;
        case 407: $text = 'Proxy Authentication Required'; break;
        case 408: $text = 'Request Time-out'; break;
        case 409: $text = 'Conflict'; break;
        case 410: $text = 'Gone'; break;
        case 411: $text = 'Length Required'; break;
        case 412: $text = 'Precondition Failed'; break;
        case 413: $text = 'Request Entity Too Large'; break;
        case 414: $text = 'Request-URI Too Large'; break;
        case 415: $text = 'Unsupported Media Type'; break;
        case 500: $text = 'Internal Server Error'; break;
        case 501: $text = 'Not Implemented'; break;
        case 502: $text = 'Bad Gateway'; break;
        case 503: $text = 'Service Unavailable'; break;
        case 504: $text = 'Gateway Time-out'; break;
        case 505: $text = 'HTTP Version not supported'; break;
        default:
            exit('Unknown http status code "' . htmlentities($code) . '"');
        break;
    }

    $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

    header($protocol . ' ' . $code . ' ' . $text);
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode($response);
}
function response($response){
    header('Content-Type: text/html');
    echo $response;
}

/**
 * @return string el formato para que el manejador de base de datos tenga la fecha, hora, minuto y segundo de defecto
 * del serivdor.
 */
function DefaultDateTimeNow(){
    if(defined('DATABASE')){
        $database = unserialize(DATABASE);
        $sgdb = $database['ENGINE'];
    }
    switch ($sgdb){
        case 'pgsql':
            return ' now() ';
            break;
        case 'mysql':
            return " current_timestamp ";
            break;
        case 'sqlite':
            //$campo .= ' AUTO INCREMENT';
            break;
        default:
            die('Error, database engine is not defined');
            break;
    }


}
function setChangeLanguage($lang){

    define('LANGUAGE', $lang);
    $_SESSION['LANGUAGE'] = $lang;
}
function setLanguage(){

    if(isset($_SESSION['LANGUAGE'])){
        define('LANGUAGE', $_SESSION['LANGUAGE'],true);
        return;
    }
    if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
        define('LANGUAGE', DEFAULT_LANGUAGE,true);
        return;
    }
    
    $set_language = false;
    if (defined('LANGUAGES')) {
        $array_language = unserialize(LANGUAGES);
        $languages = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
        
        
        foreach ($languages as $key => $value) {    
            $lang = explode('-', $value);
            if(array_key_exists($lang[0], $array_language)){
                define('LANGUAGE', $lang[0],true);
                $set_language = true;
            }        
                
        }
    }
    
    if(!$set_language)
        define('LANGUAGE', 'es', true); 

    #print_r($array_language);
}

function URL(){
    return '/' . Request::$current_url;
}