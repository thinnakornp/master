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

if(is_file('api/'.$s_name.'/'.$request->service->name.'.sql'))
{
  $o_template = new sysTemplate();
  $o_template->load('api/'.$s_name.'/'.$request->service->name.'.sql');
  foreach((array)$request->params as $_key=>$_value)
  {
    $o_template->setValue($_key, $_value);
  }
  $query = $o_template->getTemplate();
  $a_data = daoConnect::getRow($query);
  $a_return = ($a_data)?$a_data:array('error'=>'error');
}
else $a_return = array('error'=>'service not found.');

switch(strtolower($request->service->type))
{
   case 'excel' :
    $data_head = array_flip($a_return[0]);
    array_unshift($a_return, $data_head);
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getActiveSheet()->fromArray($a_return, null, 'A1');
    $objPHPExcel->getActiveSheet()->setTitle('Data');
    for ($i = 'A'; $i !=  $objPHPExcel->getActiveSheet()->getHighestColumn(); $i++) {
      $objPHPExcel->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
    }

   $objPHPExcel->getActiveSheet()->getStyle(
    'A1:' .
    $objPHPExcel->getActiveSheet()->getHighestColumn() .
    $objPHPExcel->getActiveSheet()->getHighestRow()
    )->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('temp/data.xlsx');
//    $objWriter->save('php://output');
  break;
  case 'csv' :
    $data_head = array_flip($a_return[0]);
    array_unshift($a_return, $data_head);
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getActiveSheet()->fromArray($a_return, null, 'A1');
    $objPHPExcel->getActiveSheet()->setTitle('Data');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    $objWriter->save('temp/data.csv');
  break;
  case 'json' : echo json_encode($a_return); break;
  case 'xml' :
    $o_xml = new sysXML();
    $o_xml->setResults($a_return);
    echo $o_xml->getXML();
  break;
  case 'rss' : break;
  case 'html' : break;
}

exit;

?>