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

$app->post('/denmark', function (Request $request, Response $response) {
    $request_data = $request->getParsedBody();
    $asd = $request_data['Content'];
    /*$categoryIDRaw = $request_data['CategoryID'];
    $explodedCategoryID = explode(",", $categoryIDRaw);
    $categoryID = array_filter($explodedCategoryID);
    $response_data = array();
    $response_data['error'] = false;
    $response_data['Locale'] = count($categoryID);*/
    $response_data['Content'] = array("%$asd%");
    $response->write(json_encode($response_data));
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

$app->post('/notify123123', function (Request $request, Response $response) {
    if (!haveEmptyParameters(array('from', 'to'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $from = $request_data['from'];
        $to = $request_data['to'];
        $notify = new PushNotification;
        $result = $notify->followNotification($from, $to);
        echo $result;
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(400);
}); //Add Comment

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
}); //Get Profile

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
                    $userImage = $db->userImage($username, $path);
                    if ($userImage) { //Veri tabanı işlemi kontrolü
                        $response_data = array();
                        $response_data['error'] = false;
                        $response_data['message'] = $image_name;
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
        ->withStatus(400);
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
    if (!haveEmptyParameters(array('UserName', 'UserPassword', 'PostID', 'OptionName'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $postID = $request_data['PostID'];
        $optionName = $request_data['OptionName'];
        $db = new DbOperations;
        $result = $db->vote($userName, $userPassword, $postID, $optionName);

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
                ->withStatus(422);
        } else if ($result == OPTION_NOT_EXIST) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'Option Not Available';
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
                ->withStatus(451);
        } else if ($result == USER_CANT_VOTE) {
            $message = array();
            $message['error'] = true;
            $message['message'] = 'User Cant Vote';
            $response->write(json_encode($message));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(451);
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
            $post = $db->postDetails($postID, $userName);
            if ($post == null) {
                $response_data = array();
                $response_data['error'] = true;
                $response_data['posts'] = $post;
                $response->write(json_encode($response_data));
                return $response
                    ->withHeader('Content-type', 'application/json')
                    ->withStatus(204);
            } else { //İstenilen post varsa
                $response_data = array();
                $response_data['error'] = false;
                $response_data['posts'] = $post;
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
        $userName = $request_data['UserName'];
        $userPassword = $request_data['UserPassword'];
        $userEmail = $request_data['UserEmail'];
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

$app->put('/updateuser/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    if (!haveEmptyParameters(array('email', 'name', 'school'), $request, $response)) {
        $request_data = $request->getParsedBody();
        $email = $request_data['email'];
        $name = $request_data['name'];
        $school = $request_data['school'];

        $db = new DbOperations;
        if ($db->updateUser($email, $name, $school, $id)) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = 'User Updated Successfully';
            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user;
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        } else {
            $response_data = array();
            $response_data['error'] = true;
            $response_data['message'] = 'Please try again later';
            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user;
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
