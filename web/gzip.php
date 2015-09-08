<?php
$baseDIR = __DIR__;
$requestFile = $baseDIR . DIRECTORY_SEPARATOR .'skins'. $_REQUEST['file'];

if (!file_exists($requestFile)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}
if (preg_match("/\\.(?:css)$/ui", $requestFile)) {
    header('Content-Type: text/css');
} else {
    header('Content-Type: application/javascript');
}
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24)));
ob_start("ob_gzhandler");
@readfile($requestFile);
