<?php

class DbOperations
{                                                               //CTRL+K+2 FOLD(COLLAPSE) METHODS
    private $con;

    function __construct(){
        require_once dirname(__FILE__) . '/DbConnect.php';
        require_once dirname(__FILE__) . '/PushNotification.php';
        $db = new DbConnect;

        $this->con = $db->connect();
    }

    public function getCommentLikeUserList($commentId, $userName){
        $userId = $this->getUserIDByUserName($userName);
        $sql = "SELECT * FROM comment_like_user_view WHERE CommentID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($commentId));
        $likedUsers = array();
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $likedUser = array();
            $likedUser['id'] = $row->UserId;
            $likedUser['name'] = $row->UserName;
            $likedUser['firstname'] = $row->UserFirstName;
            $likedUser['lastname'] = $row->UserLastName;
            $likedUser['isfollow'] = $this->isFollowing($userId, $row->UserId);
            //Image
            $image = @$row->UserImagePath;
            if($image !== null){
                $site_url = 'https://gulfilosu.com';
                $image = $site_url . @$row->UserImagePath;
            }
            $likedUser['image'] = $image;
            array_push($likedUsers, $likedUser);
        }
        return $likedUsers;
    }

    public function getPostLikeUserList($postId, $userName){
        $userId = $this->getUserIDByUserName($userName);
        $sql = "SELECT * FROM post_like_user_view WHERE PostID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postId));
        $likedUsers = array();
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $likedUser = array();
            $likedUser['id'] = $row->UserId;
            $likedUser['name'] = $row->UserName;
            $likedUser['firstname'] = $row->UserFirstName;
            $likedUser['lastname'] = $row->UserLastName;
            $likedUser['isfollow'] = $this->isFollowing($userId, $row->UserId);
            //Image
            $image = @$row->UserImagePath;
            if($image !== null){
                $site_url = 'https://gulfilosu.com';
                $image = $site_url . @$row->UserImagePath;
            }
            $likedUser['image'] = $image;
            array_push($likedUsers, $likedUser);
        }
        return $likedUsers;
    }

    public function replyReport($userName, $replyId, $categoryId, $content){
        $userId = $this->getUserIDByUserName($userName);
        if($this->checkCanUserReportReply($replyId, $userId)){
            if($this->isReplyExist($replyId)){
                $stmt = $this->con->prepare("INSERT INTO comment_reply_report (CommentReplyReportContent, ReplyId, UserId, CommentReplyReportCategoryId) VALUES (?, ?, ?, ?)");
                $stmt->execute(array($content, $replyId, $userId, $categoryId));
                if($stmt){
                    return POST_REPORTED;
                }else{
                    return false;
                }
            }else{
                return COMMENT_NOT_EXIST;
            }
        }else{
            return USER_CANT_REPORT;
        }
    }

    public function commentReport($userName, $commentId, $categoryId, $content){
        $userId = $this->getUserIDByUserName($userName);
        if($this->checkCanUserReportComment($commentId, $userId)){
            if($this->isCommentExist($commentId)){
                $stmt = $this->con->prepare("INSERT INTO comment_report (CommentReportContent, CommentId, UserId, CommentReportCategoryId) VALUES (?, ?, ?, ?)");
                $stmt->execute(array($content, $commentId, $userId, $categoryId));
                if($stmt){
                    return POST_REPORTED;
                }else{
                    return false;
                }
            }else{
                return COMMENT_NOT_EXIST;
            }
        }else{
            return USER_CANT_REPORT;
        }
    }

    public function profileReport($userName, $targetUserId, $categoryId, $content){
        $userId = $this->getUserIDByUserName($userName);
        if($this->checkCanUserReportProfile($targetUserId, $userId)){
            if($this->isUserIdExist($targetUserId)){ //Reportlanacak kullanıcı var mı
                $stmt = $this->con->prepare("INSERT INTO profile_report (ProfileReportContent, TargetUserID, UserId, ProfileReportCategoryId) VALUES (?, ?, ?, ?)");
                $stmt->execute(array($content, $targetUserId, $userId, $categoryId));
                if($stmt){
                    return POST_REPORTED;
                }else{
                    return false;
                }
            }else{
                return USER_NOT_FOUND;
            }
        }else{
            return USER_CANT_REPORT;
        }
    }

    public function replyDelete($replyId, $userName){
        if($this->checkReplyByReplyID($replyId) == COMMENT_EXIST){ //Reply var mı kontrolü
            $userId = $this->getUserIDByUserName($userName);
            if($this->getReplyOwnerUserID($replyId) == $userId){ //Postun sahibi mi kontrolü
                $stmt = $this->con->prepare("UPDATE comment_reply SET CommentReplyStatus = 2 WHERE CommentReplyID = ?"); //1 = Active ; 2 = Passive
                $stmt->execute(array($replyId));
                if($stmt){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function commentDelete($commentId, $userName){
        if($this->checkCommentByCommentID($commentId) == COMMENT_EXIST){ //Post var mı kontrolü
            $userId = $this->getUserIDByUserName($userName);
            if($this->getCommentOwnerUserID($commentId) == $userId){ //Postun sahibi mi kontrolü
                $stmt = $this->con->prepare("UPDATE comment SET CommentStatus = 2 wHERE CommentID = ?"); //1 = Active ; 2 = Passive
                $stmt->execute(array($commentId));
                if($stmt){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function postDelete($postId, $userName){
        if($this->checkPostByPostID($postId) == POST_EXIST){ //Post var mı kontrolü
            $userId = $this->getUserIDByUserName($userName);
            if($this->getPostOwnerUserID($postId) == $userId){ //Postun sahibi mi kontrolü
                $stmt = $this->con->prepare("UPDATE post SET PostStatus = 2 wHERE PostID = ?"); //1 = Active ; 2 = Passive
                $stmt->execute(array($postId));
                if($stmt){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function postReport($userName, $postId, $categoryId, $content){
        $userId = $this->getUserIDByUserName($userName);
        if($this->checkCanUserReportPost($postId, $userId)){
            if($this->checkPostByPostID($postId) == POST_EXIST){
                $stmt = $this->con->prepare("INSERT INTO post_report (PostReportContent, PostId, UserId, PostReportCategoryId) VALUES (?, ?, ?, ?)");
                $stmt->execute(array($content, $postId, $userId, $categoryId));
                if($stmt){
                    return POST_REPORTED;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return USER_CANT_REPORT;
        }
    }

    public function sendMail(){
        //Email gönderme yapılacak
        /*$mail = new PHPMailer(true);
        try{
            //Server Ayarları
            $mail->SMTPDebug = 1;
            $mail->isSMTP();
            $mail->Host = "smtp.yandex.com";
            $mail->SMTPAuth = true;
            $mail->Username = "bedii07@yandex.com";
            $mail->Password = "nisa1bedii2.";
            $mail->CharSet = "utf8";
            $mail->SMTPSecure = "tls";
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
        }*/
    } //Çalışmıyor

    public function forgetPassword($userName){
        $userId = $this->getUserIDByUserName($userName);
        //Send Email
        $this->sendMail();
        //Database Save
        $chars = "111111222222333333444444555555666666777777888888999999000000";
        $lenght = 6;
        $token = substr(str_shuffle($chars), 0, $lenght);
        $sql = "INSERT INTO forget_password (ForgetPasswordToken, UserId) VALUES (?, ?)";
        $stmt = $this->con->prepare($sql);
        $result = $stmt->execute(array($token, $userId));
        if($result){
            return true;
        }else{
            return false;
        }
    }

    public function getFollowing($userName){
        $userId = $this->getUserIDByUserName($userName);
        $sql = "SELECT * FROM follow_view WHERE UserId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($userId));
        $followers = array();
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $follower = array();
            $follower['id'] = $row->FollowID;
            $follower['name'] = $row->UserName;
            $follower['firstname'] = $row->UserFirstName;
            $follower['lastname'] = $row->UserLastName;
            $follower['isfollow'] = true;//$this->isFollowing($userId, $row->FollowID);
            //Image
            $image = @$row->UserImagePath;
            if($image !== null){
                $site_url = 'https://gulfilosu.com';
                $image = $site_url . @$row->UserImagePath;
            }
            $follower['image'] = $image;
            array_push($followers, $follower);
        }
        return $followers;
    }

    public function getFollower($userName){
        $userId = $this->getUserIDByUserName($userName);
        $sql = "SELECT * FROM follower_view WHERE FollowID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($userId));
        $followers = array();
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $follower = array();
            $follower['id'] = $row->UserId;
            $follower['name'] = $row->UserName;
            $follower['firstname'] = $row->UserFirstName;
            $follower['lastname'] = $row->UserLastName;
            $follower['isfollow'] = $this->isFollowing($userId, $row->UserId);
            //Image
            $image = @$row->UserImagePath;
            if($image !== null){
                $site_url = 'https://gulfilosu.com';
                $image = $site_url . @$row->UserImagePath;
            }
            $follower['image'] = $image;
            array_push($followers, $follower);
        }
        return $followers;
    }

    public function getSearch($type, $content){
        if(!empty($content)){
            /** Type Control */
            if($type == 'Post'){ //Post
                /** Refactor content variable for query*/
                $content = "%$content%";
                /** PDO Statement */
                $sql = "SELECT * FROM post_view WHERE PostQuestion like :content OR PostDescription like :content ORDER BY PostQuestion, PostDescription ASC LIMIT 30";
                $responses = array();
                $stmtSearch = $this->con->prepare($sql);
                $stmtSearch->bindValue(':content', $content);
                $stmtSearch->execute();
                while ($rowSearch = $stmtSearch->fetch(PDO::FETCH_OBJ)){
                    $response = array();
                    $response['id'] = $rowSearch->PostID;
                    $response['question'] = $rowSearch->PostQuestion;
                    $response['description'] = $rowSearch->PostDescription;
                    $site_url = 'https://gulfilosu.com';
                    $image = $site_url . @$rowSearch->UserImagePath;
                    $response['image'] = $image;
                    array_push($responses, $response);
                }
            }else if($type == 'User'){
                /** Refactor content variable for query*/
                $content = "%$content%";
                /** PDO Statement */
                $sql = "SELECT * FROM user_view WHERE UserName like :content OR UserfirstName like :content OR UserLastName like :content ORDER BY UserName, UserFirstName, UserLastName ASC LIMIT 30";
                $responses = array();
                $stmtSearch = $this->con->prepare($sql);
                $stmtSearch->bindValue(':content', $content);
                $stmtSearch->execute();
                while ($rowSearch = $stmtSearch->fetch(PDO::FETCH_OBJ)){
                    $response = array();
                    $response['id'] = $rowSearch->UserID;
                    $response['username'] = $rowSearch->UserName;
                    $response['firstname'] = $rowSearch->UserFirstName;
                    $response['surname'] = $rowSearch->UserLastName;
                    //Image
                    $image = @$rowSearch->UserImagePath;
                    if($image !== null){
                        $site_url = 'https://gulfilosu.com';
                        $image = $site_url . @$rowSearch->UserImagePath;
                    }
                    $response['image'] = $image;
                    array_push($responses, $response);
                }
            }else{
                $responses = null;
            }
            return $responses;
        }
    }

     /**
      * CategoryID = 0 -> Get All Posts
      * CategoryID = 5 -> Get Teknoloji Posts
      * CategoryID = 6 -> Get Technology Posts
      */
    
    public function getDiscover($categoryID, $locale){
        if($categoryID == 0){
            $stmtPost = $this->con->prepare("SELECT * FROM post_view ORDER BY RAND()");
            $stmtPost->execute();
        }else{
            $categoryFinalID = $this->getCategoryFinalIDByCategoryID($categoryID);
            $stmtPost = $this->con->prepare("SELECT * FROM post_view WHERE CategoryFinalID = ? ORDER BY RAND()");
            $stmtPost->execute(array($categoryFinalID));
        }
        //$localeCategories = $this->getCategoryIDsByLocale($locale); //İlerde istersen kardeşim herkes kendi diline göre görebilir bu metod sayesinde
        $posts = array();
        $options = array();
        while ($rowPost = $stmtPost->fetch(PDO::FETCH_OBJ)) {
            $post = array();
            $post['id'] = $rowPost->PostID; //ID Çekildi
            $post['question'] = $rowPost->PostQuestion; //Question Çekildi
            //**En çok oylanan option çekildi */
            $options = $this->getMostVotedOptionByPostID($rowPost->PostID);
            $post['option'] = $options;
            //**En çok oylanan option çekildi */
            $post['category'] = $rowPost->CategoryName; //Date Çekildi
            $post['date'] = $rowPost->PostDate; //Date Çekildi
            $post['category'] = $this->getCategoryNameByLocale($locale, $rowPost->CategoryFinalID);
            $post['username'] = $rowPost->UserName;
            $post['userid'] = $rowPost->UserID;
            //Image
            $image = @$rowPost->UserImagePath;
            if($image !== null){
                $site_url = 'https://gulfilosu.com';
                $image = $site_url . @$rowPost->UserImagePath;
            }
            $post['image'] = $image;
            $post['votecount'] = $this->getVoteCount($rowPost->PostID);
            $post['commentcount'] = $this->getCommentCount($rowPost->PostID);
            $post['likecount'] = $this->getPostLikeCount($rowPost->PostID);
            array_push($posts, $post);
        }
        return $posts;
    }

    public function createCategory($name, $image, $locale, $id){
        //CATEGORY NAME EKLEME
        $stmt1 = $this->con->prepare("INSERT INTO category_name (CategoryName, CategoryLocale) VALUES (?, ?)");
        $stmt1->execute(array($name, $locale));
        if ($stmt1) {//CATEGORY IMAGE EKLEME
            $categoryNameID = $this->con->lastInsertId();
            $stmt2 = $this->con->prepare("INSERT INTO category_image (CategoryImage) VALUES (?)");
            $stmt2->execute(array($image));
            if($stmt2){//CATEGORY EKLEME
                $categoryImageID = $this->con->lastInsertId();
                $stmt3 = $this->con->prepare("INSERT INTO category (CategoryNameId, CategoryImageId) VALUES (?, ?)");
                $stmt3->execute(array($categoryNameID, $categoryImageID));
                if($stmt3){//CATEGORY FINAL EKLEME
                    $categoryID = $this->con->lastInsertId();
                    $stmt4 = $this->con->prepare("INSERT INTO category_final (CategoryFinalID, CategoryId) VALUES (?, ?)");
                    $stmt4->execute(array($id, $categoryID));
                    if($stmt4){
                        return true;
                    }
                }
            }
        }
    } //Updated

    public function updateAbout($userName, $userPassword, $about){
        if($this->userLogin($userName, $userPassword) == USER_AUTHENTICATED){
            $stmt = $this->con->prepare("UPDATE user SET UserAbout = ? WHERE UserName = ?");
            if($stmt->execute(array($about, $userName))){
                return ABOUT_CHANGED;
            }else{
                return ABOUT_NOT_CHANGED;
            }
        }
    }

    public function getNotifications($userName){
        $userId = $this->getUserIDByUserName($userName);
        $stmtNotify = $this->con->prepare("SELECT * FROM notification_view WHERE UserId = ? ORDER BY NotificationID DESC");
        $stmtNotify->execute(array($userId));
        $notifies = array();
        while ($rowNotify = $stmtNotify->fetch(PDO::FETCH_OBJ)) {
            $notify = array();
            $notify['id'] = $rowNotify->NotificationID;
            $notify['type'] = $rowNotify->NotificationType;
            $notify['read'] = $rowNotify->NotificationRead;
            $notify['date'] = $rowNotify->NotificationDate;
            $notify['sender'] = $rowNotify->NotificationSenderUserName;
            //Image
            $image = @$rowNotify->NotificationSenderImage;
            if($image !== null){
                $site_url = 'https://gulfilosu.com';
                $image = $site_url . @$rowNotify->NotificationSenderImage;
            }
            $notify['senderImage'] = $image;
            if($notify['type'] == "postLike"){
                $postId = $this->getNotificationPostIdByNotificationID($rowNotify->NotificationID);
                $postTitle = $this->getPostTitleByPostID($postId);
                $notify['postId'] = $postId;
                $notify['question'] = $postTitle;
            }else if($notify['type'] == "postComment"){
                $postId = $this->getNotificationPostIdByNotificationIDForComment($rowNotify->NotificationID);
                $postTitle = $this->getPostTitleByPostID($postId);
                $notify['postId'] = $postId;
                $notify['question'] = $postTitle;
            }
            array_push($notifies, $notify);
        }
        return $notifies;
    } //updated

    public function sendToken(){
        $notify = new PushNotification;
        $result = $notify->personelNotification().setNotificationType().follow("bedii97", "Meldor");
        /*$result = $notify->personelNotification($title, $body);
        echo $result;*/
    } //Updated

    public function userToken2($title, $body){
        $notify = new PushNotification;
        $result = $notify->prepareNotification($title, $body);
        echo $result;
    } //Updated

    public function deleteUserToken($userName, $userToken){
        $userId = $this->getUserIDByUserName($userName);
        $stmt = $this->con->prepare("DELETE FROM user_token WHERE UserId = ? AND UserToken = ?");
        $stmt->execute(array($userId, $userToken));
        if ($stmt) {
            return true;
        } else {
            return false;
        }
    } //Updated

    public function userToken($token){
        $stmt = $this->con->prepare("INSERT INTO user_token (UserToken) VALUES (?)");
        $stmt->execute(array($token));
        if ($stmt) {
            return true;
        } else {
            return false;
        }
    } //Updated

    public function reply($userName, $userPassword, $reply, $commentID){
        $checkComment = $this->checkCommentByCommentID($commentID);
        if ($checkComment == COMMENT_EXIST) {
            if ($this->userLogin($userName, $userPassword) == USER_AUTHENTICATED) {
                $userID = $this->getUserIDByUserName($userName);
                $stmt = $this->con->prepare("INSERT INTO comment_reply (UserId, CommentId, CommentReplyContent) VALUES (?, ?, ?)"); //3 Parametre
                $stmt->execute(array($userID, $commentID, $reply));
                if ($stmt) {
                    return COMMENT_SUCCESS;
                } else {
                    return COMMENT_UNSUCCESS;
                }
            }
            return USER_FAILURE;
        } else if ($checkComment == POST_NOT_EXIST) {
            return POST_NOT_EXIST;
        } else {
            return WTF;
        }
    } //Updated

    public function replyUnLike($userName, $replyId){
        if($this->checkReplyByReplyID($replyId) == COMMENT_EXIST){
            $userId = $this->getUserIDByUserName($userName);
            if($this->isUserLikedReply($userId, $replyId)){
                $stmt = $this->con->prepare("DELETE FROM comment_reply_like WHERE CommentReplyId = ? AND UserId = ?");
                $stmt->execute(array($replyId, $userId));
                if($stmt){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function replyLike($userName, $replyId){
        if($this->checkReplyByReplyID($replyId) == COMMENT_EXIST){
            $userId = $this->getUserIDByUserName($userName);
            if(!$this->isUserLikedReply($userId, $replyId)){
                $stmt = $this->con->prepare("INSERT INTO comment_reply_like (CommentReplyId, UserId) VALUES (?, ?)");
                $stmt->execute(array($replyId, $userId));
                if($stmt){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function getReplies($commentId, $userName){
        $stmt = $this->con->prepare("SELECT * FROM reply_view WHERE CommentID = ? ORDER BY CommentReplyDate ASC");
        $stmt->execute(array($commentId));
        $replies = array();
        $userId = $this->getUserIDByUserName($userName);
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) { //Bir satırın tüm kolonları için fetchAll() yeterli
            $reply = array();
            $reply['id'] = $row->CommentReplyID;
            $reply['commentId'] = $row->CommentID;
            $reply['content'] = $row->CommentReplyContent;
            $reply['date'] = $row->CommentReplyDate;
            $reply['userId'] = $row->UserID;
            $reply['userName'] = $row->UserName;
            //Image
            $image = @$row->UserImagePath;
            if($image !== null){
                $site_url = 'https://gulfilosu.com';
                $image = $site_url . @$row->UserImagePath;
            }
            $reply['userImage'] = $image;
            $reply['likeCount'] = $this->getReplyLikeCount($row->CommentReplyID);
            $reply['liked'] = $this->isUserLikedReply($userId, $row->CommentReplyID);
            array_push($replies, $reply);
        }
        return $replies;
    } //Updated

    public function commentUnLike($userName, $commentId){
        if($this->checkCommentByCommentID($commentId) == COMMENT_EXIST){
            $userId = $this->getUserIDByUserName($userName);
            if($this->isUserLikedComment($userId, $commentId)){
                $stmt = $this->con->prepare("DELETE FROM comment_like WHERE CommentId = ? AND UserId = ?");
                $stmt->execute(array($commentId, $userId));
                if($stmt){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function commentLike($userName, $commentId){
        if($this->checkCommentByCommentID($commentId) == COMMENT_EXIST){
            $userId = $this->getUserIDByUserName($userName);
            if(!$this->isUserLikedComment($userId, $commentId)){
                $stmt = $this->con->prepare("INSERT INTO comment_like (CommentId, UserId) VALUES (?, ?)");
                $stmt->execute(array($commentId, $userId));
                if($stmt){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function postUnLike($userName, $postId){
        if($this->checkPostByPostID($postId) == POST_EXIST){
            $userId = $this->getUserIDByUserName($userName);
            if($this->isUserLikedPost($userId, $postId)){
                $stmt = $this->con->prepare("DELETE FROM post_like WHERE PostId = ? AND UserId = ?");
                $stmt->execute(array($postId, $userId));
                if($stmt){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function postLike($userName, $postId){
        if($this->checkPostByPostID($postId) == POST_EXIST){
            $userId = $this->getUserIDByUserName($userName);
            if(!$this->isUserLikedPost($userId, $postId)){
                $stmt = $this->con->prepare("INSERT INTO post_like (PostId, UserId) VALUES (?, ?)");
                $stmt->execute(array($postId, $userId));
                if($stmt){
                    $to = $this->getPostOwnerUsername($postId);
                    if($userName != $to){ //Kendi postunu beğenirse bildirim gitmesin
                        $notify = new PushNotification;
                        $notify->postLikeNotification($userName, $to, $postId);
                        return true;
                    }else{
                        return true;
                    }
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function categoryUnFollow($userName, $categoryId){
        $userID = $this->getUserIDByUserName($userName);
        if($this->isUserFollowingCategory($userID, $categoryId)){
            $stmt = $this->con->prepare("DELETE FROM user_category WHERE (UserId = ? AND CategoryId = ?)");
            $stmt->execute(array($userID, $categoryId));
            if($stmt){
                return UNFOLLOW_SUCCESS;
            }else{
                return UNFOLLOW_ERROR;
            }
        }else{
            return ALREADY_UNFOLLOWING;
        }
    }

    public function categoryFollow($userName, $categoryId){
        $userID = $this->getUserIDByUserName($userName);
        if(!$this->isUserFollowingCategory($userID, $categoryId)){
            $stmt = $this->con->prepare("INSERT INTO user_category (UserId, CategoryId) VALUES (?, ?)");
            $stmt->execute(array($userID, $categoryId));
            if($stmt){
                return FOLLOW_SUCCESS;
            }else{
                return FOLLOW_ERROR;
            }
        }else{
            return ALREADY_FOLLOWING;
        }
    }

    public function unFollow($userName, $targetUserName){ //userId = Takip eden, FollowId = Takip edilen //Adamın takipçileri FollowId ile bulunuyor //Adamın takip ettikleri UserId ile bulunuyor
        $userID = $this->getUserIDByUserName($userName);
        $targetUserID = $this->getUserIDByUserName($targetUserName);
        if($this->isFollowing($userID, $targetUserID)){
            $stmt = $this->con->prepare("DELETE FROM follower WHERE (FollowID = ? AND UserId = ?)");
            $stmt->execute(array($targetUserID, $userID));
            if($stmt){
                return UNFOLLOW_SUCCESS;
            }else{
                return UNFOLLOW_ERROR;
            }
        }else{
            return ALREADY_UNFOLLOWING;
        }
    }

    public function follow($userName, $targetUserName){ //userId = Takip eden, FollowId = Takip edilen //Adamın takipçileri FollowId ile bulunuyor //Adamın takip ettikleri UserId ile bulunuyor
        $userID = $this->getUserIDByUserName($userName);
        $targetUserID = $this->getUserIDByUserName($targetUserName);
        if(!$this->isFollowing($userID, $targetUserID)){
            $stmt = $this->con->prepare("INSERT INTO follower (FollowID, UserId) VALUES (?, ?)");
            $stmt->execute(array($targetUserID, $userID));
            if($stmt){
                $notify = new PushNotification;
                $notify->followNotification($userName, $targetUserName);
                return FOLLOW_SUCCESS;
            }else{
                return FOLLOW_ERROR;
            }
        }else{
            return ALREADY_FOLLOWING;
        }
    }

    public function getCategoryProfile($userName, $categoryId){
        $userID = $this->getUserIDByUserName($userName);
        $stmtCategory = $this->con->prepare("SELECT * FROM category_view WHERE CategoryId = ?");
        $stmtCategory->execute(array($categoryId));
        $profile = array();
        $category = array();

        $rowCategory = $stmtCategory->fetch(PDO::FETCH_OBJ);
        @$category['id'] = $rowCategory->CategoryID;
        @$category['name'] = $rowCategory->CategoryName;
        @$category['image'] = $rowCategory->CategoryImage;
        @$category['follow'] = "0";
        @$category['follower'] = $this->getCategoryFollowerCount($rowCategory->CategoryID);
        @$category['isfollow'] = $this->isUserFollowingCategory($userID, $categoryId);
        //MiniPost
        $categoryFinalID = $this->getCategoryFinalIDByCategoryID($categoryId);
        $stmtPost = $this->con->prepare("SELECT * FROM post_view WHERE CategoryFinalID = ? ORDER BY PostDate DESC");
        $stmtPost->execute(array($categoryFinalID));
        $posts = array();
        $options = array();
        while ($rowPost = $stmtPost->fetch(PDO::FETCH_OBJ)) {
            $post = array();
            $post['id'] = $rowPost->PostID; //ID Çekildi
            $post['question'] = $rowPost->PostQuestion; //Question Çekildi
            //**En çok oylanan option çekildi */
            $options = $this->getMostVotedOptionByPostID($rowPost->PostID);
            $post['option'] = $options;
            //**En çok oylanan option çekildi */
            $post['date'] = $rowPost->PostDate; //Date Çekildi
            $post['category'] = $rowCategory->CategoryName; //RowCategory'den geliyor bu veri
            $post['username'] = $rowPost->UserName;
            $post['userid'] = $rowPost->UserID;
            //Image
            $image = @$rowPost->UserImagePath;
            if($image !== null){
                $site_url = 'https://gulfilosu.com';
                $image = $site_url . @$rowPost->UserImagePath;
            }
            $post['image'] = $image;
            $post['votecount'] = $this->getVoteCount($rowPost->PostID);
            $post['commentcount'] = $this->getCommentCount($rowPost->PostID);
            $post['likecount'] = $this->getPostLikeCount($rowPost->PostID);
            array_push($posts, $post);
        }
        $profile['category'] = $category;
        $profile['miniPost'] = $posts;
        return $profile;
    }

    public function getProfile($userName, $targetUser){ //userId = Takip eden, FollowId = Takip edilen //Adamın takipçileri FollowId ile bulunuyor //Adamın takip ettikleri UserId ile bulunuyor
        $userID = $this->getUserIDByUserName($userName);
        $targetUserID = $this->getUserIDByUserName($targetUser);
        $stmtUser = $this->con->prepare("SELECT * FROM user_view WHERE UserID = ?");
        $stmtUser->execute(array($targetUserID));
        $profile = array();
        $user = array();

        $rowUser = $stmtUser->fetch(PDO::FETCH_OBJ);
        @$user['id'] = $rowUser->UserID;
        @$user['name'] = $rowUser->UserName;
        @$user['about'] = $rowUser->UserAbout;
        @$user['firstName'] = $rowUser->UserFirstName;
        @$user['lastName'] = $rowUser->UserLastName;
        @$user['email'] = $rowUser->UserEmail;

        //TotalLike
        $stmtTotalLike = $this->con->prepare("SELECT TotalLike FROM post_like_view WHERE UserID = ?");
        $stmtTotalLike->execute(array($targetUserID));
        $totalLike = 0;
        while ($row = $stmtTotalLike->fetch(PDO::FETCH_OBJ)) {
            $totalLike += $row->TotalLike;
        }
        $user['totalLike'] = strval($totalLike);
        //Follow
        $stmtFollow = $this->con->prepare("SELECT COUNT(*) AS 'FollowCount' FROM follower WHERE UserID = ?");
        $stmtFollow->execute(array($targetUserID));
        $rowFollow = $stmtFollow->fetch(PDO::FETCH_OBJ);
        $user['follow'] = $rowFollow->FollowCount;
        //Follower
        $stmtFollower = $this->con->prepare("SELECT COUNT(*) AS 'FollowerCount' FROM follower WHERE FollowId = ?");
        $stmtFollower->execute(array($targetUserID));
        $rowFollower = $stmtFollower->fetch(PDO::FETCH_OBJ);
        $user['follower'] = $rowFollower->FollowerCount;
        //Image
        $stmtImage = $this->con->prepare("SELECT UserImagePath FROM user_image WHERE UserId = ?");
        $stmtImage->execute(array($targetUserID));
        $rowImage = $stmtImage->fetch(PDO::FETCH_OBJ);
        $image = @$rowImage->UserImagePath;
        if($image !== null){
            $site_url = 'https://gulfilosu.com';
            $image = $site_url . @$rowImage->UserImagePath;
        }
        $user['image'] = $image; //Resim yoksa:: Trying to get property 'UserImagePath' of non-object in D:\xampp\htdocs\Raterfield\includes\DbOperations.php on line 64
        //isFollow
        if($userID == $targetUserID){
            $user['following'] = true;
        }else{
            $user['following'] = $this->isFollowing($userID, $targetUserID);
        }
        //MiniPost
        $posts = array();
        $options = array();
        $stmtMiniPost = $this->con->prepare("SELECT * FROM post_view WHERE UserID = ? ORDER BY PostDate DESC");
        $stmtMiniPost->execute(array($targetUserID));
        while ($rowMiniPost = $stmtMiniPost->fetch(PDO::FETCH_OBJ)) {
            $post = array();
            $post['id'] = $rowMiniPost->PostID;
            $post['question'] = $rowMiniPost->PostQuestion;
            $options = $this->getMostVotedOptionByPostID($rowMiniPost->PostID);
            $post['option'] = $options;
            $post['totalLike'] = $this->getTotalLikeByPostID($rowMiniPost->PostID);
            $post['votecount'] = $this->getVoteCount($rowMiniPost->PostID);
            $post['commentcount'] = $this->getCommentCount($rowMiniPost->PostID);
            array_push($posts, $post);
        }
        $profile['user'] = $user;
        $profile['miniPost'] = $posts;

        return $profile;
    }

    public function addPost($userName, $question, $description, $options, $categoryID){
        /*if(count($categoryID) == 1){
            $stmtCategory = $this->con->prepare("INSERT INTO post_category (CategoryId, PostId) VALUES (?, ?)"); //2 Parametre
            $stmtCategory->execute(array($option, $postID));
        }*/
        $userID = $this->getUserIDByUserName($userName);
        $stmtPost = $this->con->prepare("INSERT INTO post (PostQuestion, PostDescription, UserId) VALUES (?, ?, ?)"); //3 Parametre
        $stmtPost->execute(array($question, $description, $userID));
        $postID = $this->con->lastInsertId();
        if($stmtPost){
            foreach ($options as $option) {
            $stmtOption = $this->con->prepare("INSERT INTO option2 (OptionName, PostId) VALUES (?, ?)"); //2 Parametre
            $stmtOption->execute(array($option, $postID));
            }
            foreach ($categoryID as $currentCategoryID){
                $stmtCategory = $this->con->prepare("INSERT INTO post_category (CategoryId, PostId) VALUES (?, ?)"); //2 Parametre
                $stmtCategory->execute(array($currentCategoryID, $postID));
            }
            return true;
        }else{
            return false;
        }
    } //Updated

    public function getUserCategory($locale, $userName){
        $userId = $this->getUserIDByUserName($userName);
        $stmt = $this->con->prepare("SELECT * FROM category_view WHERE CategoryLocale = ?");
        $stmt->execute(array($locale));
        $categories = array();
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $category = array();
            $category['id'] = $row->CategoryID;
            $category['name'] = $row->CategoryName;
            $category['image'] = $row->CategoryImage;
            $category['isfollow'] = $this->isUserFollowingCategory($userId, $row->CategoryID);
            array_push($categories, $category);
        }
        if ($categories == null) {
            $stmt = $this->con->prepare("SELECT * FROM category_view WHERE CategoryLocale = 'English' ");
            $stmt->execute();
            $categories = array();
            while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                $category = array();
                $category['id'] = $row->CategoryID;
                $category['name'] = $row->CategoryName;
                $category['image'] = $row->CategoryImage;
                $category['isfollow'] = $this->isUserFollowingCategory($userId, $row->CategoryID);
                array_push($categories, $category);
            }
        }
        return $categories;
    }

    public function getCategory($locale){
        $stmt = $this->con->prepare("SELECT * FROM category_view WHERE CategoryLocale = ?");
        $stmt->execute(array($locale));
        $categories = array();
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $category = array();
            $category['id'] = $row->CategoryID;
            $category['name'] = $row->CategoryName;
            $category['image'] = $row->CategoryImage;
            array_push($categories, $category);
        }
        if ($categories == null) {
            $stmt = $this->con->prepare("SELECT * FROM category_view WHERE CategoryLocale = 'English' ");
            $stmt->execute();
            $categories = array();
            while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                $category = array();
                $category['id'] = $row->CategoryID;
                $category['name'] = $row->CategoryName;
                $category['image'] = $row->CategoryImage;
                array_push($categories, $category);
            }
        }
        return $categories;
    }

    public function userImage($userName, $path){
        try {
            $userID = $this->getUserIDByUserName($userName);
            $this->checkUserImage($userID); //Kullanıcının önceki resmi silindi
            $stmt = $this->con->prepare("INSERT INTO user_image (UserImagePath, UserId) VALUES (?, ?)"); //2 Parametre
            $stmt->execute(array($path, $userID));
            return true;
        } catch (Exception $e) {
            return false;
        }
    } //Updated

    public function userCategory($userName, $userCategory){
        try {
            $userID = $this->getUserIDByUserName($userName);
            foreach ($userCategory as $item) {
                $categoryId = $this->getCategoryIDByCategoryName($item);
                if($categoryId != null){
                    $stmt = $this->con->prepare("INSERT INTO user_category (UserId, CategoryId) VALUES (?, ?)"); //2 Parametre
                    $stmt->execute(array($userID, $categoryId));
                }else{
                    return false;
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    } //Updated

    public function userpost($userName){

        $stmtPost = $this->con->prepare("SELECT * FROM post_view ORDER BY PostID DESC");
        $stmtPost->execute();
        $posts = array();
        $options = array();
        while ($rowPost = $stmtPost->fetch(PDO::FETCH_OBJ)) {
            $post = array();
            $post['id'] = $rowPost->PostID;
            $post['question'] = $rowPost->PostQuestion;
            $post['description'] = $rowPost->PostDescription;
            $options = $this->getOptionsByPostID($rowPost->PostID);
            $post['option'] = $options;
            $post['date'] = $rowPost->PostDate;
            $post['username'] = $rowPost->UserName;
            $post['votecount'] = $this->getVoteCount($rowPost->PostID);
            $post['commentcount'] = $this->getCommentCount($rowPost->PostID);
            $post['voted'] = $this->getUserVotedName($userName, $rowPost->PostID);
            array_push($posts, $post);
        }
        return $posts;
    } //updated //2 tane aynı işlevi yapan metod var bkz. post

    public function vote($userName, $userPassword, $optionID){
        if ($this->userLogin($userName, $userPassword) == USER_AUTHENTICATED) {
            //$optionID = $this->getOptionIDByPostIDandOptionName($postID, $optionName);
            if ($this->checkOptionAvailable($optionID)) {
                $userID = $this->getUserIDByUserName($userName);
                if ($this->checkCanUserVote($optionID, $userID)) {
                    $stmt = $this->con->prepare("INSERT INTO vote (OptionId, UserId) VALUES (?, ?)"); //2 Parametre
                    $stmt->execute(array($optionID, $userID));
                    if ($stmt) {
                        return OPTION_SUCCESS;
                    } else {
                        return OPTION_NOT_SUCCESS;
                    }
                } else {
                    return USER_CANT_VOTE;
                }
            } else {
                return OPTION_NOT_EXIST;
            }
        } else {
            return USER_FAILURE;
        }
    } //Updated

    public function comment($userName, $userPassword, $comment, $postID){
        if ($this->checkPostByPostID($postID) == POST_EXIST) {
            if ($this->userLogin($userName, $userPassword) == USER_AUTHENTICATED) {
                $userID = $this->getUserIDByUserName($userName);
                $stmt = $this->con->prepare("INSERT INTO comment (UserId, PostId, CommentContent) VALUES (?, ?, ?)"); //3 Parametre
                $stmt->execute(array($userID, $postID, $comment));
                if ($stmt) {
                    $to = $this->getPostOwnerUsername($postID);
                    if($userName != $to){ //Kendi postunu beğenirse bildirim gitmesin
                        $notify = new PushNotification;
                        $notify->postCommentNotification($userName, $to, $postID);
                        return true;
                    }else{
                        return true;
                    }
                    return COMMENT_SUCCESS;
                } else {
                    return COMMENT_UNSUCCESS;
                }
            }
            return USER_FAILURE;
        } else if ($this->checkPostByPostID($postID) == POST_NOT_EXIST) {
            return POST_NOT_EXIST;
        } else {
            return WTF;
        }
    } //Updated

    public function getComment($userName, $postID){

        $stmt = $this->con->prepare("SELECT * FROM comment_view WHERE PostID = ? ORDER BY CommentDate DESC");
        $stmt->execute(array($postID));
        $comments = array();
        $userId = $this->getUserIDByUserName($userName);
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $comment = array();
            $comment['id'] = $row->CommentID;
            $comment['postid'] = $row->PostID;
            $comment['content'] = $row->CommentContent;
            $comment['date'] = $row->CommentDate;
            $comment['username'] = $row->UserName;
            //Image
            $image = @$row->UserImagePath;
            if($image !== null){
                $site_url = 'https://gulfilosu.com';
                $image = $site_url . @$row->UserImagePath;
            }
            $comment['image'] = $image;
            $comment['likecount'] = $this->getCommentLikeCount($row->CommentID);
            $comment['replycount'] = $this->getReplyCount($row->CommentID);
            $comment['liked'] = $this->isUserLikedComment($userId, $row->CommentID);
            array_push($comments, $comment);
        }
        return $comments;
    } //Updated

    public function postDetails($postID, $userName, $locale){
        $stmtPost = $this->con->prepare("SELECT * FROM post_view WHERE PostID = ?");
        $stmtPost->execute(array($postID));
        $options = array();
        $posts = array();
        while ($rowPost = $stmtPost->fetch(PDO::FETCH_OBJ)) {
            $userId = $this->getUserIDByUserName($userName);
            $post = array();
            $post['id'] = $rowPost->PostID;
            $post['question'] = $rowPost->PostQuestion;
            $post['description'] = $rowPost->PostDescription;
            $post['category'] = $rowPost->CategoryName;
            $options = $this->getOptionsByPostID2($rowPost->PostID, $userName);
            $post['option'] = $options;
            $post['date'] = $rowPost->PostDate;
            $post['category'] = $this->getCategoryNameByLocale($locale, $rowPost->CategoryFinalID);
            $post['username'] = $rowPost->UserName;
            //Image
            $image = @$rowPost->UserImagePath;
            if($image !== null){
                $site_url = 'https://gulfilosu.com';
                $image = $site_url . @$rowPost->UserImagePath;
            }
            $post['image'] = $image;
            $post['votecount'] = $this->getVoteCount($rowPost->PostID); //Oyları options değişkeninden çeksin
            $post['commentcount'] = $this->getCommentCount($rowPost->PostID);
            $post['likecount'] = $this->getPostLikeCount($rowPost->PostID);
            //$post['voted'] = $this->getUserVotedName($userName, $rowPost->PostID);
            $post['liked'] = $this->isUserLikedPost($userId, $rowPost->PostID);
            array_push($posts, $post);
        }
        return $posts;
    } //Updated

    public function post($userName, $locale){
        //Get UserID
        $userId = $this->getUserIDByUserName($userName);
        //Get User's Followed Users
        $userFollowedUsers = $this->getUserFollowedUsers($userId);
        $userIn  = str_repeat('?,', count($userFollowedUsers) - 1) . '?'; //Array size ı kadar ? ekliyor
        //Get User's Followed Categories
        $userFollowedCategories = $this->getUserFollowedCategories($userId);
        $categoryIn  = str_repeat('?,', count($userFollowedCategories) - 1) . '?'; //Array size ı kadar ? ekliyor
        //PDO wants one array
        $singleArray = $this->makeSingleArray($userFollowedUsers, $userFollowedCategories);
        if(empty($userFollowedUsers)){ //Herhangi bir user takip etmiyor olabilir
            $stmtPost = $this->con->prepare("SELECT * FROM post_view WHERE CategoryFinalID IN ($categoryIn) ORDER BY PostID DESC");
            $stmtPost->execute($singleArray);
        }else{
            $stmtPost = $this->con->prepare("SELECT * FROM post_view WHERE UserID IN ($userIn) OR CategoryFinalID IN ($categoryIn) ORDER BY PostID DESC");
            $stmtPost->execute($singleArray);
        }
        $posts = array();
        $options = array();
        while ($rowPost = $stmtPost->fetch(PDO::FETCH_OBJ)) {
            $post = array();
            $post['id'] = $rowPost->PostID;
            $post['question'] = $rowPost->PostQuestion;
            $post['description'] = $rowPost->PostDescription;
            //Options
            $options = $this->getOptionsByPostIDForPost2($rowPost->PostID, $userName);
            $post['option'] = $options;
            $post['date'] = $rowPost->PostDate;
            $post['category'] = $this->getCategoryNameByLocale($locale, $rowPost->CategoryFinalID);
            $post['username'] = $rowPost->UserName;
            $post['userid'] = $rowPost->UserID;
            //Image
            $image = @$rowPost->UserImagePath;
            if($image !== null){
                $site_url = 'https://gulfilosu.com';
                $image = $site_url . @$rowPost->UserImagePath;
            }
            $post['image'] = $image;
            $post['votecount'] = $this->getVoteCount($rowPost->PostID);
            $post['commentcount'] = $this->getCommentCount($rowPost->PostID);
            $post['likecount'] = $this->getPostLikeCount($rowPost->PostID);
            //$post['voted'] = $this->getUserVotedName($userName, $rowPost->PostID);
            $post['liked'] = $this->isUserLikedPost($userId, $rowPost->PostID);
            array_push($posts, $post);
        }
        return $posts;
    } //updated  //2 tane aynı işlevi yapan metod var bkz. userpost

    public function createUser($userName, $userPassword, $userFirstName, $userLastName, $userEmail, $userToken){
        if (!$this->isEmailExist($userName, $userEmail)) {
            $stmt = $this->con->prepare("INSERT INTO user (UserName, UserPassword, UserFirstName, UserLastName, UserEmail) VALUES (?, ?, ?, ?, ?)"); //5 Parametre
            $stmt->execute(array($userName, $userPassword, $userFirstName, $userLastName, $userEmail));
            if ($stmt) {
                $sql = null;
                if($this->isTokenExists($userToken)){
                    $sql = "UPDATE user_token SET UserId = ? WHERE UserToken = ?";
                }else{
                    $sql = "INSERT INTO user_token (UserId, UserToken) VALUES (?, ?)";
                }
                $userID = $this->getUserIDByUserName($userName);
                $stmtToken = $this->con->prepare($sql); //2 Parametre
                $stmtToken->execute(array($userID, $userToken));
                if($stmtToken){
                    return USER_CREATED;
                }else{
                    return USER_TOKEN_FAILURE;
                }
            } else {
                return USER_FAILURE;
            }
        }
        return USER_EXISTS;
    } //Updated

    public function userLogin($userName, $userPassword){
        if ($this->isEmailExist($userName, $userName)) {
            $hashed_password = $this->getUsersPasswordByEmailOrUserName($userName);
            if (password_verify($userPassword, $hashed_password)) {
                return USER_AUTHENTICATED;
            } else {
                return USER_PASSWORD_DO_NOT_MATCH;
            }
        } else {
            return USER_NOT_FOUND;
        }
    } //Updated

    private function getUsersPasswordByEmailOrUserName($email){
        $stmt = $this->con->prepare("SELECT UserPassword FROM user WHERE UserEmail = ? OR UserName = ?");
        $stmt->execute(array($email, $email));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row->UserPassword;
    } //Updated

    public function getAllUsers(){
        $stmt = $this->con->prepare("SELECT id, email, name, school FROM users;");
        $stmt->execute();
        //$row = $stmt->fetch(PDO::FETCH_OBJ);
        $users = array();
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $user = array();
            $user['id'] = $row->id;
            $user['email'] = $row->email;
            $user['name'] = $row->name;
            $user['school'] = $row->school;
            array_push($users, $user);
        }
        return $users;
    }

    public function getUserByEmailOrUserName($email){
        $stmt = $this->con->prepare("SELECT UserID, UserName, UserFirstName, UserLastName, UserEmail FROM user WHERE UserEmail = ? OR  UserName = ?");
        $stmt->execute(array($email, $email));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        $user = array();
        $user['UserID'] = $row->UserID;
        $user['UserName'] = $row->UserName;
        $user['UserFirstName'] = $row->UserFirstName;
        $user['UserLastName'] = $row->UserLastName;
        $user['UserEmail'] = $row->UserEmail;
        return $user;
    }
    /**
     * userId = Integer
     * firstName = String
     * lastName = String
     * about = String
     * email = String
     */
    public function updateUser($userId, $firstName, $lastName, $about, $email){
        $stmt = $this->con->prepare("UPDATE user SET UserFirstName = ?, UserLastName = ?, UserEmail = ?, UserAbout = ? WHERE UserID = ?");
        if ($stmt->execute(array($firstName, $lastName, $email, $about, $userId)))
            return true;
        return false;
    }

    public function updatePassword($currentpassword, $newpassword, $email){
        $hashed_password = $this->getUsersPasswordByEmail($email);

        if (password_verify($currentpassword, $hashed_password)) {

            $hash_password = password_hash($newpassword, PASSWORD_DEFAULT);
            $stmt = $this->con->prepare("UPDATE users SET password = ? WHERE email = ?");
            if ($stmt->execute(array($hash_password, $email)))
                return PASSWORD_CHANGED;
            return PASSWORD_NOT_CHANGED;
        } else {
            return PASSWORD_DO_NOT_MATCH;
        }
    }

    public function deleteUser($id){
        $stmt = $this->con->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute(array($id)))
            return true;
        return false;
    }

    public function isEmailExist($username, $email){
        //Kullanıcı adı veya eposta var mı ?
        $stmt = $this->con->prepare("SELECT UserID FROM user WHERE UserEmail = ? OR UserName = ?");
        $stmt->execute(array($email, $username));
        $row = $stmt->rowCount();
        if (@$row > 0) {
            return true; //var
        } else {
            return false; //yok
        }
    } //Updated

    public function getUserIDByUserName($userName){
        $sql = "SELECT UserID FROM user WHERE UserName = ? OR UserEmail = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($userName, $userName));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->UserID;
    } //Updated

    public function getUserNameByUserID($userId){
        $sql = "SELECT UserName FROM user WHERE UserID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($userId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row->UserName;
    } //Updated

    public function checkReplyByReplyID($replyID){
        $sql = "SELECT * FROM comment_reply WHERE CommentReplyID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($replyID));
        $row = $stmt->rowCount();
        if (@$row > 0) {
            return COMMENT_EXIST;
        } else {
            return COMMENT_NOT_EXIST;
        }
    } //Updated

    public function checkCommentByCommentID($commentID){
        $sql = "SELECT * FROM comment WHERE CommentID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($commentID));
        $row = $stmt->rowCount();
        if (@$row > 0) {
            return COMMENT_EXIST;
        } else {
            return COMMENT_NOT_EXIST;
        }
    } //Updated

    public function checkPostByPostID($postID){
        $sql = "SELECT * FROM post WHERE PostID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        $row = $stmt->rowCount();
        if (@$row > 0) {
            return POST_EXIST;
        } else {
            return POST_NOT_EXIST;
        }
    } //Updated

    public function checkOptionAvailable($optionID){
        $sql = "SELECT * FROM option2 WHERE OptionID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($optionID));
        $row = $stmt->rowCount();
        if (@$row > 0) {
            return true;
        } else {
            return false;
        }
    } //Updated

    public function getOptionsByPostID($postID){
        $options = array();
        $option = array();
        $optionCount = 1;
        $sql = "SELECT OptionID, OptionName FROM option2 WHERE postId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $option['name'] = $row->OptionName;
            $option['count'] = $this->getOptionCountByOptionID($row->OptionID);
            array_push($options, $option);
            //$options[$optionCount] = $option;
            $optionCount++;
        }
        while ($optionCount <= 5) {
            $option['name'] = "";
            $option['count'] = "0";
            array_push($options, $option);
            //$options[$optionCount] = $option;
            $optionCount++;
        }
        return $options;
    } //Updated

    public function getOptionsByPostID2($postID, $userName){
        $options = array();
        $option = array();
        $votedName = $this->getUserVotedName($userName, $postID);
        $sql = "SELECT OptionID, OptionName FROM option2 WHERE postId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $option['id'] = $row->OptionID;
            $option['name'] = $row->OptionName;
            $option['count'] = $this->getOptionCountByOptionID($row->OptionID);
            if($row->OptionName === $votedName){
                $option['voted'] = true;
            }else{
                $option['voted'] = false;
            }
            array_push($options, $option);
            //$options[$optionCount] = $option;
        }
        return $options;
    } //Updated

    public function getOptionsByPostIDForPost($postID){
        $options = array();
        $option = array();
        $optionCount = 1;
        $sql = "SELECT * FROM option2_view WHERE PostID = ? ORDER BY VoteCount DESC LIMIT 2";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $option['name'] = $row->OptionName;
            $option['count'] = $row->VoteCount;
            array_push($options, $option);
            //$options[$optionCount] = $option;
            $optionCount++;
        }
        while ($optionCount <= 5) {
            $option['name'] = "";
            $option['count'] = "0";
            array_push($options, $option);
            //$options[$optionCount] = $option;
            $optionCount++;
        }
        return $options;
    } //Updated

    public function getOptionsByPostIDForDiscover($postID){
        $options = array();
        $option = array();
        $optionCount = 1;
        $sql = "SELECT * FROM option2_view WHERE PostID = ? ORDER BY VoteCount ASC LIMIT 2"; //Normalde DESC ile Çalışması gerekirken ASC İle çalıştı inan ne yaptım bilmiyorum
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $option['name'] = $row->OptionName;
            $option['count'] = $row->VoteCount;
            array_push($options, $option);
            //$options[$optionCount] = $option;
            $optionCount++;
        }
        while ($optionCount <= 5) {
            $option['name'] = "";
            $option['count'] = "0";
            array_push($options, $option);
            //$options[$optionCount] = $option;
            $optionCount++;
        }
        return $options;
    } //Updated

    public function getMostVotedOptionByPostID($postID){
        $option = array();
        $sql = "SELECT * FROM option2_view WHERE PostID = ? ORDER BY VoteCount DESC LIMIT 1"; //Normalde DESC ile Çalışması gerekirken ASC İle çalıştı inan ne yaptım bilmiyorum
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        $option['name'] = $row->OptionName;
        $option['count'] = $row->VoteCount;
        return $option;
    } //Updated

    public function getOptionsByPostIDForPost2($postID, $userName){
        $options = array();
        $option = array();
        $votedName = $this->getUserVotedName($userName, $postID);
        $sql = "SELECT * FROM option2_view WHERE PostID = ? ORDER BY VoteCount DESC LIMIT 2";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $option['name'] = $row->OptionName;
            $option['count'] = $row->VoteCount;
            if($row->OptionName === $votedName){
                $option['voted'] = true;
            }else{
                $option['voted'] = false;
            }
            array_push($options, $option);
            //$options[$optionCount] = $option;
        }
        return $options;
    } //Updated

    public function getOptionCountByOptionID($optionID){
        $sql = "SELECT COUNT(*) AS Count FROM vote WHERE OptionId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($optionID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->Count;
    } //Updated

    public function getUserVotedName($userName, $postId){
        //$userId = $this->getUserIDByUserName($userName);
        $sql = "SELECT OptionName FROM vote_view WHERE UserName = ? AND PostId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($userName, $postId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        @$optionName = $row->OptionName;
        if (@$row->OptionName === null) { //Null ise değiştir
            $optionName = "";
        }
        return $optionName;
    } //Updated

    public function checkCanUserVote($optionID, $userID){
        $postID = $this->getPostIDByOptionID($optionID);
        $sql = "SELECT * FROM vote_view WHERE PostId = ? AND UserId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID, $userID));
        $row = $stmt->rowCount();
        if (@$row > 0) {
            return false;
        } else {
            return true;
        }
    } //Updated

    public function getPostIDByOptionID($optionID){
        $sql = "SELECT PostId FROM option2 WHERE OptionID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($optionID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row->PostId;
    } //Updated

    public function getOptionIDByPostIDandOptionName($postID, $optionName){
        $sql = "SELECT OptionID FROM `option2` WHERE PostId = ? AND OptionName = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID, $optionName));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->OptionID;
    } //Updated

    public function getVoteCount($postID){
        $sql = "SELECT COUNT(*) AS Count FROM vote_view WHERE PostId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->Count;
    } //Updated

    public function getCommentCount($postID){
        $sql = "SELECT COUNT(*) AS Count FROM comment_view WHERE PostID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->Count;
    } //Updated

    public function getCategoryIDByCategoryName($categoryName){
        $sql = "SELECT CategoryID FROM category_view WHERE CategoryName = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($categoryName));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row->CategoryID;
    } //Updated

    /**
     * Required locale and categoryFinalId
     * Return CategoryName
     */
    public function getCategoryNameByLocale($locale, $categoryFinalId){
        $sql = "SELECT CategoryName FROM category_view WHERE CategoryLocale = ? AND FinalID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($locale, $categoryFinalId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row->CategoryName;
    } //Updated

    public function isCategoryExist($categoryId){
        $stmt = $this->con->prepare("SELECT COUNT(CategoryID) AS 'Count' FROM category_view WHERE CategoryID = ?");
        $stmt ->execute(array($categoryId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if(@$row->Count > 0){
            return true;
        }else{
            return false;
        }
    }

    public function isUserExist($user){ //UserName or UserEmail
            $stmt = $this->con->prepare("SELECT UserID FROM user WHERE UserEmail = ? OR UserName = ?");
            $stmt ->execute(array($user, $user));
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            if(@$row->UserID > 0){
                return true;
            }else{
                return false;
            }
    }

    public function isUserIdExist($userId){ //UserName or UserEmail
        $stmt = $this->con->prepare("SELECT UserID FROM user WHERE UserID = ?");
        $stmt ->execute(array($userId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if(@$row->UserID > 0){
            return true;
        }else{
            return false;
        }
    }

    public function isCommentExist($commentId){ //UserName or UserEmail
        $stmt = $this->con->prepare("SELECT CommentID FROM comment WHERE CommentID = ?");
        $stmt ->execute(array($commentId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if(@$row->CommentID > 0){
            return true;
        }else{
            return false;
        }
    }

    public function isReplyExist($replyId){
        $stmt = $this->con->prepare("SELECT CommentReplyID FROM comment_reply WHERE CommentReplyID = ?");
        $stmt ->execute(array($replyId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if(@$row->CommentReplyID > 0){
            return true;
        }else{
            return false;
        }
    }

    public function getTotalLikeByPostID($postID){
        $stmt = $this->con->prepare("SELECT TotalLike FROM post_like_view WHERE PostID = ?");
        $stmt ->execute(array($postID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->TotalLike;
    } 

    public function isUserFollowingCategory($userId, $categoryId){
        $stmt = $this->con->prepare("SELECT COUNT(*) AS 'Count' FROM `user_category` WHERE UserId = ? AND CategoryId = ?");
        $stmt ->execute(array($userId, $categoryId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if(@$row->Count > 0){ //Takip Ediyor
            return true;
        }else{ //Etmiyor
            return false;
        }
    }

    public function isFollowing($userId, $targetUserId){
        $stmt = $this->con->prepare("SELECT COUNT(UserId) AS 'Count' FROM follower WHERE FollowID = ? AND UserId = ?");
        $stmt ->execute(array($targetUserId, $userId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if(@$row->Count > 0){ //Takip Ediyor
            return true;
        }else{ //Etmiyor
            return false;
        }
    }

    public function getCategoryFollowerCount($categoryId){
        $sql = "SELECT COUNT(*) AS 'Count' FROM user_category WHERE CategoryId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($categoryId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->Count;
    }

    public function getPostLikeCount($postID){
        $sql = "SELECT COUNT(*) AS Count FROM post_like WHERE PostId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->Count;
    } //Updated

    public function getCommentLikeCount($commentID){
        $sql = "SELECT COUNT(*) AS Count FROM comment_like WHERE CommentId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($commentID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->Count;
    } //Updated

    public function getReplyLikeCount($replyID){
        $sql = "SELECT COUNT(*) AS Count FROM comment_reply_like WHERE CommentReplyId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($replyID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->Count;
    } //Updated

    public function getReplyCount($commentID){
        $sql = "SELECT COUNT(*) AS Count FROM reply_view WHERE CommentID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($commentID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->Count;
    } //Updated

    public function isUserLikedPost($userId, $postId){
        $stmt = $this->con->prepare("SELECT COUNT(*) AS 'Count' FROM post_like WHERE UserId = ? AND PostId = ?");
        $stmt ->execute(array($userId, $postId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if(@$row->Count > 0){
            return true;
        }else{
            return false;
        }
    }

    public function isUserLikedComment($userId, $commentId){
        $stmt = $this->con->prepare("SELECT COUNT(*) AS 'Count' FROM comment_like WHERE UserId = ? AND CommentId = ?");
        $stmt ->execute(array($userId, $commentId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if(@$row->Count > 0){
            return true;
        }else{
            return false;
        }
    }

    public function isUserLikedReply($userId, $replyId){
        $stmt = $this->con->prepare("SELECT COUNT(*) AS 'Count' FROM comment_reply_like WHERE UserId = ? AND CommentReplyId = ?");
        $stmt ->execute(array($userId, $replyId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if(@$row->Count > 0){
            return true;
        }else{
            return false;
        }
    }

    public function isTokenExists($token){
        $stmt = $this->con->prepare("SELECT COUNT(*) AS 'Count' FROM user_token WHERE UserToken = ?");
        $stmt ->execute(array($token));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        if(@$row->Count > 0){
            return true;
        }else{
            return false;
        }
    }

    public function setUserToken($userToken, $userName){
        $sql = null;
        $userID = $this->getUserIDByUserName($userName);
        if($this->isTokenExists($userToken)){
        $sql = "UPDATE user_token SET UserId = ? WHERE UserToken = ?";
        }else{
            $sql = "INSERT INTO user_token (UserId, UserToken) VALUES (?, ?)";
        }
        $stmtToken = $this->con->prepare($sql); //2 Parametre
        $stmtToken->execute(array($userID, $userToken));
        if($stmtToken){
            return true;
        }else{
            return false;
        }
    }

    public function getReplyOwnerUserID($replyId){
        $sql = "SELECT UserID FROM reply_view WHERE CommentReplyID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($replyId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->UserID;
    } //Updated

    public function getCommentOwnerUserID($commentId){
        $sql = "SELECT UserID FROM comment_view WHERE CommentID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($commentId));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->UserID;
    } //Updated

    /**
     * Required $postID
     * Return Integer UserID
     */
    public function getPostOwnerUserID($postID){
        $sql = "SELECT UserID FROM post_view WHERE PostId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->UserID;
    } //Updated

    public function getPostOwnerUsername($postID){
        $sql = "SELECT UserName FROM post_view WHERE PostId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->UserName;
    } //Updated

    public function getNotificationPostIdByNotificationIDForComment($notificationID){
        $sql = "SELECT PostId FROM notification_postcomment_view WHERE NotificationId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($notificationID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->PostId;
    } //Updated

    public function getNotificationPostIdByNotificationID($notificationID){
        $sql = "SELECT PostId FROM notification_postlike WHERE NotificationId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($notificationID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->PostId;
    } //Updated

    public function getPostTitleByPostID($postID){
        $sql = "SELECT PostQuestion FROM post_view WHERE PostId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->PostQuestion;
    } //Updated

    public function getCategoryIDsByLocale($locale){
        $sql = "SELECT CategoryID FROM category_view WHERE CategoryLocale = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($locale));
        $categoryIDs = array();
        while($row = $stmt->fetch(PDO::FETCH_OBJ)){
            //$categoryIDs = $row->CategoryID;
            array_push($categoryIDs, $row->CategoryID);
        }
        return $categoryIDs;
    }

    public function getCategoryFinalIDByCategoryID($categoryID){
        $sql = "SELECT FinalID FROM category_view WHERE CategoryID = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($categoryID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return @$row->FinalID;
    }

    public function getUserFollowedCategories($userID){
        $sql = "SELECT CategoryId FROM user_category WHERE UserId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($userID));
        $categoriesRaw = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); //Gereksiz kolonu siliyor
        $categoriesFinal = array();
        foreach($categoriesRaw as $category){
            $categoryFinal = $this->getCategoryFinalIDByCategoryID($category);
            array_push($categoriesFinal, $categoryFinal); //Categorynin tüm dillerini aktif etmek için final id gönderiyorum
        }
        return $categoriesFinal;
    } //Updated

    public function makeSingleArray($array1, $array2){ //Returning one integrated array for post
        $singleArray = array();
        foreach($array1 as $as){
            array_push($singleArray, $as); //Categorynin tüm dillerini aktif etmek için final id gönderiyorum
        }
        foreach($array2 as $as){
            array_push($singleArray, $as); //Categorynin tüm dillerini aktif etmek için final id gönderiyorum
        }
        return $singleArray;
    }

    public function getUserFollowedUsers($userID){//userId = Takip eden, FollowId = Takip edilen //Adamın takipçileri FollowId ile bulunuyor //Adamın takip ettikleri UserId ile bulunuyor
        $sql = "SELECT FollowId FROM follower WHERE UserId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($userID));
        $followings = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); //Gereksiz kolonu siliyor
        return $followings;
    } //Updated

    public function checkUserImage($userId){
        $stmtImage = $this->con->prepare("SELECT UserImagePath FROM user_image WHERE UserId = ?");
        $stmtImage ->execute(array($userId));
        $row = $stmtImage->fetch(PDO::FETCH_OBJ);
        $imagePath = $row->UserImagePath;
        if($imagePath == null || $imagePath == ""){
            return true;
        }else{
            $stmtDelete = $this->con->prepare("DELETE FROM user_image WHERE UserId = ?");
            $result = $stmtDelete ->execute(array($userId));
            if($result){
                @$imageResult = unlink(".." . $imagePath);
                if($imageResult){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
            return true;
        }
    }

    /**
     * Required postID, userId
     * if already reported return false
     * return boolean
     */
    public function checkCanUserReportPost($postID, $userID){
        $sql = "SELECT * FROM post_report WHERE PostId = ? AND UserId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID, $userID));
        $row = $stmt->rowCount();
        if (@$row > 0) {
            return false;
        } else {
            return true;
        }
    } //Updated

    /**
     * Required targetUserID, userId
     * if already reported return false
     * return boolean
     */
    public function checkCanUserReportProfile($targetUserID, $userID){
        $sql = "SELECT * FROM profile_report WHERE TargetUserID = ? AND UserId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($targetUserID, $userID));
        $row = $stmt->rowCount();
        if (@$row > 0) {
            return false;
        } else {
            return true;
        }
    } //Updated

    public function checkCanUserReportComment($commentId, $userID){
        $sql = "SELECT * FROM comment_report WHERE CommentId = ? AND UserId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($commentId, $userID));
        $row = $stmt->rowCount();
        if (@$row > 0) {
            return false;
        } else {
            return true;
        }
    } //Updated

    public function checkCanUserReportReply($replyId, $userID){
        $sql = "SELECT * FROM comment_reply_report WHERE ReplyId = ? AND UserId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($replyId, $userID));
        $row = $stmt->rowCount();
        if (@$row > 0) {
            return false;
        } else {
            return true;
        }
    } //Updated

    public function checkPostStatus($postID){
        $sql = "SELECT PostStatus FROM post WHERE PostId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        $postStatus = @$row->PostStatus;
        return $postStatus;
    }

    public function isPostDeleted($postID){
        $sql = "SELECT PostStatus FROM post WHERE PostId = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute(array($postID));
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        $postStatus = $row->PostStatus;
        return $postStatus;
    }

    public function v1Log($userId, $logAction){
        $ipAddress = $this->getIP();
        $browserTitle = $this->getBrowserTitle();
        $sql = "INSERT INTO v1_log (UserId, LogAction, LogIpAddress, LogBrowserTitle) VALUES (?, ?, ?, ?)";
        $stmtLog = $this->con->prepare($sql); //2 Parametre
        $stmtLog->execute(array($userId, $logAction, $ipAddress, $browserTitle));
        if($stmtLog){
            return true;
        }else{
            return false;
        }
    }

    public function getIP(){
        if(getenv("HTTP_CLIENT_IP")) {
             $ip = getenv("HTTP_CLIENT_IP");
         } elseif(getenv("HTTP_X_FORWARDED_FOR")) {
             $ip = getenv("HTTP_X_FORWARDED_FOR");
             if (strstr($ip, ',')) {
                 $tmp = explode (',', $ip);
                 $ip = trim($tmp[0]);
             }
         } else {
         $ip = getenv("REMOTE_ADDR");
         }
        return $ip;
    }

    public function getBrowserTitle(){
        //Headerstaki User-Agent bilgisini döndürüyor
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /*public function getIP(){
        if (!empty($_SERVER['HTTP_CLIENT_IP']))  
        {  
            $ip=$_SERVER['HTTP_CLIENT_IP'];  
        }  
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) //Proxy den bağlanıyorsa gerçek IP yi alır.
         
        {  
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];  
        }  
        else  
        {  
            $ip=$_SERVER['REMOTE_ADDR'];  
        }  
        return $ip;  
    }*/
}
