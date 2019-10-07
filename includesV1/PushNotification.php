<?php

class PushNotification{
    private $con;

    function __construct(){
        require_once dirname(__FILE__) . '/DbConnect.php';
        require_once dirname(__FILE__) . '/DbOperations.php';

        $db = new DbConnect;

        $this->con = $db->connect();
    }

    function sendNotification($tokens, $message){
        $url = 'https://fcm.googleapis.com/fcm/send';
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
        $headers = array(
            'Authorization:key = AAAAPmLx8ak:APA91bFEdbWbQWieUJ9MYQR1Tvn27hGcyQbzA1B36pmARfOXNFnktlF48-G3DbmnwpDeRGqczm6cxCZ_WvtvMpFP4Rs5Ao5in0E_0jXtXjvyPCIf7YUE-T8J96ZgTatSrJv8xabSiUn0',
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

    public function getTargetUserTokens($to){
        $db = new DbOperations;
        $targetUserId = $db->getUserIDByUserName($to);
        $stmt = $this->con->prepare("SELECT UserToken FROM user_token WHERE UserId = ?");
        $stmt->execute(array($targetUserId));
        $tokens = array();
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $tokens[] = $row->UserToken;
        }
        return $tokens;
    }

    public function commentReplyNotification($from, $to, $commentId){
        $db = new DbOperations;
        $fromID = $db->getUserIDByUserName($from);
        $toID = $db->getUserIDByUserName($to);
        $type = "commentReply";
        $tokens = $this->getTargetUserTokens($to);
        $message = array(
            "type" => $type,
            "sender" => $from,
            "commentId" => $commentId
        );
        $lastDate = date_create($this->commentRepyLastDate($fromID, $toID, $commentId));
        $nowDate = date_create(date('Y-m-d H:i:s'));
        $addTime = '1 days';
        date_add($lastDate, date_interval_create_from_date_string($addTime));
        //2019-09-17 13:03:32 gelen değer
        //2019-09-18 13:03:32 +1 değer
        //2019-09-17 13:03:32 şuan

        if($lastDate < $nowDate){ //Küçükse notification işlemini başlat
            $stmtNotification = $this->con->prepare("INSERT INTO `notification`(`NotificationType`, `NotificationSenderId`, `UserId`) VALUES (?, ?, ?)");
            $result = $stmtNotification->execute(array($type, $fromID, $toID));
            if($result){
                $notificationID = $this->con->lastInsertId(); 
                $stmtPostlike = $this->con->prepare("INSERT INTO `notification_commentreply`(`CommentId`, `NotificationId`) VALUES (?, ?)");
                $stmtPostlike->execute(array($commentId, $notificationID));
            }
        }

        $message_status = $this->sendNotification($tokens, $message);
        $response = array(
            "stmt" => $result,
            "push" => $message_status
        );
        return $response;
    }

    public function commentRepyLastDate($fromID, $toID, $commentId){
        $sql = "SELECT NotificationDate FROM notification_commentreply_view WHERE NotificationSenderId = ? AND UserId = ? AND CommentId = ? ORDER BY NotificationDate DESC LIMIT 1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($fromID, $toID, $commentId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        $date = @$row->NotificationDate;
        if($date == null){
            return date_create("2000-01-01 00:00:00");
        }else{
            return $date;
        }
    }

    public function voteNotification($from, $to, $postId){ //$from - UserName -- $to - UserName
        $db = new DbOperations;
        $fromID = $db->getUserIDByUserName($from);
        $toID = $db->getUserIDByUserName($to);
        $type = "postVote";
        $tokens = $this->getTargetUserTokens($to);
        $message = array(
            "type" => $type,
            "sender" => $from,
            "postId" => $postId
        );
        $lastDate = date_create($this->postLikeLastDate($fromID, $toID, $postId));
        $nowDate = date_create(date('Y-m-d H:i:s'));
        $addTime = '1 days';
        date_add($lastDate, date_interval_create_from_date_string($addTime));
        //2019-09-17 13:03:32 gelen değer
        //2019-09-18 13:03:32 +1 değer
        //2019-09-17 13:03:32 şuan

        if($lastDate < $nowDate){ //Küçükse notification işlemini başlat
            $stmtNotification = $this->con->prepare("INSERT INTO `notification`(`NotificationType`, `NotificationSenderId`, `UserId`) VALUES (?, ?, ?)");
            $result = $stmtNotification->execute(array($type, $fromID, $toID));
            if($result){
                $notificationID = $this->con->lastInsertId(); 
                $stmtVote = $this->con->prepare("INSERT INTO `notification_vote`(`PostId`, `NotificationId`) VALUES (?, ?)");
                $stmtVote->execute(array($postId, $notificationID));
            }
        }
        $message_status = $this->sendNotification($tokens, $message);
        $response = array(
            "stmt" => $result,
            "push" => $message_status
        );
        return $response;
    }

    public function followNotification($from, $to){ //$from - UserName -- $to - UserName
        $db = new DbOperations;
        $fromID = $db->getUserIDByUserName($from);
        $toID = $db->getUserIDByUserName($to);
        $type = "follow";
        $tokens = $this->getTargetUserTokens($to);
        $message = array(
            "type" => $type,
            "sender" => $from
        );
        $lastDate = date_create($this->followLastDate($fromID, $toID));
        $nowDate = date_create(date('Y-m-d H:i:s'));
        $addTime = '1 days';
        date_add($lastDate, date_interval_create_from_date_string($addTime));
        if($lastDate < $nowDate){
            $stmtNotification = $this->con->prepare("INSERT INTO `notification`(`NotificationType`, `NotificationSenderId`, `UserId`) VALUES (?, ?, ?)");
            $result = $stmtNotification->execute(array($type, $fromID, $toID));
        }
        $message_status = $this->sendNotification($tokens, $message);
        $response = array(
            "stmt" => $result,
            "push" => $message_status
        );
        return $response;
    }

    public function postCommentNotification($from, $to, $postId){
        $db = new DbOperations;
        $fromID = $db->getUserIDByUserName($from);
        $toID = $db->getUserIDByUserName($to);
        $type = "postComment";
        $tokens = $this->getTargetUserTokens($to);
        $message = array(
            "type" => $type,
            "sender" => $from,
            "postId" => $postId
        );
        $lastDate = date_create($this->postCommentLastDate($fromID, $toID, $postId));
        $nowDate = date_create(date('Y-m-d H:i:s'));
        $addTime = '1 days';
        date_add($lastDate, date_interval_create_from_date_string($addTime));
        //2019-09-17 13:03:32 gelen değer
        //2019-09-18 13:03:32 +1 değer
        //2019-09-17 13:03:32 şuan

        if($lastDate < $nowDate){ //Küçükse notification işlemini başlat
            $stmtNotification = $this->con->prepare("INSERT INTO `notification`(`NotificationType`, `NotificationSenderId`, `UserId`) VALUES (?, ?, ?)");
            $result = $stmtNotification->execute(array($type, $fromID, $toID));
            if($result){
                $notificationID = $this->con->lastInsertId(); 
                $stmtPostlike = $this->con->prepare("INSERT INTO `notification_postcomment`(`PostId`, `NotificationId`) VALUES (?, ?)");
                $stmtPostlike->execute(array($postId, $notificationID));
            }
        }

        $message_status = $this->sendNotification($tokens, $message);
        $response = array(
            "stmt" => $result,
            "push" => $message_status
        );
        return $response;
    }

    public function postLikeNotification($from, $to, $postId){
        $db = new DbOperations;
        $fromID = $db->getUserIDByUserName($from);
        $toID = $db->getUserIDByUserName($to);
        $type = "postLike";
        $tokens = $this->getTargetUserTokens($to);
        $message = array(
            "type" => $type,
            "sender" => $from,
            "postId" => $postId
        );
        $lastDate = date_create($this->postLikeLastDate($fromID, $toID, $postId));
        $nowDate = date_create(date('Y-m-d H:i:s'));
        $addTime = '1 days';
        date_add($lastDate, date_interval_create_from_date_string($addTime));
        //2019-09-17 13:03:32 gelen değer
        //2019-09-18 13:03:32 +1 değer
        //2019-09-17 13:03:32 şuan

        if($lastDate < $nowDate){ //Küçükse notification işlemini başlat
            $stmtNotification = $this->con->prepare("INSERT INTO `notification`(`NotificationType`, `NotificationSenderId`, `UserId`) VALUES (?, ?, ?)");
            $result = $stmtNotification->execute(array($type, $fromID, $toID));
            if($result){
                $notificationID = $this->con->lastInsertId(); 
                $stmtPostlike = $this->con->prepare("INSERT INTO `notification_postlike`(`PostId`, `NotificationId`) VALUES (?, ?)");
                $stmtPostlike->execute(array($postId, $notificationID));
            }
        }
        $message_status = $this->sendNotification($tokens, $message);
        $response = array(
            "stmt" => $result,
            "push" => $message_status
        );
        return $response;
        
    }

    public function postCommentLastDate($fromID, $toID, $postID){
        $sql = "SELECT NotificationDate FROM notification_postcomment_view WHERE NotificationSenderId = ? AND UserId = ? AND PostId = ? ORDER BY NotificationDate DESC LIMIT 1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($fromID, $toID, $postID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        $date = @$row->NotificationDate;
        if($date == null){
            return date_create("2000-01-01 00:00:00");
        }else{
            return $date;
        }
    }

    /**
     * Günde 1 aksiyona 1 notification göndermek için
     * Required $fromID, $toID, $postID
     * return $date
     */
    public function postLikeLastDate($fromID, $toID, $postID){
        $sql = "SELECT NotificationDate FROM notification_vote_view WHERE NotificationSenderId = ? AND UserId = ? AND PostId = ? ORDER BY NotificationDate DESC LIMIT 1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($fromID, $toID, $postID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        $date = @$row->NotificationDate;
        if($date == null){
            return date_create("2000-01-01 00:00:00");
        }else{
            return $date;
        }
    }

    public function followLastDate($fromID, $toID){
        $sql = "SELECT NotificationDate FROM notification_follow_view WHERE NotificationSenderId = ? AND UserId = ? ORDER BY NotificationDate DESC LIMIT 1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($fromID, $toID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        $date = @$row->NotificationDate;
        if($date == null){
            return date_create("2000-01-01 00:00:00"); //Null gitmemesi için random bir date
        }else{
            return $date;
        }
    }
}