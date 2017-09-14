<?php

class sysXML
{
  protected $_value;
  protected $_file_name;
  protected $_encode;
  protected $_dir;

  public function sysXML()
  {
    $this->_encode = 'UTF-8';
    $this->_dir = 'xml';
  }

  public function setEncode($s_name)
  {
    $this->_encode = $s_name;
  }

  public function load($s_name)
  {
    if(is_file($s_name))
    {
      $s_name = $s_name;
      $s_xml = file_get_contents($s_name);
      $this->_value = $this->xmlToArray($s_xml);
    }
    else if($s_name)
    {
      $this->_value = $this->xmlToArray($s_name);
    }
    else $this->_value = false;
  }

  public function getResults()
  {
    return $this->_value;
  }

  public function setResults($a_data)
  {
    $this->_value = $a_data;
  }

  public function loadResults($s_name, $s_dir=NULL)
  {
    if(is_null($s_dir)) $s_dir = $this->_dir;
    if(is_file($s_dir.'/'.$s_name))
    {
      $s_xml = file_get_contents($s_dir.'/'.$s_name);
      $this->_value = $this->xmlToArray($s_xml);
    }
    else $this->_value = false;
  }

  private function setRows($a_data, $i=0)
  {
    $s_xml = NULL;
    $_i = 0;
    foreach($a_data as $_key=>$_item)
    {
      $_i++;
      $_item =str_replace('&', '&amp;', $_item);
      $_item =str_replace('<', '&lt;', $_item);
      $_item =str_replace('>', '&gt;', $_item);
      $_item =str_replace('"', '&quot;', $_item);
      if(is_numeric($_key)) $_key = 'row_'.$_i;
      if(is_array($_item))
      {
        $s_xml.= str_repeat('  ', $i).'<'.$_key.'>'.PHP_EOL;
        $s_xml.= $this->setRows($_item, ($i+1));
        $s_xml.= str_repeat('  ', $i).'</'.$_key.'>'.PHP_EOL;
      }
      else if(!is_null($_item)  && strlen(trim($_item)) >= 1)
      {
        $s_xml.= str_repeat('  ', $i).'<'.$_key.'>'.$_item.'</'.$_key.'>'.PHP_EOL;
      }
      else
        $s_xml.= str_repeat('  ', $i).'<'.$_key.' />'.PHP_EOL;
    }
    return $s_xml;
  }

  public function save($s_name, $s_dir=false)
  {
    if(!$s_dir) $s_dir = $this->_dir;
    if(is_array($this->_value))
    {
      $s_xml = '<?xml version=\'1.0\' encoding=\''.$this->_encode.'\'?>'.PHP_EOL;
      $s_xml.= $this->setRows($this->_value);
      sysFile::saveFile($s_dir.'/'.$s_name, $s_xml);
    }
  }

  public function getXML()
  {
    if(is_array($this->_value))
    {
      $s_xml = '<?xml version=\'1.0\' encoding=\''.$this->_encode.'\'?>'.PHP_EOL;
      $s_xml.= $this->setRows($this->_value);
    }
    return $s_xml;
  }

  public function xmlToArray($contents, $get_attributes = 1, $priority = 'tag')
  {
    if(!$contents) return array();
    if(!function_exists('xml_parser_create')) die("'xml_parser_create()' function not found!");
    //Get the XML parser of PHP - PHP must have this module for the parser to work
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, $this->_encode);
    # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);
    if(!$xml_values) return;
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();
    $current = &$xml_array;
    $repeated_tag_index = array();
    foreach($xml_values as $data)
    {
      unset($attributes,$value);
      extract($data);
      $result = '';
      $attributes_data = array();
      if(isset($value))
      {
        if($priority == 'tag') $result = $value;
        else $result['value'] = $value;
      }
      if(isset($attributes) and $get_attributes)
      {
        foreach($attributes as $attr => $val)
        {
          if($priority == 'tag') $attributes_data[$attr] = $val;
          else $result['attr'][$attr] = $val;
        }
      }
      if($type == "open")
      {
        $parent[$level-1] = &$current;
        if(!is_array($current) or (!in_array($tag, array_keys($current))))
        {
          $current[$tag] = $result;
          if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
          $repeated_tag_index[$tag.'_'.$level] = 1;
          $current = &$current[$tag];
        }
        else
        {
          if(isset($current[$tag][0]))
          {
            $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
            $repeated_tag_index[$tag.'_'.$level]++;
          }
          else
          {
            $current[$tag] = array($current[$tag],$result);
            $repeated_tag_index[$tag.'_'.$level] = 2;
            if(isset($current[$tag.'_attr']))
            {
              $current[$tag]['0_attr'] = $current[$tag.'_attr'];
              unset($current[$tag.'_attr']);
            }
          }
          $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
          $current = &$current[$tag][$last_item_index];
        }
      }
      else if($type == "complete")
      {
        if(!isset($current[$tag]))
        {
          $current[$tag] = $result;
          $repeated_tag_index[$tag.'_'.$level] = 1;
          if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;
        }
        else
        {
          if(isset($current[$tag][0]) and is_array($current[$tag]))
          {
            $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
            if($priority == 'tag' and $get_attributes and $attributes_data)
            {
              $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
            }
            $repeated_tag_index[$tag.'_'.$level]++;
          }
          else
          {
            $current[$tag] = array($current[$tag],$result);
            $repeated_tag_index[$tag.'_'.$level] = 1;
            if($priority == 'tag' and $get_attributes)
            {
              if(isset($current[$tag.'_attr']))
              {
                $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                unset($current[$tag.'_attr']);
              }
              if($attributes_data)
              {
                $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
              }
            }
            $repeated_tag_index[$tag.'_'.$level]++;
          }
        }
      }
      else if($type == 'close')
      {
        $current = &$parent[$level-1];
      }
    }
    return($xml_array);
  }

}

?>