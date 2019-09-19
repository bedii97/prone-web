<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../includes/DbOperations.php';
require '../includes/PushNotification.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true //Geliştirme aşamasında detaylı hata raporu almak için
    ]
]);

/*//Token Oluşturma
$token = openssl_random_pseudo_bytes(16);
$token = bin2hex($token);
*/

//Code 203 = User Failure
//Code 204 = No Content

$app->post('/deleteusertoken', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'Token'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $token = $request_data['Token'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $result = $db->deleteUserToken($userName, $token);
            if ($result == true) {
                $message = array();
                $message['error'] = false;
                $message['message'] = 'Success';
                $response->write(json_encode($message));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(201);
            } else {
                $message = array();
                $message['error'] = true;
                $message['message'] = 'UnSuccess';
                $response->write(json_encode($message));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(400);
}); //Add Token

$app->post('/replyreport', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'ReplyId', 'CategoryId', 'Content'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $replyId = $request_data['ReplyId'];
        $categoryId = $request_data['CategoryId'];
        $content = $request_data['Content'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $postReportResult = $db->replyReport($userName, $replyId, $categoryId, $content);
            if ($postReportResult == POST_REPORTED) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Success';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else if($postReportResult == USER_CANT_REPORT){
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Already Reported';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else if($postReportResult == COMMENT_NOT_EXIST) {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Reply not exist';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Unexpected Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Like Post

$app->post('/commentreport', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'CommentId', 'CategoryId', 'Content'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $commentId = $request_data['CommentId'];
        $categoryId = $request_data['CategoryId'];
        $content = $request_data['Content'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $postReportResult = $db->commentReport($userName, $commentId, $categoryId, $content);
            if ($postReportResult == POST_REPORTED) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Success';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else if($postReportResult == USER_CANT_REPORT){
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Already Reported';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else if($postReportResult == COMMENT_NOT_EXIST) {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Comment not exist';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Unexpected Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Like Post

$app->post('/profilereport', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'UserId', 'CategoryId', 'Content'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $userId = $request_data['UserId'];
        $categoryId = $request_data['CategoryId'];
        $content = $request_data['Content'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $postReportResult = $db->profileReport($userName, $userId, $categoryId, $content);
            if ($postReportResult == POST_REPORTED) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Success';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else if($postReportResult == USER_CANT_REPORT){
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Already Reported';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else if($postReportResult == USER_NOT_FOUND) {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Target user not exist';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Unexpected Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Like Post

$app->post('/replydelete', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'ReplyId'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $replyId = $request_data['ReplyId'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $commentDeleteResult = $db->replyDelete($replyId, $userName);
            if ($commentDeleteResult) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Success';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Unexpected Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Like Post

$app->post('/commentdelete', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'CommentId'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $commentId = $request_data['CommentId'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $commentDeleteResult = $db->commentDelete($commentId, $userName);
            if ($commentDeleteResult) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Success';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Unexpected Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Like Post

$app->post('/postdelete', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'PostId'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $postId = $request_data['PostId'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $postDeleteResult = $db->postDelete($postId, $userName);
            if ($postDeleteResult) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Success';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Unexpected Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Like Post

$app->post('/postreport', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'PostId', 'CategoryId', 'Content'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $postId = $request_data['PostId'];
        $categoryId = $request_data['CategoryId'];
        $content = $request_data['Content'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $postReportResult = $db->postReport($userName, $postId, $categoryId, $content);
            if ($postReportResult == POST_REPORTED) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Success';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else if($postReportResult == USER_CANT_REPORT){
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Already Reported';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Unexpected Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Like Post

$app->post('/denmark', function (Request $request, Response $response) {
    $request_data = $request->getParsedBody();
    $denmark = $request_data['denmark'];
    //$db = new DbOperations;
    date_default_timezone_set('Europe/Istanbul');
    $lastDate = date_create($denmark);
    $nowDate = date_create(date('Y-m-d H:i:s'));
    $addTime = '1 days';
    date_add($lastDate, date_interval_create_from_date_string($addTime));
    if($lastDate < $nowDate){
        $cikti = "addedTime < nowDate";
    }else{
        $cikti = "else";
    }
    $response_data['denmark'] = $cikti;
    $response->write(json_encode($response_data));
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Get Posts

$app->post('/forgetpassword', function (Request $request, Response $response) {
    if(!haveEmptyParameters(array('UserName'), $request, $response)){
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $db = new DbOperations;
        $result = $db->isEmailExist($userName, $userName);
        if($result){
            $forgetPassword = $db->forgetPassword($userName);
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = $forgetPassword;
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }else{
            $response_data['error'] = $result;
            $response_data['message'] = "There is no accout like that";
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->post('/checkuser', function (Request $request, Response $response) {
    if(!haveEmptyParameters(array('UserAction'), $request, $response)){
        $request_data = $request->getParsedBody();
        $userAction = $request_data['UserAction'];
        $db = new DbOperations;
        $result = $db->isEmailExist($userAction, $userAction);
        if($result){
            $response_data['error'] = $result;
            $response_data['message'] = "Not Available";
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }else{
            $response_data['error'] = $result;
            $response_data['message'] = "Available";
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->post('/getfollowing', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $db = new DbOperations;
            $search = $db->getFollowing($userName);
            $response_data = array();
            $response_data['error'] = false;
            $response_data['follower'] = $search;
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Get Posts

$app->post('/getfollower', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $db = new DbOperations;
            $search = $db->getFollower($userName);
            $response_data = array();
            $response_data['error'] = false;
            $response_data['follower'] = $search;
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Get Posts

$app->post('/search', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'Type', 'Content'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $type = $request_data['Type'];
        $content = $request_data['Content'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $db = new DbOperations;
            $search = $db->getSearch($type, $content);
            $response_data = array();
            $response_data['error'] = false;
            $response_data['search'] = $search;
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Get Posts

$app->post('/discover', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        @$categoryID = $request_data['Category'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $db = new DbOperations;
            $posts = $db->getDiscover($categoryID);
            $response_data = array();
            $response_data['error'] = false;
            $response_data['posts'] = $posts;
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Get Posts

$app->post('/admin/category', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('Name', 'Image', 'Locale', 'ID'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $name = $request_data['Name'];
        $image = $request_data['Image'];
        $locale = $request_data['Locale'];
        $id = $request_data['ID'];
        $db = new DbOperations;
        $result = $db->createCategory($name, $image, $locale, $id);
        if ($result) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Success';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Error';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
}); //Update Password

$app->post('/about', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'About'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $about = $request_data['About'];
        $db = new DbOperations;
        $result = $db->updateAbout($userName, $userPassword, $about);
        if ($result == ABOUT_CHANGED) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'About Changed';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == ABOUT_NOT_CHANGED) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Some error occurred';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
}); //Update Password

$app->post('/notifies', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array('UserName', 'UserPassword'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $db = new DbOperations;
            $notifies = $db->getNotifications($userName);
            $response_data = array();
            $response_data['error'] = false;
            $response_data['notifies'] = $notifies;
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Get Posts

$app->post('/usertoken', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('Token'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $token = $request_data['Token'];
        $db = new DbOperations;
        $result = $db->userToken($token);

        if ($result == true) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Success';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);
        } else if ($result == false) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'UnSuccess';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);
        } else if ($result == USER_FAILURE) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'User Denied';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(203);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(400);
}); //Add Comment

$app->post('/reply', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'Reply', 'CommentId'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $reply = $request_data['Reply'];
        $commentID = $request_data['CommentId'];
        $db = new DbOperations;
        $result = $db->reply($userName, $userPassword, $reply, $commentID);

        if ($result == COMMENT_SUCCESS) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Comment successfully';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);
        } else if ($result == COMMENT_UNSUCCESS) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Comment UnSuccess';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);
        } else if ($result == USER_FAILURE) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'User Denied';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(203);
        } else if ($result == POST_NOT_EXIST) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Post Not Found';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(204);
        } else if ($result == WTF) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Something Went Wrong';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(451);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(400);
}); //Add Comment

$app->post('/replyunlike', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'ReplyId'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $replyId = $request_data['ReplyId'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $replyUnLikeResult = $db->replyUnLike($userName, $replyId);
            if ($replyUnLikeResult) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Success';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Unexpected Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //UnLike Reply

$app->post('/replylike', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'ReplyId'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $replyId = $request_data['ReplyId'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $replyLikeResult = $db->replyLike($userName, $replyId);
            if ($replyLikeResult) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Success';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Unexpected Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Like Reply

$app->post('/reply/{id}', function (Request $request, Response $response, array $args) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $commentId = $args['id'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $replies = $db->getReplies($commentId, $userName);
            if ($replies == null) { //Olmayan commentta boş ekran geliyor
                $response_data = array();
                $response_data['error'] = true;
                $response_data['replies'] = $replies;
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(204);
            } else { //İstenilen post varsa
                $response_data = array();
                $response_data['error'] = false;
                $response_data['replies'] = $replies;
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
}); //Get Replies

$app->post('/commentunlike', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'CommentId'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $commentId = $request_data['CommentId'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $postLikeResult = $db->commentUnLike($userName, $commentId);
            if ($postLikeResult) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Success';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Unexpected Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //UnLike Comment

$app->post('/commentlike', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'CommentId'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $commentId = $request_data['CommentId'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $postLikeResult = $db->commentLike($userName, $commentId);
            if ($postLikeResult) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Success';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Unexpected Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Like Comment

$app->post('/postunlike', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'PostId'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $postId = $request_data['PostId'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $postLikeResult = $db->postUnLike($userName, $postId);
            if ($postLikeResult) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Success';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Unexpected Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //UnLike Post

$app->post('/postlike', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'PostId'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $postId = $request_data['PostId'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $postLikeResult = $db->postLike($userName, $postId);
            if ($postLikeResult) {
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Success';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Unexpected Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Like Post

$app->post('/unfollow', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'TargetUserName'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $targetUserName = $request_data['TargetUserName'];
        if (@$userName == $targetUserName) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Good Idea';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else {
            $db = new DbOperations;
            $result = $db->userLogin($userName, $userPassword);
            if ($result == USER_AUTHENTICATED) {
                if ($db->isUserExist($targetUserName)) {
                    $unFollowResult = $db->unFollow($userName, $targetUserName);
                    if ($unFollowResult == UNFOLLOW_SUCCESS) {
                        $response_data = array();
                        $response_data['error'] = false;
                        $response_data['message'] = 'Success';
                        $response->write(json_encode($response_data));
                        return $response
                            ->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
                    } else if ($unFollowResult == UNFOLLOW_ERROR) {
                        $response_data = array();
                        $response_data['error'] = true;
                        $response_data['message'] = 'Error';
                        $response->write(json_encode($response_data));
                        return $response
                            ->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
                    } else if ($unFollowResult == ALREADY_UNFOLLOWING) {
                        $response_data = array();
                        $response_data['error'] = true;
                        $response_data['message'] = 'Already UnFollowing';
                        $response->write(json_encode($response_data));
                        return $response
                            ->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
                    } else {
                        $response_data = array();
                        $response_data['error'] = true;
                        $response_data['message'] = 'Unexpected Error';
                        $response->write(json_encode($response_data));
                        return $response
                            ->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
                    }
                } else {
                    $response_data = array();
                    $response_data['error'] = true;
                    $response_data['message'] = 'Target User not exist';
                    $response->write(json_encode($response_data));
                    return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
                }
            } else if ($result == USER_NOT_FOUND) {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'User not exist';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Invalid credential';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //UnFollow User

$app->post('/follow', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'TargetUserName'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $targetUserName = $request_data['TargetUserName'];
        if (@$userName == $targetUserName) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Good Idea';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else {
            $db = new DbOperations;
            $result = $db->userLogin($userName, $userPassword);
            if ($result == USER_AUTHENTICATED) {
                if ($db->isUserExist($targetUserName)) {
                    $followResult = $db->follow($userName, $targetUserName);
                    if ($followResult == FOLLOW_SUCCESS) {
                        $response_data = array();
                        $response_data['error'] = false;
                        $response_data['message'] = 'Success';
                        $response->write(json_encode($response_data));
                        return $response
                            ->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
                    } else if ($followResult == FOLLOW_ERROR) {
                        $response_data = array();
                        $response_data['error'] = true;
                        $response_data['message'] = 'Error';
                        $response->write(json_encode($response_data));
                        return $response
                            ->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
                    } else if ($followResult == ALREADY_FOLLOWING) {
                        $response_data = array();
                        $response_data['error'] = true;
                        $response_data['message'] = 'Already Following';
                        $response->write(json_encode($response_data));
                        return $response
                            ->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
                    } else {
                        $response_data = array();
                        $response_data['error'] = true;
                        $response_data['message'] = 'Unexpected Error';
                        $response->write(json_encode($response_data));
                        return $response
                            ->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
                    }
                } else {
                    $response_data = array();
                    $response_data['error'] = true;
                    $response_data['message'] = 'Target User not exist';
                    $response->write(json_encode($response_data));
                    return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
                }
            } else if ($result == USER_NOT_FOUND) {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'User not exist';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Invalid credential';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Follow User

$app->post('/profile', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'TargetUserName'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $username = $request_data['UserName'];
        $userpassword = $request_data['UserPassword'];
        $targetUser = $request_data['TargetUserName'];
        $db = new DbOperations;
        $result = $db->userLogin($username, $userpassword);
        if ($result == USER_AUTHENTICATED) {
            if ($db->isUserExist($targetUser)) {
                $response_data = array();
                $response_data = $db->getProfile($username, $targetUser);
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'User not exist';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(400);
}); //Get Profile //image updated

$app->post('/deneme2', function (Request $request, Response $response) {
    $request_data = $request->getParsedBody();
    @$options = $request_data['Options'];
    $response_data = array();
    $response_data['error'] = false;
    $response_data['message'] = array_filter($options);
    $response->write(json_encode($response_data));
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Updated

$app->post('/addpost', function (Request $request, Response $response) {

    if (!haveEmptyParametersAndOptions(array('UserName', 'UserPassword', 'Question', 'Description', 'CategoryID'), $request, $response, 5)) { //Farklı bir boşluk kontrol metodu var

        $options = array();

        $request_data = $request->getParsedBody();

        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $question = $request_data['Question'];
        $description = $request_data['Description'];
        $categoryIDRaw = $request_data['CategoryID'];
        $explodedCategoryID = explode(",", $categoryIDRaw);
        $categoryID = array_filter($explodedCategoryID);
        @$options_raw = $request_data['Options'];
        @$options = array_filter($options_raw); //Empty array elemanlarını siliyor

        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) { //Giriş başarılıysa
            $addPostResult = $db->addPost($userName, $question, $description, $options, $categoryID);
            if ($addPostResult) { //Post işlemi başarılıysa
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Successful';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            } else { //Post ekleme işlemi başarısızsa
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Error';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
}); //Add Post

$app->post('/deneme', function (Request $request, Response $response) {
    $better_token = md5(uniqid(mt_rand(), true));
    $response_data = array();
    $response_data['error'] = false;
    $response_data['message'] = $better_token;
    $response->write(json_encode($response_data));
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Updated

$app->post('/image', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('Image', 'UserName', 'UserPassword'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $image = $request_data['Image'];
        $username = $request_data['UserName'];
        $userpassword = $request_data['UserPassword'];
        $db = new DbOperations;
        $result = $db->userLogin($username, $userpassword);
        if ($result == USER_AUTHENTICATED) {
            $better_token = md5(uniqid(mt_rand(), true));
            $image_name = $better_token . ".png";
            $decoded_image = base64_decode($image);
            $path = '../images/' . $image_name;
            if (!file_exists($path)) { //Dosya var mı kontrolü
                $file = fopen($path, 'wb'); //wb nin anlamı binary cinsinden bir şey yazıcaz
                $is_written = fwrite($file, $decoded_image);
                fclose($file);
                if ($is_written) { //Dosya oluşturuldu mu kontrolü
                    /* Veritabanı kodları */
                    $beautyImagePath = "/images/" . $image_name;
                    $userImage = $db->userImage($username, $beautyImagePath);
                    if ($userImage) { //Veri tabanı işlemi kontrolü
                        //$image_site_url = 'http://localhost/raterfield/images/';
                        $image_site_url = 'https://gulfilosu.com/images/';
                        $response_data = array();
                        $response_data['error'] = false;
                        $response_data['message'] = $image_site_url . $image_name;
                        $response->write(json_encode($response_data));
                        return $response
                            ->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
                    } else { //Veri tabanındanı başarısızsa
                        $response_data = array();
                        $response_data['error'] = true;
                        $response_data['message'] = "There is some error from db";
                        $response->write(json_encode($response_data));
                        return $response
                            ->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
                    }
                } else { //Dosya Oluşturulmadıysa
                    $response_data = array();
                    $response_data['error'] = true;
                    $response_data['message'] = "Upload Not Success";
                    $response->write(json_encode($response_data));
                    return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
                }
            } else { //Dosya varsa
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = "Already Exists";
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Updated

$app->post('/category', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('Locale'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $locale = $request_data['Locale'];
        $db = new DbOperations;
        $categories = $db->getCategory($locale);
        $response_data = array();
        $response_data['error'] = false;
        $response_data['category'] = $categories;
        $response->write(json_encode($response_data));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(400);
}); //Updated

$app->post('/userpost', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array('UserName', 'UserPassword'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $db = new DbOperations;
            $posts = $db->userpost($userName);
            $response_data = array();
            $response_data['error'] = false;
            $response_data['posts'] = $posts;
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Updated

$app->post('/vote', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'OptionID'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $optionID = $request_data['OptionID'];
        $db = new DbOperations;
        $result = $db->vote($userName, $userPassword, $optionID);

        if ($result == OPTION_SUCCESS) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Option successfully';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);
        } else if ($result == OPTION_NOT_SUCCESS) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Some error occurred';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == OPTION_NOT_EXIST) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Option Not Available';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_FAILURE) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'User Denied';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_CANT_VOTE) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'User Cant Vote';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(400);
}); //Updated

$app->post('/comment', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'Comment', 'PostID'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $comment = $request_data['Comment'];
        $postID = $request_data['PostID'];
        $db = new DbOperations;
        $result = $db->comment($userName, $userPassword, $comment, $postID);

        if ($result == COMMENT_SUCCESS) {
            $message = array();
            $message['error'] = false;
            $message['message'] = 'Comment successfully';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(201);
        } else if ($result == COMMENT_UNSUCCESS) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Comment UnSuccess';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);
        } else if ($result == USER_FAILURE) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'User Denied';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(203);
        } else if ($result == POST_NOT_EXIST) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Post Not Found';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(204);
        } else if ($result == WTF) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Something Went Wrong';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(451);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(400);
}); //Add Comment

$app->post('/comment/{id}', function (Request $request, Response $response, array $args) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword'), $request, $response)) {
    $request_data = $request->getParsedBody();
    $userName = $request_data['UserName'];
    $userPassword = $request_data['UserPassword'];
    $postID = $args['id'];
    $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $comment = $db->getComment($userName, $postID);
            if ($comment == null) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['comments'] = $comment;
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(204);
        } else { //İstenilen post varsa
            $response_data = array();
            $response_data['error'] = false;
            $response_data['comments'] = $comment;
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Get Comments

$app->post('/postdetails/{id}', function (Request $request, Response $response, array $args) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $postID = $args['id'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $postStatus = $db->isPostDeleted($postID);
            if($postStatus == "Active"){
                $post = $db->postDetails($postID, $userName);
                if ($post == null) {
                    $response_data = array();
                    $response_data['error'] = true;
                    $response_data['posts'] = $post;
                    $response->write(json_encode($response_data));
                    return $response
                        ->withHeader('Content-type', 'application/json')
                         ->withStatus(200);
                } else { //İstenilen post varsa
                    $response_data = array();
                    $response_data['error'] = false;
                    $response_data['posts'] = $post;
                    $response->write(json_encode($response_data));
                    return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(200);
                }
            }else if($postStatus == "Passive"){
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = "Deleted";
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }else{
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = "Some Error Occured";
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
}); //Get Post Details

$app->post('/post', function (Request $request, Response $response) {

    if (!haveEmptyParameters(array('UserName', 'UserPassword'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $db = new DbOperations;
            $posts = $db->post($userName);
            $response_data = array();
            $response_data['error'] = false;
            $response_data['posts'] = $posts;
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Get Posts

$app->post('/createuser', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'UserFirstName', 'UserLastName', 'UserEmail', 'UserCategory', 'UserToken'), $request, $response)) {
        $request_data = $request->getParsedBody();
        //UserName
        $userName = $request_data['UserName'];
        $userName = trim(preg_replace('/\s+/', '', $userName));
        $userName = strtolower($userName);
        //UserEmail
        $userEmail = $request_data['UserEmail'];
        $userEmail = trim(preg_replace('/\s+/', '', $userEmail));
        $userEmail = strtolower($userEmail);
        //UserPassword
        $userPassword = $request_data['UserPassword'];
        if(preg_match("/^[a-z0-9_.]+$/", $userName) == 1 && filter_var($userEmail, FILTER_VALIDATE_EMAIL) && (strlen($userName) >= 4 && strlen($userName) <= 25) && (strlen($userPassword) >= 6 && strlen($userPassword) <= 150)) {
            // string only contain the a to z , 0 to 9, _, .
            $userFirstName = $request_data['UserFirstName'];
            $userLastName = $request_data['UserLastName'];
            $userToken = $request_data['UserToken'];
            $hash_password = password_hash($userPassword, PASSWORD_DEFAULT);
            $db = new DbOperations;
            $userCreateResult = $db->createUser($userName, $hash_password, $userFirstName, $userLastName, $userEmail, $userToken);

            if ($userCreateResult == USER_CREATED) {
                $userCategory = $request_data['UserCategory'];
                $explodedUserCategory = explode(",", $userCategory);
                $categoryCreateResult = $db->userCategory($userName, $explodedUserCategory);
                if ($categoryCreateResult) {
                    $user = $db->getUserByEmailOrUserName($userName);
                    $message = array();
                    $message['error'] = false;
                    $message['message'] = 'User created successfully';
                    $message['user'] = $user;
                    $response->write(json_encode($message));
                    return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
                } else {
                    $message = array();
                    $message['error'] = true;
                    $message['message'] = 'There is a error from category';
                    $response->write(json_encode($message));
                    return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
                }
            } else if($userCreateResult == USER_TOKEN_FAILURE){
                $message = array();
                $message['error'] = false;
                $message['message'] = 'Token Failed';
                $response->write(json_encode($message));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
            } else if ($userCreateResult == USER_FAILURE) {
                $message = array();
                $message['error'] = true;
                $message['message'] = 'Some error occurred';
                $response->write(json_encode($message));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(422);
            } else if ($userCreateResult == USER_EXISTS) {
                $message = array();
                $message['error'] = true;
                $message['message'] = 'User Already Exists';
                $response->write(json_encode($message));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(451);
            }
        }else{ // Kullanıcı adı
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Invalid Credentials';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(451);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(400);
}); //Create User

$app->post('/userlogin', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'UserToken'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $userToken = $request_data['UserToken'];

        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            $userId = $db->getUserIDByUserName($userName);
            $logAction = "login"; //Constants a bağlanacak
            $db->v1Log($userId, $logAction);
            if($db->setUserToken($userToken, $userName)){
                $user = $db->getUserByEmailOrUserName($userName);
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Login Successful';
                $response_data['user'] = $user;
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }else{
                $user = $db->getUserByEmailOrUserName($userName);
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = 'Login Successful But Token Failed';
                $response_data['user'] = $user;
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if($result == USER_TOKEN_FAILURE){
            $user = $db->getUserByEmailOrUserName($userName);
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Login Successful but Token Failed';
            $response_data['user'] = $user;
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Login User

$app->get('/allusers', function (Request $request, Response $response) {
    $db = new DbOperations;
    $users = $db->getAllUsers();
    $response_data = array();
    $response_data['error'] = false;
    $response_data['users'] = $users;
    $response->write(json_encode($response_data));
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->post('/updateuser/{id}', function (Request $request, Response $response, array $args) {
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'FirstName', 'LastName', 'About', 'Email'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userId = $args['id'];
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $firstName = $request_data['FirstName'];
        $lastName = $request_data['LastName'];
        $about = $request_data['About'];
        $email = $request_data['Email'];
        $db = new DbOperations;
        $result = $db->userLogin($userName, $userPassword);
        if ($result == USER_AUTHENTICATED) {
            if($userId == $db->getUserIDByUserName($userName)){
                $db = new DbOperations;
                $updateUser = $db->updateUser($userId ,$firstName, $lastName, $about, $email); //5 Parameters
                $response_data = array();
                $response_data['error'] = false;
                $response_data['message'] = $updateUser;
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }else{ //Verilen id ile kullanıcı adından alınan id uyuşmuyor, büyük ihtimal apiyi çözen birisi bunu denedi
                $response_data = array();
                $response_data['error'] = true;
                $response_data['message'] = 'Access Denied';
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(200);
            }
        } else if ($result == USER_NOT_FOUND) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == USER_PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->put('/updatepassword', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('currentpassword', 'newpassword', 'email'), $request, $response)) {

        $request_data = $request->getParsedBody();
        $currentpassword = $request_data['currentpassword'];
        $newpassword = $request_data['newpassword'];
        $email = $request_data['email'];
        $db = new DbOperations;
        $result = $db->updatePassword($currentpassword, $newpassword, $email);
        if ($result == PASSWORD_CHANGED) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'Password Changed';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == PASSWORD_DO_NOT_MATCH) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'You have given wrong password';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else if ($result == PASSWORD_NOT_CHANGED) {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Some error occurred';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);
}); //Update Password

$app->delete('/deleteuser/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $db = new DbOperations;
    $response_data = array();
    if ($db->deleteUser($id)) {
        $response_data['error'] = false;
        $response_data['message'] = 'User has been deleted';
    } else {
        $response_data['error'] = true;
        $response_data['message'] = 'Plase try again later';
    }
    $response->write(json_encode($response_data));
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
}); //Delete User

function haveEmptyParameters($required_params, $request, $response){
    $error = false;
    $error_params = '';
    $request_params = $request->getParsedBody();
    foreach ($required_params as $param) {
        if (!isset($request_params[$param]) || strlen($request_params[$param]) <= 0) {
            $error = true;
            $error_params .= $param . ', ';
        }
    }
    if ($error) {
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error;
}

function haveEmptyParametersAndOptions($required_params, $request, $response, $optionsNumber){
    $error = false;
    $error_params = '';
    $counter = 0;
    $request_params = $request->getParsedBody();
    @$options_raw = $request_params['Options'];
    @$options = array_filter($options_raw); //Empty array elemanlarını siliyor
    foreach ($required_params as $param) {
        if (!isset($request_params[$param]) || strlen($request_params[$param]) <= 0) {
            $error = true;
            $error_params .= $param . ', ';
        }
    }
    /*for ($i = 1; $i <= $optionsNumber; $i++) {
        if (@isset($request_params['Option' . $i]) || @strlen($request_params['Option' . $i]) > 0) {
            $counter++;
        }*/
    if (is_array($options)) {
        foreach ($options as $option) {
            if (isset($option) || strlen($option) > 0) {
                $counter++;
            }
        }
    }
    if ($error && $counter < 2) { //İki koşulda hataysa
        $error = true;
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ', Options are missing or empty';
        $response->write(json_encode($error_detail));
    } elseif ($error) { //Parametre Kontrolü
        $error = true;
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    } elseif ($counter < 2) { //Seçenekler Kontrolü
        $error = true;
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Required options are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error;
}

$app->run();
