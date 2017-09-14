<?php

if (!defined('PATH')) DEFINE('PATH', '../');
/*
DEFINE('SYS_SITE', 'ditp');
DEFINE('SYS_MODULE', 'main');
DEFINE('SYS_PAGE', 'index');
*/
DEFINE('SYS_DATE_FORMAT', 'd/m/Y');
DEFINE('SYS_DATE_FORMAT_FULL', 'd/m/Y H:i');
DEFINE('SYS_LIMIT', 20);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0, proxy-revalidate, no-transform");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include(PATH.'configs/config.inc.php');

/*
if($_GET)
{
  foreach($_GET as $_key=>$_item)
  {
    $_GET[$_key] = htmlspecialchars(strip_tags($_item));
  }
}
*/

?>