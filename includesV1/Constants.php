<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'prone');

define('WTF', 002);
define('USER_CREATED', 101);
define('USER_EXISTS', 102);
define('USER_FAILURE', 103); 
define('USER_CANT_VOTE', 104);
define('USER_TOKEN_FAILURE', 105);
define('POST_REPORTED', 106);
define('USER_CANT_REPORT', 107);
define('USER_AUTHENTICATED', 201);
define('USER_NOT_FOUND', 202); 
define('USER_PASSWORD_DO_NOT_MATCH', 203);
define('PASSWORD_CHANGED', 301);
define('PASSWORD_DO_NOT_MATCH', 302);
define('PASSWORD_NOT_CHANGED', 303);
define('ABOUT_CHANGED', 304);
define('ABOUT_NOT_CHANGED', 305);
define('COMMENT_SUCCESS', 401);
define('COMMENT_UNSUCCESS', 402);
define('POST_EXIST', 501);
define('POST_NOT_EXIST', 502);
define('COMMENT_EXIST', 503);
define('COMMENT_NOT_EXIST', 504);
define("OPTION_SUCCESS", 601);
define('OPTION_NOT_SUCCESS', 602);
define('OPTION_NOT_EXIST', 603);
define('FOLLOW_SUCCESS', 701);
define('UNFOLLOW_SUCCESS', 702);
define('ALREADY_FOLLOWING', 703);
define('ALREADY_UNFOLLOWING', 704);
define('FOLLOW_ERROR', 705);
define('UNFOLLOW_ERROR', 706);