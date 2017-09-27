<?php
    namespace fw_Klipso\applications\images;
    /**
     * A class for the manipulation of images. Allows you to upload images to the server and resize
     * Class UploadFile
     * @package fw_klipso\applications\images
     */

    class UploadFile{
        private $cluster;
        private $type_images = array('image/jpeg','image/jpg', 'image/png', 'image/gif');
        private $cluster_name = '';
        private $type_file = array(
                'application/pdf',
                'application/x-bzpdf',
                'application/x-gzpdf',
                'application/msword',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            );

        public function __construct(){
            /* valida si la extension GD esta instalada */
            if(!gd_info())
                die('Please install php-gd');

            if(!defined('CLUSTER_UPLOAD_FILES'))
                die('Please set the constant CLUSTER_UPLOAD in the settings.php file');

           /* $this->cluster = BASE_DIR . CLUSTER_UPLOAD;

            if(!file_exists($this->cluster))
                mkdir($this->cluster, 0755);*/
            $this->cluster_name = CLUSTER_UPLOAD_FILES . date('m-Y') . '/';
            $this->cluster = BASE_DIR . $this->cluster_name;

            if(!file_exists($this->cluster)){
                mkdir($this->cluster, 0755, true);
            }

            /* crear cluster por meses del año */

        }
        private function validateTypeImage($type){
            if(!in_array($type, $this->type_images))
                return false;
            else
                return true;
        }
        private function validateTypeFile($type){
            if(!in_array($type, $this->type_file))
                return false;
            else
                return true;
        }
        private function getImageCreate($type, $file){
            switch ($type){
                case 'image/jpeg':
                    return imagecreatefromjpeg($file);
                case 'image/jpg':
                    return imagecreatefromjpeg($file);
                case 'image/png':
                    return imagecreatefrompng($file);
                case 'image/gif':
                    return imagecreatefromgif($file);
            }
        }
        public function setUpload(Array $file, $encryp_name = true){
            $this->setUploadImage($file, $encryp_name);
        }
        public function setUploadFile(Array $file, $encryp_name = true){
            if(!$this->validateTypeFile($file['type']))
                throw new \Exception('File not support');


            /* captura el nombre y verifica si lo desean cifrado */
            $name_file = $file['name'];            
            if($encryp_name){
                /* extraer extension  */
                $ext = explode('.', $name_file);
                $ext = $ext[count($ext) - 1]; 
                $name = md5($name_file . date('Y-m-d H:i:s')) . '.'.$ext;
            }
            else
                $name = str_replace(' ','-', $name_file);


            move_uploaded_file($file['tmp_name'], $this->cluster. $name);

            return '/'.$this->cluster_name . $name; 
        }
        public function setUploadImage(Array $file, $encryp_name = true){
            /* validar si el archivo es permitido por la aplicacion */

            if(!$this->validateTypeImage($file['type']))
                return $this->setUploadFile($file, $encryp_name);

            /* captura el nombre y verifica si lo desean cifrado */
            $name_file = $file['name'];
            if($encryp_name)
                $name_file = md5($name_file . date('Y-m-d H:i:s'));
            else
                $name_file = str_replace(' ','-', $name_file);

            /* obtiene la informacion de la imagen */
            $data_image = getimagesize($file['tmp_name']);
            $width = $data_image[0];
            $height = $data_image[1];

            /* crea una nueva imagen con las medidas de la imagen que se esta subien al servidor */
            $imagen_p = imagecreatetruecolor($width, $height);
            $imagen = $this->getImageCreate($file['type'], $file['tmp_name']);

            imagecopyresampled($imagen_p, $imagen, 0, 0, 0, 0, $width, $height, $width, $height);
            imagepng($imagen_p, $this->cluster. $name_file . '.png');

            imagedestroy($imagen_p);
            imagedestroy($imagen);

            $this->resize($name_file . '.png', $name_file, 920);
            $this->resize($name_file . '.png', $name_file. '_sm', 200);
            $this->resize($name_file . '.png', $name_file. '_md', 480);

            return '/' . $this->cluster_name . $name_file. '.png';

        }
        private function resize($image, $new_name, $width){
            $image = $this->cluster . $image;

            /* obtiene la informacion de la imagen */
            $data_image = getimagesize( $image);

            $wpercent = floatval($width / $data_image[0]);

            $hsize = intval($data_image[1] * $wpercent);

            $imagen_p = imagecreatetruecolor($width, $hsize);
            $imagen = imagecreatefrompng($image);

            imagecopyresampled($imagen_p, $imagen, 0, 0, 0, 0, $width, $hsize, $data_image[0], $data_image[1]);
            imagepng($imagen_p, $this->cluster. $new_name . '.png');

            imagedestroy($imagen_p);
            imagedestroy($imagen);
        }

        /**
         *Defines the type of file that will allow uploading to the server
         * @param array $mime_type_images
         */
        public function setMIMETypeFile(Array $mime_type_images){
            $this->type_file = $mime_type_images;
        }

        /**
         *Defines the type of file images that will allow uploading to the server
         * @param array $mime_type_images
         */
        public function setMIMETypeImage(Array $mime_type_images){
            $this->type_images = $mime_type_images;
        }

        public function setDeleteImage($image){
            $image = explode('.', $image);
            $md = $image[0] . '_md.png';
            $sm = $image[0] . '_sm.png';
            $img = $image[0] . '.png';

            if(!unlink($this->cluster . '/'. $md))
                throw new \Exception('error delete file image');
            if(!unlink($this->cluster . '/'. $sm))
                throw new \Exception('error delete file image');
            if(!unlink($this->cluster . '/'. $img))
                throw new \Exception('error delete file image');

            return true;
        }
    }