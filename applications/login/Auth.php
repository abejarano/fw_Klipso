<?php
namespace fw_Klipso\applications\login;
use fw_Klipso\kernel\engine\middleware\Response;
use fw_Klipso\kernel\engine\middleware\Session;


class Auth extends Session {
    private $model = '';

    /**
     * Auth constructor. Espera como parametro el nombre del campo login usado en el modelo, y el nombre del campo pass
     * usado en el modelo.
     * @param $field_name_user
     * @param $fiel_name_pass
     */
    public function __construct($field_name_login = '', $fiel_name_pass = '')
    {
        $prefix = getPrefixModel(USER_MODEL);

        if(!empty($prefix))
            $this->model = $prefix .'_'. strtolower(USER_MODEL);
        else
            $this->model = strtolower(USER_MODEL);

        if(!empty($field_name_login))
            $this->field_name_login = $field_name_login;
        else
            $this->field_name_login = 'user_login';

        if(!empty($fiel_name_pass))
            $this->fiel_name_pass = $fiel_name_pass;
        else
            $this->fiel_name_pass = 'user_pass';


    }

    public function makeLogout($redirect = ''){
        $id_session = $_SESSION['sessionid'];
        $this->destroy();

        $sql = "delete from fw_klipso_session where session_id = '".$id_session."' ";
        $this->raw($sql);

        if(empty($redirect )){
            $redirec = trim(DOMAIN_NAME,'/') .'/'. trim(LOGIN_URL,'/');
            header("Location: $redirec ");    
        }else{
            header("Location: $redirect ");   
        }
        

    }
    public function makeLogin($user, $pass, $ajax = false)
    {
        
        $redirec = trim(DOMAIN_NAME,'/') .'/'. trim(LOGIN_URL,'/');
        $sql = "select * from  ". $this->model . " where lower(" . $this->field_name_login . ") = ?";

        $rs = $this->raw($sql,[$user]);

        $rs = stdClassToArray($rs);

        if(empty($rs)){
            if (!$ajax)
                redirect($redirec, 'The user is not registered');
            else
                return false;
        

        } if($rs[$this->fiel_name_pass] != Auth::getEncryptPass($pass)){

            if (!$ajax) {
                redirect($redirec, 'Invalid password');
                return false;
            } else
                return false;
        }

        /* se quita el camo pass de los datos que se van a registrar en la session */
        unset($rs[$this->fiel_name_pass]);
        return $this->registerSession($rs);
    }

    /**
     * @param $pass
     * @return string
     */
    public static function getEncryptPass($pass){
        $semilla = "ZDQ3ZmRmZTM1MjIxODk0MWUxNDRlMGQ4YmMzZTBlZjI=";
        return hash('sha256',md5(sha1($pass). sha1($semilla)));
    }
}