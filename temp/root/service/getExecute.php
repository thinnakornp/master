<?php
DEFINE('PATH', '../../');
// TODO: logs

include('../config.inc.php');
include('connect.inc.php');

header("Access-Control-Allow-Origin: *");
$s_name = 'getExecute';

$s_json = file_get_contents("php://input");
//$request = json_decode($s_json, true);
$request = json_decode($s_json, false);

if(is_file('api/'.$s_name.'/'.$request->service->name.'.sql'))
{
  $o_template = new sysTemplate();
  $o_template->load('api/'.$s_name.'/'.$request->service->name.'.sql');
  foreach((array)$request->params as $_key=>$_value)
  {
    $o_template->setValue($_key, $_value);
  }
  $query = $o_template->getTemplate();
//    preg_match( "/insert into/i" , $query, $res);
  $res = daoConnect::execute($query);
  if($request->service->insert_id && $res)
  {
    $id = daoConnect::getInsertID($request->service->insert_id->table, $request->service->insert_id->field);
    $a_return = array('id'=>$id);
  }
  else $a_return = array('data'=>$res);
}
else $a_return = array('error'=>'service not found.');

switch(strtolower($request->service->type))
{
  case 'excel' : break;
  case 'csv' : break;
  case 'json' : echo json_encode($a_return); break;
  case 'xml' :
    $o_xml = new sysXML();
    $o_xml->setResults($a_return);
    echo $o_xml->getXML();
  break;
  case 'rss' : break;
  case 'html' : break;
  default: echo json_encode($a_return);
}
exit;

?>