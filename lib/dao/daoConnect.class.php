<?php

class daoConnect
{
  private static $_connect;
  private static $_use;
  private static $_page;
  private static $_count;

  public static function setConnect($s_name, $s_type, $s_connect, $s_user, $s_pass, $s_db)
  {
    $s_name = strtolower($s_name);
    self::$_connect[$s_name] = new daoConnectDatabase();
    self::$_connect[$s_name]->setConnect($s_type, $s_connect, $s_user, $s_pass, $s_db);
    $_chk = self::$_connect[$s_name]->connect();
    self::$_use = $s_name;
  }

  public static function getDatabase()
  {
    if(is_object(self::$_connect[self::$_use]))
      return self::$_connect[self::$_use]->getDatabase();
    else return false;
  }

  public static function setUse($s_name)
  {
    $s_name = strtolower($s_name);
    if(isset(self::$_connect[$s_name]))
    {
      self::$_use = $s_name;
    }
  }

  public static function getDatabaseType()
  {
    if(is_object(self::$_connect[self::$_use]))
    {
      return self::$_connect[self::$_use]->getDatabaseType();
    }
    return false;
  }

  public static function execute($s_sql)
  {
    if(is_object(self::$_connect[self::$_use]) && $s_sql)
    {
      if(self::getDatabaseType()=='mssql') self::$_connect[self::$_use]->_db->Execute('SET ANSI_WARNINGS OFF');
      self::$_connect[self::$_use]->_db->Execute($s_sql);
      self::checkError($s_sql);
      if(self::getDatabaseType()=='mssql') self::$_connect[self::$_use]->_db->Execute('SET ANSI_WARNINGS ON');
      return (self::$_connect[self::$_use]->_db->_queryID===false)?false:true;
    }
    return false;
  }

  public static function getArray($s_sql)
  {
    if(is_object(self::$_connect[self::$_use]))
    {
      $a_data = self::$_connect[self::$_use]->_db->GetArray($s_sql);
      self::checkError($s_sql);
      return $a_data;
    }
    return false;
  }

  public static function getRow($s_sql)
  {
    if(is_object(self::$_connect[self::$_use]))
    {
      $a_data = self::$_connect[self::$_use]->_db->GetRow($s_sql);
      self::checkError($s_sql);
      return $a_data;
    }
    return false;
  }

  public static function getOne($s_sql)
  {
    if(is_object(self::$_connect[self::$_use]))
    {
      $a_data = self::$_connect[self::$_use]->_db->GetOne($s_sql);
      self::checkError($s_sql);
      return $a_data;
    }
    return false;
  }

  public static function getQueryLimit($s_sql, $i_page=1, $i_limit=20)
  {
    switch(self::$_connect[self::$_use]->getDatabaseType())
    {
      case 'mysqli' :
      case 'mysql'  : $_sql = databaseMySQL::getQueryLimit($s_sql, $i_page, $i_limit); break;
      case 'mssql'  : $_sql = databaseMSSQL::getQueryLimit($s_sql, $i_page, $i_limit); break;
      case 'oci8'   :
      case 'oracle' : $_sql = databaseOracle::getQueryLimit($s_sql, $i_page, $i_limit); break;
    }
    $s_row = self::getCount($s_sql);
    $o_page = new sysPage();
    $o_page->setWord('page');
    $o_page->setCurrent($i_page);
    $o_page->setLimit($i_limit);
    $o_page->setRow($s_row);
    self::$_page = $o_page->getParams();
    return self::getArray($_sql);
  }

  public static function getPage()
  {
    return self::$_page;
  }

  public static function getCount($s_sql)
  {
    switch(self::$_connect[self::$_use]->getDatabaseType())
    {
      case 'mysqli' :
      case 'mysql'  : return databaseMySQL::getCount($s_sql); break;
      case 'mssql'  : return databaseMSSQL::getCount($s_sql); break;
      case 'oci8'   :
      case 'oracle' : return databaseOracle::getCount($s_sql); break;
    }
    return false;
  }

  public static function getInsertID($s_table, $s_field)
  {
    if(is_object(self::$_connect[self::$_use]))
      return self::$_connect[self::$_use]->_db->PO_Insert_ID($s_table, $s_field);
    else return false;
  }

  private static function checkError($s_sql)
  {
    if($_error = self::$_connect[self::$_use]->getErrorMsg())
    {
      $_message = '[Date:'.date('Y-m-d H:i:s').'] [IP:'.$_SERVER['REMOTE_ADDR'].'] ['.sys::getSite().'/'.sys::getModule().'/'.sys::getPage().']'.PHP_EOL;
      $_message.= '[Query:'.$s_sql.']'.PHP_EOL;
      $_message.= '[Error Massage:'.$_error.']'.PHP_EOL;
      $_message.= '===================================='.PHP_EOL;
      $_file = PATH.'logs/error/'.date('Ymd').'.log';
      $_dir = trim(dirname($_file),'/');
      sysFile::forceDirectory($_dir);
      $f_resource = fopen($_file, 'a+');
      fwrite($f_resource, $_message);
      fclose($f_resource);
    }
  }


}

?>