<?php

class databaseOracle
{

  public static function getCount($s_sql)
  {
    $s_count = 'SELECT count(*) as num FROM ('.$s_sql.') datarow';
    return daoConnect::getOne($s_count);
  }

  public static function getQueryLimit($s_sql, $i_page=1, $i_limit=20)
  {
    $i_min = (($i_page-1) * $i_limit) + 1;
    $i_max = $i_page * $i_limit;
    return 'select * from ( select a.*, rownum rnum from ( '.$s_sql.' ) a where rownum <= '.$i_max.' ) where rnum >= '.$i_min;
  }

  public static function getTable()
  {
    $s_query = "select OBJECT_NAME from user_objects where object_type = 'TABLE'";
    $a_rs = daoConnect::getArray($s_query);
    $a_table = false;
    if($a_rs)
    {
      foreach ($a_rs as $_item)
      {
        $a_table[] = $_item['OBJECT_NAME'];
      }
    }
    sort($a_table);
    return $a_table;
  }

  public static function isTable($s_table)
  {
    $s_query = "select OBJECT_NAME from user_objects where object_type = 'TABLE' and OBJECT_NAME = '".$s_table."'";
    $a_rs = daoConnect::getRow($s_query);
    if($a_rs) return true;
    else return false;
  }

  public static function setSchema($s_table)
  {
    $s_sql = 'SELECT * FROM ALL_CONS_COLUMNS A JOIN ALL_CONSTRAINTS C ON A.CONSTRAINT_NAME = C.CONSTRAINT_NAME
              WHERE C.TABLE_NAME = \''.$s_table.'\' AND C.CONSTRAINT_TYPE = \'P\'';
    $a_pk = daoConnect::getArray($s_sql);
    if($a_pk)
    {
      foreach($a_pk as $_item)
      {
        $a_data[] = $_item['COLUMN_NAME'];
      }
    }

    $s_query = 'SELECT cc.COLUMN_NAME, cc.DATA_TYPE, cc.DATA_LENGTH, cc.NULLABLE, tc.COMMENTS FROM
    user_tab_columns cc
    join   user_col_comments  tc on  cc.column_name = tc.column_name
    and cc.table_name  = tc.table_name
    WHERE cc.table_name=\''.$s_table.'\'';
    $a_structure = daoConnect::getArray($s_query);
    if($a_structure)
    {
      foreach($a_structure as $_item)
      {
        if(in_array($_item['COLUMN_NAME'], $a_data))
        {
          $_item['Key'] = 'PRI';
        }
        $a_schema[$_item['COLUMN_NAME']]=self::getStructure($_item);
      }
    }
    return $a_schema;
  }

  final private static function getStructure($a_data)
  {
    switch($a_data['DATA_TYPE'])
    {
      case 'VARCHAR2' : $s_type = 'char'; break;
      case 'NUMBER' : $s_type = 'int'; break;
      case 'CLOB' : $s_type = 'text'; break;
      case 'DATE' : $s_type = 'date'; break;
      default : $s_type = 'char';
    }
    $a_data_field['Type'] = $s_type;
    if($a_data['DATA_LENGTH'] && $a_data['DATA_TYPE']!='DATE')
      $a_data_field['Size'] = $a_data['DATA_LENGTH'];
    if($a_data['NULLABLE'] == 'Y')
      $a_data_field['Null'] = 'YES';
    if($a_data['Key'] == 'PRI')
      $a_data_field['Key'] = 'PRI';
    if($a_data['COMMENTS']) $a_data_field['Comment'] = $a_data['COMMENTS'];
   return $a_data_field;
  }

}

?>