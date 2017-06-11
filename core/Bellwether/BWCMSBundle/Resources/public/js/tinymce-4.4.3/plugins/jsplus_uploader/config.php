<?php

// Absolute URL to upload folder via HTTP.
// Will affect to client (JS) part of plugins.
// By default script is configured to automatically detect it.
// If you want to change it, do it like this:
// $config['BaseUrl'] = 'http://yoursite.com/ckeditor_or_tinymce/plugins/jsplus_uploader/userfiles/';
$config['BaseUrl'] = preg_replace('/(uploader\.php.*)/', 'userfiles/', $_SERVER['PHP_SELF']);

// Absolute or relative path to directory on the server where uploaded files will be stored.
// Used by this PHP script only.
// By default it automatically detects the directory.
// You can change it, see this example:
// $config['BaseDir'] = "/var/www/ckeditor_or_tinymce/jsplus_uploader/userfiles/";
$config['BaseDir'] = dirname(__FILE__).'/userfiles/';

$config['ResourceType']['Files'] = Array(
		'maxSize' => 0, 			// maxSize in bytes for uploaded files, 0 for any
		'allowedExtensions' => '*' 	// means any extensions are allowed
);

$config['ResourceType']['Images'] = Array(
		'maxSize' => 16*1024*1024, 	// maxSize in bytes for uploaded images, 0 for any
		'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
);

$config['JPEGQuality'] = 95; // Will be used for resizing JPEG images

// Some restrictions to avoid server overload / DDoS
$config['MaxImgResizeWidth'] = 2000; 
$config['MaxImgResizeHeight'] = 2000;
$config['MaxThumbResizeWidth'] = 500;
$config['MaxThumbResizeHeight'] = 500;

$config['AllowExternalWebsites'] = ''; // Crossdomain upload is disabled by default
// If you want to enable it use this code:
// $config['AllowExternalSites'] = 'http://yoursite.com';
// or to allow all websites (with caution!):
// $config['AllowExternalWebsites'] = '*';



if (substr($config['BaseUrl'], -1) !== '/')
	$config['BaseUrl'] .= '/';
if (substr($config['BaseDir'], -1) !== '/' && substr($config['BaseDir'], -1) !== '\\')
	$config['BaseDir'] .= '/';

?>