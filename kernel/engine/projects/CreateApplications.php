<?php
    namespace fw_Klipso\kernel\engine\projects;

    class CreateApplications{
        public function __construct($name, $base_dir){
            $this->name  = $name;
            $this->base_dir  = $base_dir;
            $this->setCreateDirectories();

        }
        /**
        * Create directories and files initial for the application
        */
        private function setCreateDirectories(){
            /* verify if exist the directory for the applications */
            $folder_app = $this->base_dir . 'apps';
            if(!file_exists($folder_app)){
                mkdir($folder_app, 0755);
            }

            /* create folder for the new application */
            $folder_new_app = $folder_app .'/'. ucwords($this->name);
            mkdir($folder_new_app, 0755);

            /* create folder for the project templates */
            if(!file_exists($this->base_dir . 'templates/' . strtolower($this->name)))
                mkdir($this->base_dir . 'templates/' . strtolower($this->name), 0655);

            /* create folder for the application controllers*/
            mkdir($folder_new_app . '/controllers',0755);

            /* create folder for the application models*/
            mkdir($folder_new_app . '/models',0755);

            $this->setCreateControllers($folder_new_app . '/controllers');
        }

        private function setCreateControllers($folder_new_apps){

            $file_controller = __DIR__ . '/modelProject/controller.txt';
            $folder_new_apps = $folder_new_apps . '/'. ucwords($this->name).'Controller.php';
            $params = array(
                'name_app' => ucwords($this->name)
            );

            /*se almacean las lineas leidas*/
            $line_read = array();

            /*archivo de destino, donde se escribira las lineas del archivo $file_settings*/
            $new_apps = fopen($folder_new_apps, 'w');

            /* se lee el archivo file_settings */
            $reader = fopen($file_controller, 'r');
            while(!feof($reader)) {
                $linea = fgets($reader);
                if(!in_array($linea, $line_read, true)){
                    foreach ($params as $key => $value) {
                        if(preg_match("/$key/", $linea))
                            $linea = str_replace('{%' . $key .'%}', $value, $linea);

                    }
                }
                fwrite($new_apps, $linea);
                $line_read = array_merge($line_read, array($linea));
            }
            fclose($reader);
        }
    }
?>