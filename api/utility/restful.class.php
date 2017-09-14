<?php

class restful {
  static $_type = array (
    'json',
    'xml',
    'excel',
    'csv',
    'pdf',
    'html'
  );

  static $_func = array (
    'getData',
    'getDataList',
    'getExecute',
    'getOption'
  );

  public static function saveExcel($a_data, $filename='temp', $fieldname=false, $sheetname='data')
  {
    if(is_array($a_data))
    {
      $data_head = array_flip($a_data[0]);
      array_unshift($a_return, $data_head);
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->getActiveSheet()->fromArray($a_data, null, 'A1');
      $objPHPExcel->getActiveSheet()->setTitle($sheetname);
      for ($i = 'A'; $i !=  $objPHPExcel->getActiveSheet()->getHighestColumn(); $i++) {
        $objPHPExcel->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
      }
      $objPHPExcel->getActiveSheet()->getStyle(
        'A1:' .
        $objPHPExcel->getActiveSheet()->getHighestColumn() .
        $objPHPExcel->getActiveSheet()->getHighestRow()
      )->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('temp/'.$filename.'.xlsx');
      return true;
    }
    return false;
  }

  public static function call($service, $request)
  {
    if(is_file('api/'.$service.'/'.$request->service->name.'.sql'))
    {
      $o_template = new sysTemplate();
      $o_template->load('api/'.$service.'/'.$request->service->name.'.sql');
      foreach((array)$request->params as $_key=>$_value)
      {
        $o_template->setValue($_key, $_value);
      }
      $query = $o_template->getTemplate();
      switch($service)
      {
        case 'getData' : $a_data = daoConnect::getRow($query); break;
        case 'getDataList' : $a_data = daoConnect::getArray($query); break;
        case 'getExecute' :
          $res = daoConnect::execute($query);
          if($request->service->insert_id && $res)
          {
            $id = daoConnect::getInsertID($request->service->insert_id->table, $request->service->insert_id->field);
            $a_data = array('id'=>$id);
          }
          else $a_data = array('data'=>$res);
        break;
      }

      if($a_data){
        $a_return = array('status'=>1, 'message'=>'connect success', 'response'=> $a_data);
      }else{
        $a_return = array('status'=>1, 'message'=>'No data');
      }
    }
    else if($service=='getOption'){
      $a_return = array('status'=>1, 'message'=>'connect success');
    }
    else
    {
      $a_return = array('status'=>0, 'message'=>'service not found');
    }

    if($request->option->option_name)
    {
      if(is_file('function/'.$request->option->option_name.'.php'))
      {
        include('function/'.$request->option->option_name.'.php');
        eval('$data = '.$request->option->option_name.'("'.implode('","', $request->option->param).'");');
        $a_return['option'] = $data;
      }else{
        $a_return['option'] = 0;
      }
    }

    switch(strtolower($request->service->type))
    {
      case 'excel' :
        $data_head = array_flip($a_return['response'][0]);
        array_unshift($a_return['response'], $data_head);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->fromArray($a_return['response'], null, 'A1');
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
        $data_head = array_flip($a_return['response'][0]);
        array_unshift($a_return['response'], $data_head);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->fromArray($a_return['response'], null, 'A1');
        $objPHPExcel->getActiveSheet()->setTitle('Data');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
        $objWriter->save('temp/data.csv');
      break;
      case 'json' : echo json_encode($a_return); break;
      case 'xml' :
        $o_xml = new sysXML();
        $o_xml->setResults($a_return['response']);
        echo $o_xml->getXML();
      break;
      case 'rss' : break;
      case 'html' : break;
      default : echo json_encode($a_return); break;
    }
    return $a_return;
  }

}

?>