<?php

class daoConnectDatabase
{
  public $_db;
  public $_error = false;
  private $_connect =  array('DATABASE_TYPE'=>'mysql');
  private $_status = true;

  public function getDatabase()
  {
    return $this->_connect['DATABASE_DB'];
  }

  public function setConnect($s_type, $s_connect, $s_user, $s_pass, $s_db)
  {
    $this->_connect['DATABASE_TYPE'] = strtolower($s_type);
    $this->_connect['DATABASE_CONNECT'] = $s_connect;
    $this->_connect['DATABASE_USER'] = $s_user;
    $this->_connect['DATABASE_PASSWORD'] = $s_pass;
    $this->_connect['DATABASE_DB'] = $s_db;
  }

  public function connect()
  {
    if(isset($this->_connect))
    {
      try{
        $s_type = ($this->_connect['DATABASE_TYPE'] == 'mssql')?'mssqlnative':$this->_connect['DATABASE_TYPE'];
//        $s_type = $this->_connect['DATABASE_TYPE'];
        $this->_db = NewADOConnection($s_type);
        $this->_db->SetFetchMode(2);
        
        $this->_db->debug = SYSTEM_ADODB_DEBUG;
        $this->_db->autoCommit = true;
        if($this->_connect['DATABASE_TYPE'] == 'oci8')
        {
          $this->_db->charSet = 'AL32UTF8';
          $this->_status = $this->_db->Connect($this->_connect['DATABASE_CONNECT'], $this->_connect['DATABASE_USER'], $this->_connect['DATABASE_PASSWORD']);
        }
        else
          $this->_status = $this->_db->Connect($this->_connect['DATABASE_CONNECT'], $this->_connect['DATABASE_USER'], $this->_connect['DATABASE_PASSWORD'], $this->_connect['DATABASE_DB']);
        $this->_error = $this->_db->ErrorMsg();
        return $this->_status;
      }
      catch (exception $e)
      {
        adodb_pr($e);
        $e = adodb_backtrace($e->trace);
        return false;
      }
    }
  }

  public function isConnect()
  {
    return $this->_status;
  }

  public function getDatabaseType()
  {
    return $this->_connect['DATABASE_TYPE'];
  }

  public function getErrorMsg()
  {
    return $this->_db->ErrorMsg();
  }

  public function commit()
  {
    $this->_db->CommitTrans();
  }

}

?>