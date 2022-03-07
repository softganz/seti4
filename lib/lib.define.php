<?php

/**
* SOFTGANZ :: define
*
* Copyright (c) 2000-2002 The SoftGanz Group By Panumas Nontapan
* Authors : Panumas Nontapan <webmaster@softganz.com>
*             : http://www.softganz.com/
* ============================================
* This module is to ....................
*
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================
*/

/**************************
define
***************************/
define('_STRIPSLASHES',1);
define('_ADDSLASHES',2);
define('_HTMLSPECIALCHARS',4);
define('_TRIM',8);
define('_NEWLINE_TO_BR',16);
define('_KEYTOLOWER',32);
define('_KEYTOUPPER',64);
define('_URLENCODE',128);
define('_STRIPTAG',256);
define('_REMOVEEMPTY',512);

define('_TEXT_SEPARATOR',',');

define('_CONFIRM_VALUE', 'yes');

define('_GET_KEY_STRING',1);
define('_GET_KEY_NO',2);
define('_GET_KEY_ALL',3);

define('_MULTIPLE_TEMPLATE_TYPE',1);
define('_SINGLE_TEMPLATE_TYPE',2);
define('_HTML_TEMPLATE_TYPE',4);

define('_AUTH_OWNER',2);

define('_NL',"\r\n");

define('FUNCTION_PATTERN','[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*');

define('_self',$_SERVER['PHP_SELF']);

define('_IBUY_STICKY',252);
define('_BLOG_STICKY',253);
define('_HOME_STICKY',254);
define('_CATEGORY_STICKY',255);

define('_REJECT',-2);
define('_CANCEL',-1);
define('_START',0);
define('_DRAFT',1);
define('_PUBLISH',2);
define('_WAITING',3);
define('_BLOCK',4);
define('_LOCK',5);
define('_LOCKDETAIL',11);
define('_COMPLETE',100);


define('_COMMENT_NO',0);
define('_COMMENT_READ',1);
define('_COMMENT_READWRITE',2);

define('_MEMBER',127);
define('_MEMBER_MANAGER',1);
define('_MEMBER_OWNER',2);
define('_MEMBER_DOCTOR',101);
define('_MEMBER_BANNED',-1);
define('_MEMBER_WAITING',-2);

define('_CACHE_URL',_URL.'floodphoto/cache/');
define('_CACHE_FOLDER',cfg('folder.abs').'floodphoto/cache/');

define('_CACHE_URL_OLD',_URL.'file/c/');
define('_CACHE_FOLDER_OLD',cfg('folder.abs').'file/c/');

define('_CHAR_3DOTS','&#8942;');
define('_CHAR_BACKARROW','&#10094;');

define('_IS_ADMIN', 			bindec('0000000000000001'));
define('_IS_OWNER', 			bindec('0000000000000010'));
define('_IS_OFFICER', 		bindec('0000000000000100'));
define('_IS_TRAINER', 		bindec('0000000000001000'));
define('_IS_COMMENTATOR',	bindec('0000000000010000'));
define('_IS_ZONEADMIN', 	bindec('0000000000100000'));
define('_IS_ACCESS', 			bindec('0000000010000000'));
define('_IS_ADDABLE', 		bindec('0000000100000000'));
define('_IS_EDITABLE', 		bindec('0000001000000000'));
define('_IS_EDITDETAIL',	bindec('0000010000000000'));
define('_IS_RIGHT', 			bindec('0000100000000000'));
define('_IS_DELETABLE', 	bindec('0001000000000000'));
define('_IS_SIGNED', 			bindec('0010000000000000'));

define('_JSON_PATTERN_V7',trim('
	/
	\-\-\s.*       # -- space
	(\{            # { character
	    (?:        # non-capturing group
	        [^{}]  # anything that is not a { or }
	        |      # OR
	        (?R)   # recurses the entire pattern
	    )*         # previous group zero or more times
	\})$           # } character
	/x
	')
);

define('_JSON_PATTERN',trim('
	/
	(.*)\;\s.*\-\-\s.*   # -- space
	(\{            # { character
	    (?:        # non-capturing group
	        [^{}]  # anything that is not a { or }
	        |      # OR
	        (?R)   # recurses the entire pattern
	    )*         # previous group zero or more times
	\})$           # } character
	/xsU
	')
);

define('_HEADER_BACK', '<nav class="nav -back"><a class="sg-action" data-rel="back"><i class="icon -material">arrow_back</i></a></nav>');

define('_HTTP_OK', 200);
define('_HTTP_OK_NO_CONTENT', 204);	// NO CONTENT SEND TO BROWSER!!!
define('_HTTP_ERROR_BAD_REQUEST', 400); // Bad Request
define('_HTTP_ERROR_UNAUTHORIZED', 401); // Unauthorized : Not sign in
define('_HTTP_ERROR_FORBIDDEN', 403); // Forbidden : Sign in ready but no right
define('_HTTP_ERROR_NOT_FOUND', 404); // Page Not Found
define('_HTTP_ERROR_NOT_ALLOWED', 405); // Method Not Allowed : Page found but request not allow
define('_HTTP_ERROR_NOT_ACCEPTABLE', 406); // Not Acceptable : Page found but data incomplete
define('_HTTP_ERROR_TIMEOUT', 408); // Request Time-out
define('_HTTP_ERROR_UNSUPPORT', 415); // Unsupported Media Type : Wrong media type on upload
?>