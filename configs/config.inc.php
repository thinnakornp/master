<?php
error_reporting (E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
//error_reporting (E_ALL);
date_default_timezone_set ('Asia/Bangkok');
//ini_set('mbstring.internal_encoding', 'UTF-8');

if(!headers_sent())
{
  session_cache_limiter("must-revalidate");
  session_start();
}
//header("Content-type: text/html; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("X-Frame-Options: SAMEORIGIN");

DEFINE('SYSTEM_CACHE', true);
$_debug = ($_GET['debug'])?$_GET['debug']:false;
DEFINE('SYSTEM_ADODB_DEBUG', $_debug);

DEFINE('SYSTEM_PLAY', 100);
//DEFINE('SYSTEM_ADODB_DEBUG', true);

include(PATH.'vendor/autoload.php');

include(PATH.'configs/function.inc.php');

include(PATH.'api/api.inc.php');
include(PATH.'lib/lib.inc.php');
//Plugin
//include_once(PATH.'plugin/adodb5/adodb.inc.php');
//include_once(PATH.'plugin/Classes/PHPExcel.php');
//include_once(PATH.'plugin/nusoap/lib/nusoap.php');
?>