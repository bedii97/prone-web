<?php
class PushNotification
{
    private $db, $conn;

    private $notificationType;
    private $currentUsername;
    private $targetUsername;

    function __construct(){
        require_once dirname(__FILE__) . '/DbConnect.php';
        require_once dirname(__FILE__) . '/DbOperations.php';

        // nesneler
        $this -> notificationType = $notificationType;
        $this -> currentUsername = $currentUsername;
        $this -> targetUsername = $targetUsername;

        $this-> db = new DbConnect;
        $this -> conn = $this -> db -> connect();
    }

    function send_notification($tokens, $message){
        $fields = null;
    
        if(count($tokens) > 1){
            $fields = array(
                'registration_ids' => $tokens,
                'data' => $message,
            );
        }else{
            $fields = array(
                'to' => $tokens[0],
                'data' => $message,
            );
        }
        $url = 'https://fcm.googleapis.com/fcm/send';
        

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

    function follow($from, $to){

        $this -> notificationType = "follow";
        $this -> currentUsername = $from;
        $this -> targetUsername = $to;
        $this->prepareNotification();
    }
    
    function like(){

        $this -> notificationType = "like";
    }

    function prepareNotification(){

        $db = new DbOperations;
        $targetUserId = $db->getUserIDByUserName($this -> targetUsername);
        $stmt = $this->conn->prepare("SELECT UserToken FROM user_token WHERE UserId = ?");
        $stmt->execute(array($targetUserId));
        $tokens = array();
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $tokens[] = $row->UserToken;
        }
        $message = array(
            "type" => $this -> notificationType,
            "sender" => $this -> currentUsername
        );
        $message_status = $this->send_notification($tokens, $message);
        return $message_status;
    }


    function killAllObjects(){
        $this -> notificationType = null; // null = 0, 'bedii' = 40 byte * 100 kullanıcı = 40000 byte 
        $this -> currentUsername = null;
        $this -> targetUsername = null;
    }

    function __destruct(){

    }
}
