<?php

class sysPage
{
  private $_current;
  private $_row;
  private $_page = 20;
  private $_limit = 20;
  private $_word = 'page';

  public function setPage($s_value)
  {
    $this->_page = $s_value;
  }

  public function setCurrent($s_value)
  {
    $this->_current = $s_value;
  }

  public function setRow($s_value)
  {
    $this->_row = $s_value;
  }

  public function setWord($s_value)
  {
    $this->_word = $s_value;
  }

  public function setLimit($s_value)
  {
    $this->_limit = $s_value;
  }

  public function getParams()
  {
    $a_data['row'] = $this->_row;
    $a_data['word'] = $this->_word;
    $a_data['limit'] = $this->_limit;
    $a_data['page'] = $this->_current;
    $a_data['pageAll'] = ceil($this->_row / $this->_limit);
    $targetPage = ceil($this->_current / $this->_page);
    $a_data['startPage'] = (($targetPage-1) * $this->_page) + 1;
    $a_data['stopPage'] =  $targetPage * $this->_page;
    if($a_data['stopPage'] > $a_data['pageAll'])
    {
      $a_data['stopPage'] = $a_data['pageAll'];
    }
    if($a_data['page']>$a_data['pageAll'])
    {
      $a_data['page']=$a_data['pageAll'];
    }
    return $a_data;
  }

}

?>