<?php
DEFINE('SYS_PATH', '../../');
include(PATH.'config/proc.inc.php');
//daoConnect::setConnect('db_41_ilove', 'mysql', '127.0.0.1', 'mculture', '3culture@dm!n', 'db_41_ilove', 'db_41_ilove');
daoConnect::setConnect('football', 'mysqli', '127.0.0.1', 'root', null, 'football', 'football');
//daoConnect::setConnect('demo2', 'mysqli', '127.0.0.1', 'root', null, 'demo2', 'demo2');
//daoConnect::setConnect('etax_test', 'mysqli', '127.0.0.1', 'root', null, 'etax_test', 'etax_test');
daoConnect::execute("set names 'utf8'");


?>