<?php

class databaseMSSQL
{

  public static function getCount($s_sql)
  {
    $s_count = 'SELECT count(*) as num FROM ('.$s_sql.') datarow';
    return daoConnect::getOne($s_count);
  }

  public static function getQueryLimit($s_sql, $i_page=1, $i_limit=20)
  {
    $s_sql = 'WITH DataLimit AS (';
    $s_sql.= $s_sql;
    $s_sql.= ') SELECT * FROM DataLimit WHERE numRow BETWEEN '.((($i_page-1)*$i_limit)+1).' AND '.((($i_page-1)*$i_limit)+$i_limit).' ORDER BY numRow ASC';
    return $s_sql;
  }

  public static function getTable($s_name)
  {
    $s_query = 'select name from sysobjects where xtype = \'U\' order by name';
    $a_rs = daoConnect::getArray($s_query);
    $a_table = false;
    if($a_rs)
    {
      foreach ($a_rs as $_item)
      {
        $a_table[] = $_item['name'];
      }
    }
    return $a_table;
  }

  public static function isTable($s_table)
  {
    $s_query = "SELECT * FROM INFORMATION_SCHEMA.Columns where TABLE_NAME = '".$s_table."'";
    $a_rs = daoConnect::getRow($s_query);
    if($a_rs) return true;
    else return false;
  }

  public static function setSchema($s_table)
  {
    $s_sql = 'select COLUMN_NAME, TABLE_NAME
      from INFORMATION_SCHEMA.COLUMNS
      where TABLE_SCHEMA = \'dbo\' and TABLE_NAME = \''.$s_table.'\'
      and COLUMNPROPERTY(object_id(TABLE_NAME), COLUMN_NAME, \'IsIdentity\') = 1
      order by TABLE_NAME';
    $a_auto = daoConnect::getArray($s_sql);
    $a_data_auto = array();
    if($a_auto)
    {
      foreach($a_auto as $_item)
      {
        $a_data_auto[] = $_item['COLUMN_NAME'];
      }
    }
    $s_query = "SELECT * FROM INFORMATION_SCHEMA.Columns where TABLE_NAME = '".$s_table."'";
    $a_structure = daoConnect::getArray($s_query);
    if($a_structure)
    {
      foreach($a_structure as $_item)
      {
        if(in_array($_item['COLUMN_NAME'], $a_data))
          $_item['Key'] = 'PRI';
        if(in_array($_item['COLUMN_NAME'], $a_data_auto))
          $_item['Extra'] = 'auto_increment';
        $a_schema[$_item['COLUMN_NAME']]=self::getStructure($_item);
      }
    }
    return $a_schema;
  }

  final private static function getStructure($a_data)
  {
//    print_pre($a_data);
    $a_data_field['Type'] = $a_data['DATA_TYPE'];
    if($a_data['CHARACTER_MAXIMUM_LENGTH'])
      $a_data_field['Size'] = $a_data['CHARACTER_MAXIMUM_LENGTH'];
    if($a_data['IS_NULLABLE']== 'NO')
      $a_data_field['Null'] = $a_data['IS_NULLABLE'];
    if($a_data['Key'])
      $a_data_field['Key'] = $a_data['Key'];
    if($a_data['Extra'])
      $a_data_field['Extra'] = $a_data['Extra'];
    return $a_data_field;
  }


}

?>