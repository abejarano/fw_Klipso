<?php
/**
 * Este aplicativo se encarga de conextarse con un servidor SMTP para el envio de correo electronico
 * Esta basando en la conocida libreria phpmailer.
 */
    namespace fw_Klipso\applications\send_mail;


    use fw_Klipso\applications\send_mail\library\PHPMailer;

    class SendMail{
        private $mailer;

        public function __construct(){
            if(!defined('SERVER_SMTP') || !defined('USER_SMTP') || !defined('PASS_SMTP') || !defined('PORT_SMTP') ||
                !defined('TYPE_SECURE_SMTP')){
                die('To use the application of sending electronic mail must define the following constants in the file 
                settings.php: SERVER_SMTP, PORT_SMTP, TYPE_SECURE_SMTP, USER_SMTP_FROM_NAME, USER_SMTP, PASS_SMTP');
            }

            $this->mailer = new PHPMailer();
            $this->configurePHPMAiler();
        }
        private function configurePHPMAiler(){
            $this->mailer->isSMTP();
            //Enable SMTP debugging
            // 0 = off (for production use)
            // 1 = client messages
            // 2 = client and server messages
            $this->mailer->SMTPDebug = 0;

            $this->mailer->Debugoutput = 'html';

            $this->mailer->Host = SERVER_SMTP;

            $this->mailer->Port = PORT_SMTP;

            $this->mailer->SMTPSecure = TYPE_SECURE_SMTP;

            $this->mailer->SMTPAuth = true;

            $this->mailer->Username = USER_SMTP;

            $this->mailer->Password = PASS_SMTP;

            $this->mailer->From = USER_SMTP;
            $this->mailer->FromName = USER_SMTP_FROM_NAME;

            if(defined('REPLY_TO'))
                $this->mailer->addReplyTo();
        }
        public function send(Array $Address, $subject, $message, Array $cc = array()){

            foreach ($Address as $key => $value){
                if(!is_numeric($key))
                    $this->mailer->addAddress(trim($key), trim($value));
                else
                    $this->mailer->addAddress(trim($value));

                $this->mailer->Subject = utf8_decode($subject);
                $this->mailer->msgHTML(utf8_decode($message));

                if (!$this->mailer->send()) {
                    echo "Mailer Error: " . $this->mailer->ErrorInfo;
                    return false;
                } else {
                    $this->mailer->clearAllRecipients();
                    $this->mailer->clearAddresses();
                    return true;
                }
            }


        }
        public function clear(){
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAddresses();
        }
    }