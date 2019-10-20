<?php
    namespace fw_Klipso\kernel\engine\projects;

    class CreateProject{
        public function __construct($name, $base_dir){
            $this->name  = $name;
            $this->base_dir  = $base_dir;

            $this->setCreateDirectories();

        }

        /**
         * Create directories and files initial for project
         */
        private function setCreateDirectories(){
            $name_project = $this->base_dir . ucwords($this->name) . '_Project';
            $file_htaccess = __DIR__ . '/modelProject/htaccess.txt';
            $file_ignore = __DIR__ . '/modelProject/gitignore.txt';

            /* create folder for project */
            if(!mkdir($name_project, 0755)){
                exit('Could not create project directory');
            }

            /* create htaccess file for project */
            copy($file_htaccess, $this->base_dir . '.htaccess');

            copy($file_ignore, $this->base_dir . '.gitignore');

            /* create folder static file for project */
            if(!mkdir($this->base_dir . 'static', 0755)){
                exit('Could not create directory for static fiel');
            }

            /* create folder static file js for project */
            if(!mkdir($this->base_dir . 'static/js', 0755)){
                exit('Could not create directory for static fiel js');
            }
            /* create folder static file css for project */
            if(!mkdir($this->base_dir . 'static/css', 0755)){
                exit('Could not create directory for static fiel css');
            }
            /* create folder static file images for project */
            if(!mkdir($this->base_dir . 'static/images', 0755)){
                exit('Could not create directory for static fiel images');
            }

            /* create folder template or project */
            if(!mkdir($this->base_dir . 'Views', 0655)){
                exit('Could not create directory for template');
            }

            /* create example file settings for project */
            #copy($file_settings, $name_project . '/settings.php');
            $this->setCreateFileSettingProject($name_project);

            /* create example file urls for project */
            $this->setCreateUrlFiel($name_project . '/urls.php');

            $this->setCreateFileIndexProject();


        }
        private function getGenerateSecretKey(){
            $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890"; 
            $numerodeletras=10; 
            $semilla = "ZDQ3ZmRmZTM1MjIxODk0MWUxNDRlMGQ4YmMzZTBlZjI=";
            $cadena = ""; //variable para almacenar la cadena generada
            for($i=0;$i<$numerodeletras;$i++){
                $cadena .= substr($caracteres,rand(0,strlen($caracteres)),1); /*Extraemos 1 caracter de los caracteres
            entre el rango 0 a Numero de letras que tiene la cadena */
            }
            return hash('sha256',md5(sha1($cadena). sha1($semilla)));
            
        }
        private function setCreateFileSettingProject($dir){            
            $file_settings = __DIR__ . '/modelProject/settings.txt';
            $file_settings_project = $this->base_dir . ucwords($this->name) . '_Project/' . 'settings.php';
            $params = array(
                'name_project' => ucwords($this->name) . '_Project',
                'secret_key' => $this->getGenerateSecretKey(),
            );

            /*se almacean las lineas leidas*/
            $line_read = array();

            /*archivo de destino, donde se escribira las lineas del archivo $file_settings*/
            $settings_project = fopen($file_settings_project, 'w');

            /* se lee el archivo file_settings */
            $reader = fopen($file_settings, 'r');
            while(!feof($reader)) {
                $linea = fgets($reader);
                if(!in_array($linea, $line_read, true)){
                    foreach ($params as $key => $value) {
                        if(preg_match("/$key/", $linea))
                            $linea = str_replace('{%' . $key .'%}', $value, $linea);

                    }
                }
                fwrite($settings_project, $linea);
                $line_read = array_merge($line_read, array($linea));
            }
            fclose($reader);
            
        }
        private function setCreateFileIndexProject(){
            $file_index = __DIR__ . '/modelProject/index.txt';
            $file_index_project = $this->base_dir . 'index.php';
            $params = array(
                'name_project' => ucwords($this->name) . '_Project'
            );

            /*se almacean las lineas leidas*/
            $line_read = array();

            /*archivo de destino, donde se escribira las lineas del archivo $file_settings*/
            $index_project = fopen($file_index_project, 'w');

            /* se lee el archivo file_settings */
            $reader = fopen($file_index, 'r');
            while(!feof($reader)) {
                $linea = fgets($reader);
                if(!in_array($linea, $line_read, true)){
                    foreach ($params as $key => $value) {
                        if(preg_match("/$key/", $linea))
                            $linea = str_replace('{%' . $key .'%}', $value, $linea);

                    }
                }
                fwrite($index_project, $linea);
                $line_read = array_merge($line_read, array($linea));
            }
            fclose($reader);
        }
        private function setCreateUrlFiel($url_file){
            $file_url_project = $url_file;
            $file_url = __DIR__ . '/modelProject/urls.txt';
            $reader = fopen($file_url, 'r');
            $url_example = fread($reader, filesize($file_url));
            fclose($reader);

            $params = array(
                'project_name' => $this->name,
            );
            /*se almacean las lineas leidas*/
            $line_read = array();

            /*archivo de destino, donde se escribira las lineas del archivo $file_settings*/
            $url_project = fopen($file_url_project, 'w');

            /* se lee el archivo file_settings */
            $reader = fopen($file_url, 'r');
            while(!feof($reader)) {
                $linea = fgets($reader);
                if(!in_array($linea, $line_read, true)){
                    foreach ($params as $key => $value) {
                        if(preg_match("/$key/", $linea))
                            $linea = str_replace('{%' . $key .'%}', $value, $linea);

                    }
                }
                fwrite($url_project, $linea);
                $line_read = array_merge($line_read, array($linea));
            }
            fclose($reader);
        }
    }