<?php

class databaseSchema
{
  private static $_database;
  private static $_table;
  private static $_type;

  final public static function getInstance()
  {
    static $_instance;
    if(!is_a($_instance, __CLASS__))
    {
      $c = __CLASS__;
      $_instance = new $c;
    }
  }

  final public static function setDatabase()
  {
    if(!$s_base) $s_base = daoConnect::getDatabase();
    self::getInstance();
    self::$_type = daoConnect::getDatabaseType();
    return self::$_database = $s_base;
//    self::setTable();
  }

  final public static function getTable()
  {
    switch(strtolower(self::$_type))
    {
      case 'mysqli' :
      case 'mysql' : self::$_table = databaseMySQL::getTable(self::$_database); break;
      case 'mssql' : self::$_table = databaseMSSQL::getTable(); break;
      case 'oci8' : self::$_table = databaseOracle::getTable(); break;
    }
    return self::$_table;
  }

  final public static function createTable($s_table, $s_pk, $a_field)
  {
    switch(strtolower(self::$_type))
    {
      case 'mysqli' :
      case 'mysql' :
        return databaseMySQL::createTable($s_table, $s_pk, $a_field);
      break;
      case 'mssql' :
        return databaseMSSQL::createTable($s_table, $s_pk, $a_field);
      break;
      case 'oci8' :
        return databaseOracle::createTable($s_table, $s_pk, $a_field);
      break;
    }
    return false;
  }

  final public static function isTable($s_table)
  {
    switch(strtolower(self::$_type))
    {
      case 'mysqli' :
      case 'mysql' :
        return databaseMySQL::isTable($s_table);
      break;
      case 'mssql' :
        return databaseMSSQL::isTable($s_table);
      break;
      case 'oci8' :
        return databaseOracle::isTable($s_table);
      break;
    }
    return false;
  }

  final public static function getSchema($s_table)
  {
    switch(strtolower(self::$_type))
    {
      case 'mysqli' :
      case 'mysql' :
        if(databaseMySQL::isTable($s_table))
        {
          $a_schema = databaseMySQL::setSchema($s_table);
        }
      break;
      case 'mssql' :
        if(databaseMSSQL::isTable($s_table))
        {
          $a_schema = databaseMSSQL::setSchema($s_table);
        }
      break;
      case 'oci8' :
        if(databaseOracle::isTable($s_table))
        {
          $a_schema = databaseOracle::setSchema($s_table);
        }
      break;
    }
    return $a_schema;
  }

}

?>