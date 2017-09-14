<?php

include('config.inc.php');

$a_data = getPath('lib');
print_pre($a_data);
$s_filename = PATH.'lib/setting.json';
$file = fopen($s_filename, 'w');
fwrite($file, json_encode($a_data, JSON_FORCE_OBJECT));
fclose($file);

$a_data = getPath('api');
$s_filename = PATH.'api/setting.json';
$file = fopen($s_filename, 'w');
fwrite($file, json_encode($a_data, JSON_FORCE_OBJECT));
fclose($file);
print_pre($a_data);
exit;

?>