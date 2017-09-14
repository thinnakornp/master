<?php
DEFINE('PATH', '../../');
// TODO: logs

include('../config.inc.php');
include('connect.inc.php');

header("Access-Control-Allow-Origin: *");
$s_name = 'getData';

$s_json = file_get_contents("php://input");
//$request = json_decode($s_json, true);
$request = json_decode($s_json, false);


?>