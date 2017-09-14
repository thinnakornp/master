<?php
/**
 * DateTime::createFromFormat for PHP version 5.3
 **/

class daoAction
{
  protected $_error = false,
            $_table,
            $_struture,
            $_data = false,
            $_source = false,
            $_key = false,
            $_convert,
            $_type,
            $_insert_id;

  public function insert($s_table, $a_data, $s_convert=true)
  {
    $this->_type = 'insert';
    $this->_convert = $s_convert;
    $this->_table = $s_table;
    $this->_struture = utility::getStructure($this->_table);
    $this->_data = array_intersect_key($a_data, $this->_struture);
    if($this->_data)
    {
      return $this->validate(false);
    }
    else
    {
      $this->_error['system'] = 'No data';
      $this->_error['message'] = 'Error';
      return false;
    }
    return true;
  }

  public function update($s_table, $a_data, $a_key, $s_convert=true)
  {
    $this->_type = 'update';
    $this->_convert = $s_convert;
    $this->_table = $s_table;
    $this->_key = $a_key;
    $a_where = array();
    $this->_struture = utility::getStructure($this->_table);
    foreach((array)$a_key as $_key=>$_item)
    {
      $a_where[] = $_key."='".$_item."'";
    }
    $sql = 'select * from '.$this->_table.' where '.implode(' AND ', $a_where);
    $a_source = daoConnect::getRow($sql);
    $a_intersect_key = array_intersect_key($a_data, $a_source);
    $this->_data = array_diff_assoc($a_intersect_key, $a_source);
    $this->_source = array_intersect_key($a_source, $this->_data);
    if($this->_data)
    {
      return $this->validate(false);
    }
    return true;
  }

  public function delete($s_table, $a_key)
  {
    $this->_type = 'delete';
    $this->_table = $s_table;
    $this->_key = $a_key;
    $a_where = array();
    foreach((array)$a_key as $_key=>$_item)
    {
      $a_where[] = $_key."='".$_item."'";
    }
    $sql = 'select * from '.$this->_table.' where '.implode(' AND ', $a_where);
    $this->_source = daoConnect::getRow($sql);
    if($this->_source)
    {
      return true;
    }
    else
    {
      $this->_error['system'] = 'Not found';
      $this->_error['message'] = 'กรุณาตรวจสอบข้อมูล';
      return false;
    }
  }

  public function save()
  {
    $a_source = array();
    $a_value = array();
    if($this->_type == 'insert')
    {
      foreach((array)$this->_data as $_key=>$_item)
      {
        array_push($a_source, $_key);
        array_push($a_value, "'".get_string_query($_item)."'");
      }
      $_query = 'INSERT INTO '.$this->_table.' ('.implode(', ', $a_source).') VALUES ('.implode(', ', $a_value).')';
    }
    else if($this->_type == 'update')
    {
      foreach((array)$this->_data as $_key=>$_item)
      {
        $a_value[] = $_key."='".get_string_query($_item)."'";
      }
      foreach((array)$this->_key as $_key=>$_item)
      {
        $a_where[] = $_key."='".get_string_query($_item)."'";
      }
      if($a_value)
      $_query = 'UPDATE '.$this->_table.' SET '.implode(', ', $a_value).' WHERE '.implode(' AND ', $a_where);
    }
    else if($this->_type == 'delete')
    {
      foreach((array)$this->_key as $_key=>$_item)
      {
        $a_where[] = $_key."='".get_string_query($_item)."'";
      }
      $_query = 'DELETE FROM '.$this->_table.' WHERE '.implode(' AND ', $a_where);
    }
    daoConnect::execute($_query);
  }
// TODO: add insert log

  public static function getInsertID($s_table, $s_field)
  {
    return daoConnect::getInsertID($s_table, $s_field);
  }

