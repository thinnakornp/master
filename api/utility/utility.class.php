<?php

class utility
{

  public static function getStructure($s_table)
  {
    $s_file = '../model/'.daoConnect::getDatabase().'/structure/'.$s_table.'.json';
    $s_custom = '../model/'.daoConnect::getDatabase().'/custom/'.$s_table.'.json';
    if(is_file($s_file))
    {
      $s_json = sysFile::readFile($s_file);
      $a_json = json_decode($s_json, true);

      if(is_file($s_custom))
      {
        $s_custom = sysFile::readFile($s_file);
        $a_custom = json_decode($s_custom, true);
        return array_replace_recursive($a_json, $a_custom);
      }
      return $a_json;
    }
    return false;
  }

/**
 * type : char, text, int, decimal, date, datetime, time
 **/
  public static function createModel($s_table)
  {
    $a_schema = databaseSchema::getSchema($s_table);
    if($a_schema)
    {
      foreach($a_schema as $_key=>$_item_schema)
      {
        if(isset($_item_schema['Key']) && $_item_schema['Key']=='PRI')
        {
          $a_data[$_key]['key'] = 'PRI';
        }
        if(isset($_item_schema['Key']) && $_item_schema['Key']=='UNI')
        {
          $a_data[$_key]['key'] = 'UNI';
        }

        if(isset($_item_schema['Type']) && in_array($_item_schema['Type'], array('nvarchar', 'varchar', 'char')))
        {
          $a_data[$_key]['type'] = 'char';
          $a_data[$_key]['lenght'] = $_item_schema['Size'];
        }
        else if(isset($_item_schema['Type']) && in_array($_item_schema['Type'], array('bit')))
        {
          $a_data[$_key]['type'] = 'char';
          $a_data[$_key]['lenght'] = 1;
        }
        else if(isset($_item_schema['Type']) && in_array($_item_schema['Type'], array('uniqueidentifier')))
        {
          $a_data[$_key]['type'] = 'char';
          $a_data[$_key]['lenght'] = 25;
        }
        else if(isset($_item_schema['Type']) && in_array($_item_schema['Type'], array('text', 'tinytext', 'mediumtext', 'longtext')))
        {
          $a_data[$_key]['type'] = 'text';
          $a_data[$_key]['lenght'] = $_item_schema['Size'];
        }
        else if(isset($_item_schema['Type']) && in_array($_item_schema['Type'], array('tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'numeric')))
        {
          $a_size = array('tinyint'=>1, 'smallint'=>2, 'mediumint'=>3, 'int'=>4, 'bigint'=>8, 'numeric'=>4);
          $i_size = pow(256, $a_size[$_item_schema['Type']]);
          $a_data[$_key]['type'] = 'int';
          if(isset($_item_schema['Attributes']) && $_item_schema['Attributes'] == 'unsigned')
          {
            $a_data[$_key]['max'] = ($i_size-1);
            $a_data[$_key]['min'] = 0;
          }
          else
          {
            $a_data[$_key]['max'] = (($i_size/2)-1);
            $a_data[$_key]['min'] = -($i_size/2);
          }
          if(isset($_item_schema['Zero']) && $_item_schema['Zero'] == 'zerofill')
          {
            $a_data[$_key]['zero'] = true;
          }
        }
        else if(isset($_item_schema['Type']) && in_array($_item_schema['Type'], array('float', 'double', 'decimal', 'money')))
        {
          $a_data[$_key]['type'] = 'decimal';
          if(isset($_item_schema['Size']))
          {
            $a_size = explode(',', $_item_schema['Size']);
            if(isset($a_size[1])) $i_decimal = $a_size[1];
            else $i_decimal = 4;
            $i_len = $a_size[0]-$i_decimal;
            $a_data[$_key]['max'] = str_repeat('9',$i_len).'.'.str_repeat('9',$i_decimal);
            if(isset($_item_schema['Attributes']) && $_item_schema['Attributes'] == 'unsigned')
            {
              $a_data[$_key]['min'] = 0;
            }
            else
            {
              $a_data[$_key]['min'] =  '-'.str_repeat('9',$i_len).'.'.str_repeat('9',$i_decimal);
            }
            $a_data[$_key]['decimal'] = $i_decimal;
          }
        }
        else if(isset($_item_schema['Type']) && in_array($_item_schema['Type'], array('date', 'datetime', 'time')))
        {
          $a_data[$_key]['type'] = $_item_schema['Type'];
        }
        else
        {
          $a_data[$_key]['type'] = 'text';
        }

        if(isset($_item_schema['Extra']) && $_item_schema['Extra']=='auto_increment')
          $a_data[$_key]['auto'] = true;
        if(isset($_item_schema['Null']) && $_item_schema['Null']=='NO')
          $a_data[$_key]['request'] = true;
        if($_item_schema['Comment'])
          $a_data[$_key]['commend'] = $_item_schema['Comment'];
      }
      sysFile::saveFile('../model/'.daoConnect::getDatabase().'/schema/'.$s_table.'.json', json_encode($a_schema, JSON_FORCE_OBJECT));
      sysFile::saveFile('../model/'.daoConnect::getDatabase().'/structure/'.$s_table.'.json', json_encode($a_data, JSON_FORCE_OBJECT));
    }
  }

}

?>