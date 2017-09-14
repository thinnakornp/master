<?php

class databaseMySQL
{

  public static function getCount($s_sql)
  {
    $s_count = 'SELECT count(*) as num FROM ('.$s_sql.') datarow';
    return daoConnect::getOne($s_count);
  }

  public static function getQueryLimit($s_sql, $i_page=1, $i_limit=20)
  {
    return $s_sql.' LIMIT '.(($i_page-1)*$i_limit).', '.$i_limit;
  }

  public static function getTable($s_name)
  {
    $s_query = 'SHOW TABLES FROM '.$s_name;
    $a_rs = daoConnect::getArray($s_query);
    $a_table = false;
    if($a_rs)
    {
      foreach ($a_rs as $_item)
      {
        $a_table[] = $_item['Tables_in_'.$s_name];
      }
    }
    return $a_table;
  }

  public static function isTable($s_table)
  {
    $s_query = "SHOW TABLES LIKE '".$s_table."'";
    $a_rs = daoConnect::getRow($s_query);
    if($a_rs) return true;
    else return false;
  }

  public static function setSchema($s_table)
  {
    $s_query = 'SHOW FULL FIELDS FROM `'.$s_table.'`';
    $a_structure = daoConnect::getArray($s_query);
    if($a_structure)
    {
      foreach($a_structure as $_item)
      {
        $a_schema[$_item['Field']]=self::getStructure($_item);
      }
    }
    return $a_schema;
  }

  final private static function getStructure($a_data)
  {
    if(preg_match('@^(set|enum)\((.+)\)$@i', $a_data['Type'], $a_tmp))
    {
      $a_data_field['Type'] = $a_tmp[1];
      $a_data_field['Size'] = $a_tmp[2];
    }
    elseif(preg_match('@^(char|varchar|float|double|decimal|year|binary|varbinary)\((.+)\)$@i', $a_data['Type'], $a_tmp))
    {
      $a_data_field['Type'] = $a_tmp[1];
      $a_data_field['Size'] = $a_tmp[2];
    }
    elseif(preg_match('@^(float|double|text|longtext|mediumtext|date|datetime|timestamp|time|blob|tinyblob|mediumblob|longblob|tinytext)$@i', $a_data['Type'], $a_tmp))
    {
      $a_data_field['Type'] = $a_tmp[1];
    }
    else
    {
      $s_data = $a_data['Type'];
      if(preg_match('@zerofill$@i', $s_data))
      {
        $s_data = str_replace(' zerofill', '', $s_data);
        $a_data_field['Zero'] = 'zerofill';
      }
      if(preg_match('@unsigned$@i', $s_data))
      {
        $s_data = str_replace(' unsigned', '', $s_data);
        $a_data_field['Attributes'] = 'unsigned';
      }
      if(preg_match('@^(tinyint|smallint|mediumint|int|bigint|float|double|decimal)\((.+)\)$@i', $s_data, $a_tmp))
      {
        $a_data_field['Type'] = $a_tmp[1];
        $a_data_field['Size'] = $a_tmp[2];
      }
    }
    if(!empty($a_data['Collation']))
      $a_data_field['Collation'] = $a_data['Collation'];
    if(!empty($a_data['Null']))
      $a_data_field['Null'] = $a_data['Null'];
    if(!empty($a_data['Key']))
      $a_data_field['Key'] = $a_data['Key'];
    if($a_data['Default']=='0')
      $a_data_field['Default'] = $a_data['Default'];
    elseif(!empty($a_data['Default']))
      $a_data_field['Default'] = $a_data['Default'];
    if(!empty($a_data['Extra']))
      $a_data_field['Extra'] = $a_data['Extra'];
    if(!empty($a_data['Privileges']))
      $a_data_field['Privileges'] = $a_data['Privileges'];
    if(!empty($a_data['Comment']))
      $a_data_field['Comment'] = $a_data['Comment'];
    return $a_data_field;
  }

}

?>