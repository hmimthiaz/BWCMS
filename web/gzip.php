<?php
$baseDIR = __DIR__;
$requestFile = $baseDIR . DIRECTORY_SEPARATOR . 'skins' . $_REQUEST['file'];
$requestFile = str_replace('..', '', $requestFile);
$requestFile = str_replace('//', '/', $requestFile);
$requestFile = str_replace('//', '/', $requestFile);
$requestFile = str_replace('//', '/', $requestFile);
$requestFile = str_replace('\\', '\\', $requestFile);

if (!file_exists($requestFile)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

$fileExtension = strtolower(pathinfo($requestFile, PATHINFO_EXTENSION));
if ($fileExtension=='css') {
    header('Content-Type: text/css');
} else if ($fileExtension=='js') {
    header('Content-Type: application/javascript');
} else {
    header("HTTP/1.0 404 Not Found");
    exit;
}

header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24)));
ob_start("ob_gzhandler");
@readfile($requestFile);
