<?php

$s_json = file_get_contents(PATH.'lib/setting.json');
$a_json = json_decode($s_json, true);

foreach((array) $a_json as $item)
{
  include($item);
}
/*
include('dao/daoConnect.class.php');
include('dao/daoConnectDatabase.class.php');
include('database/databaseMSSQL.class.php');
include('database/databaseMySQL.class.php');
include('database/databaseOracle.class.php');
include('database/databaseSchema.class.php');
include('sys/sys.class.php');
include('sys/sysBrowser.class.php');
include('sys/sysCache.class.php');
include('sys/sysFile.class.php');
include('sys/sysPage.class.php');
include('sys/sysTemplate.class.php');
include('sys/sysXML.class.php');
*/
?>