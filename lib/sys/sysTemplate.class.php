<?php

class sysTemplate
{
  private $_template;

  public function load($s_path)
  {
    $this->_template = sysFile::readFile($s_path);
  }

  public function loadTemplate($s_file, $s_path)
  {
    $this->_template = sysFile::readFile($s_path.'/'.$s_file);
  }

  public function setValue($s_name, $s_value)
  {
    $this->_template = str_replace("##$s_name##", $s_value, $this->_template);
  }

  public function setTemplate($s_template)
  {
    $this->_template = $s_template;
  }

  public function getTemplate()
  {
    return $this->_template;
  }

  public function save($s_file, $s_path)
  {
    sysFile::saveFile($s_path.'/'.$s_file,  $this->_template);
  }

}

?>