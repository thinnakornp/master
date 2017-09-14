<?php
DEFINE('SYS_PATH', '../../');
include(PATH.'config/proc.inc.php');
daoConnect::setConnect('generator_api', 'mysqli', 'localhost', 'root', null, 'generator_api', 'generator_api');
daoConnect::execute("set names 'utf8'");
?>