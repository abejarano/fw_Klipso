<?php
/**
 * Busca los modelos, los instancia y obtiene todos los atributos: campos, claves primarias, clave foraneas
 * User: abejarano
 * Date: 22/03/17
 * Time: 10:39 AM
 */

namespace fw_Klipso\kernel\engine\dataBase\Func;


class FindModel
{
    private $dir_apps = array();

    public function __construct($model)
    {
        $this->soughtModel = $model . '.php';
        $this->searchApps();

        $this->obj_model = $this->findModel();
    }

    /**
     * Busca las app instaladas segun el archivo settings.php y las coloca en el atributo $name_apps
     */
    private function searchApps(){
        $apps_install = unserialize(APP_INSTALL);
        foreach ($apps_install as $value){
            $app = str_replace('.', '/', $value);
            $this->dir_apps[] = BASE_DIR . $app . '/models';
        }
    }

    /**
     * Recore el directorio de models de cada apps instalada en busca del modelo que se desea instancia
     */
    private function findModel(){
        foreach ($this->dir_apps as $app){
            $dh = opendir($app);
            while(($file = readdir($dh)) !== false){
                if(is_file($app .'/'. $file)){
                    if ($file == $this->soughtModel){
                        $instance = str_replace('/','\\', str_replace(BASE_DIR,'', $this->soughtModel));
                        return $app .'/'. str_replace('.php','',$this->soughtModel);
                    }
                }
            }

        }

        throw new \Exception('The model '.str_replace('.php','',$this->soughtModel).' is not found in any of the installed applications');


    }

    public function instanceModelFound(){
        $instance = str_replace('/','\\', str_replace(BASE_DIR,'', $this->obj_model));
        return new $instance;
    }


}