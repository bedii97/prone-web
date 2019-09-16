<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "autoload.php";

$mail = new PHPMailer(true);
        try{
            //Server Ayarları
            $mail->SMTPDebug = 1;
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "bedii97@gmail.com";
            $mail->Password = "nisa1bedii2.";
            $mail->CharSet = "utf8";
            $mail->SMTPSecure = "ssl";
            $mail->Port = 587;

            //Alıcı ayarları
            $mail->setFrom("bedii97@gmail.com", "Prone");
            $mail->addAddress("bediiusa@gmail.com", "");
            //Gönderi Ayarları
            $mail->isHTML();
            $mail->Subject = "Şifremi Unuttum Maili";
            $mail->Body = "Eğer bu maili siz talep etmediyseniz lütfen dikkate almayınız. Şifre sıfırlama kodunuz: 123321";

            if($mail->send()){
                echo "Oldu";
            }else{
                echo "Olmadı";
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }