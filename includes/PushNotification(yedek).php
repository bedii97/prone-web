<?php

function send_notification($tokens, $message){
    $url = 'https://fcm.googleapis.com/fcm/send';
    $fields = array(
        //'registration_ids' => $tokens,
        'to' => "f64IXHjniDw:APA91bEaTGiMvW51voyF1aQLjWiJOCGtYIEqD_ih755MdX_B2JEcFzRsC_5ys2zawcpDQTXiYHwpwX3rirFV0xWcHBQT-beYRIeyUtW8p5osTceiQp2_0TWaOw0akEkXZYKodUlga9Us",
        'data' => $message,
    );
    $headers = array(
        'Authorization:key = AAAAPmLx8ak:APA91bE8W3AUVuU0GZ5cWg9ykLDOEgUj33kPEri3TtuxNZqcDqv7c6zytKnGiXQq9gMq-f7RPL8gnHX4OsOTqBCktkc2U5ut8GZgBVfPRJxCOzTMtHS4V0xCMNFc03DDIJ7rHnNHJZcC',
        'Content-Type: application/json'
    );

    $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
}

$message = array(
    "type" => "follow",
    "sender" => "bedii97"
);
$tokens = "asd";
$message_status = send_notification($tokens, $message);
echo $message_status;