  public function getError()
  {
    return $this->_error;
  }

// TODO: Error message
  public function validate($s_type=true)
  {
    foreach((array)$this->_struture as $_key=>$_item)
    {
      if(isset($this->_data[$_key]) || $this->_type != 'update')
      {
        if(!(!$s_type && $_item['key']=='PRI'))
        {
          if($_item['type']=='char' || $_item['type']=='text')
          {
            if($_item['request'] && mb_strlen($this->_data[$_key])==0)
            {
              $this->_error['error'][$_key] = 'กรุณากรอกข้อมูล';
            }
          }
          else if($_item['type']=='int' || $_item['type']=='decimal')
          {
            if(empty($this->_data[$_key]))
              $this->_data[$_key] = 0;
            if(!$this->_convert)
              $this->_data[$_key] = str_replace(',', '', $this->_data[$_key]);
            if(!is_numeric($this->_data[$_key]))
            {
              $this->_error['error'][$_key] = 'กรุณากรอกข้อมูลเป็นตัวเลขเท่านั้น';
            }
            if($_item['request'] && !$this->_data[$_key])
            {
              $this->_error['error'][$_key] = 'กรุณากรอกข้อมูล';
            }
            if(isset($_item['max']) && $this->_data[$_key] > $_item['max'])
            {
              $this->_error['error'][$_key] = 'ข้อมูลไม่มากกว่า '.$_item['max'];
            }
            if(isset($_item['min']) && $this->_data[$_key] < $_item['min'])
            {
              $this->_error['error'][$_key] = 'ข้อมูลไม่น้อยกว่า '.$_item['min'];
            }
          }
          else if(($_item['type']=='datetime' || $_item['type']=='date'))
          {
            if($_item['request'] && !$this->_data[$_key])
            {
              $this->_error['error'][$_key] = 'กรุณากรอกข้อมูล';
            }
            else if(DateTime::createFromFormat('d/m/Y', $this->_data[$_key]) || DateTime::createFromFormat('d/m/Y H:i', $this->_data[$_key]))
            {
              $a_data = explode(' ', $this->_data[$_key]);
              $a_date = explode('/', $a_data[0]);
              $s_d = $a_date[0];
              $s_m = $a_date[1];
              $s_y = $a_date[2]-543;
              if(!checkdate($s_m, $s_d, $s_y))
              {
                $this->_error['error'][$_key] = $this->_data[$_key].'รูปแบบวันที่ไม่ถูกต้อง';
              }
              if(!$this->_convert)
                $this->_data[$_key] = convertDate($this->_data[$_key]);
            }
            else if(DateTime::createFromFormat('Y-m-d', $this->_data[$_key]) || DateTime::createFromFormat('Y-m-d H:i', $this->_data[$_key]) || DateTime::createFromFormat('Y-m-d H:i:s', $this->_data[$_key]))
            {
              $a_data = explode(' ', $this->_data[$_key]);
              $a_date = explode('-', $a_data[0]);
              $s_y = $a_date[0];
              $s_m = $a_date[1];
              $s_d = $a_date[2];
              if(!checkdate($s_m, $s_d, $s_y))
              {
                $this->_error['error'][$_key] = $this->_data[$_key].'รูปแบบวันที่ไม่ถูกต้อง';
              }
            }
            else if($this->_data[$_key] == '')
            {
              $this->_data[$_key]  = '0000-00-00';
            }
            else
            {
              $this->_error['error'][$_key] = $this->_data[$_key].'รูปแบบวันที่ไม่ถูกต้อง';
            }
          }

          if(isset($_item['lenght']) && mb_strlen($this->_data[$_key]) > $_item['lenght'])
          {
            $this->_error['error'][$_key] = 'ขนาดยาวเกินกว่า '.$_item['lenght']." อักษร";
          }
          if(isset($_item['pattern']) && $b_err && !preg_match($_item['pattern'], $this->_data[$_key]) && mb_strlen($this->_data[$_key])>0)
          {
            $this->_error['error'][$_key] = 'รูปแบบของข้อมูลไม่ถูกต้อง';
          }
        }
      }
    }
    if($this->_error['error'])
    {
      $this->_error['message'] = 'กรุณาตรวจสอบข้อมูล';
      return false;
    }
    return true;
  }

}

?>