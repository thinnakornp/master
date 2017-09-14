<?php

function checkWebsite($host)
{
  $a_data = parse_url($host);
  if($a_data['port'])
  {
    $port = $a_data['port'];
  }
  else
  {
    switch($a_data['scheme'])
    {
      case 'http' : $port='80'; break;
      case 'https' : $port='443'; break;
    }
  }
  if($socket =@ fsockopen($a_data['host'], $port, $errno, $errstr, 30))
  {
    fclose($socket);
    return true;
  }
  else
  {
    return false;
  }
}

function getPath($path)
{
  $_path = PATH.$path;
  $a_dir = scandir($_path);
  if($a_dir)
  {
    $a_dir = array_diff($a_dir, array('.', '..'));
    foreach($a_dir as $_item)
    {
      if(is_dir($_path.'/'.$_item))
      {
        $a_file = scandir($_path.'/'.$_item);
        foreach($a_file as $_file)
        {
          if(is_file($_path.'/'.$_item.'/'.$_file))
          {
            $file[] = $_item.'/'.$_file;
          }
        }
      }
    }
  }
  return $file;
}

function Tis620ToUtf8($tis)
{
  for( $i=0 ; $i< strlen($tis) ; $i++ )
  {
    $s = substr($tis, $i, 1);
    $val = ord($s);
    if( $val < 0x80 )
    {
       $utf8 .= $s;
    }
    else if ( ( 0xA1 <= $val and $val <= 0xDA ) or ( 0xDF <= $val and $val <= 0xFB ) )
    {
      $unicode = 0x0E00 + $val - 0xA0;
      $utf8 .= chr( 0xE0 | ($unicode >> 12) );
      $utf8 .= chr( 0x80 | (($unicode >> 6) & 0x3F) );
      $utf8 .= chr( 0x80 | ($unicode & 0x3F) );
    }
  }
  return $utf8;
}

function Utf8ToTis620($string)
{
  $str = $string;
  $res = '';
  for ($i = 0; $i < strlen($str); $i++)
  {
    if (ord($str[$i]) == 224)
    {
      $unicode = ord($str[$i+2]) & 0x3F;
      $unicode |= (ord($str[$i+1]) & 0x3F) << 6;
      $unicode |= (ord($str[$i]) & 0x0F) << 12;
      $res .= chr($unicode-0x0E00+0xA0);
      $i += 2;
    }
    else
    {
      $res .= $str[$i];
    }
  }
  return $res;
}

function print_pre($a_data)
{
  if(is_array($a_data) || is_object($a_data))
  {
    echo '<pre class="debug">'.PHP_EOL;
    print_r($a_data);
    echo '</pre>'.PHP_EOL;
  }
  else echo $a_data;
}

function include_template($s_name, $a_value=null)
{
  $s_chk = false;
  if(is_array($a_value)) extract($a_value);
  else if($a_value) parse_str($a_value);
  $s_mod = false;
  $s_name = trim($s_name);
  if(preg_match("/^([a-zA-Z0-9_])+\/([a-zA-Z0-9_])+\/([a-zA-Z0-9_])+$/", $s_name))
  {
    $a_module = explode('/', $s_name);
    $s_site = $a_module[0];
    $s_module = $a_module[1];
    $s_template = $a_module[2];
    $s_mod = true;
  }
  else if(preg_match("/^([a-zA-Z0-9_])+\/([a-zA-Z0-9_])+$/", $s_name))
  {
    $a_module = explode('/', $s_name);
    $s_site = sys::getSite();
    $s_module = $a_module[0];
    $s_template = $a_module[1];
    $s_mod = true;
  }
  else if(preg_match("/^([a-zA-Z0-9_])+$/", $s_name))
  {
    $s_site = sys::getSite();
    $s_module = sys::getModule();
    $s_template = $s_name;
    $s_mod = true;
  }
  if($s_mod)
  {
    $_path = PATH.'apps/'.$s_site.'/'.$s_module.'/';
    if(is_file($_path.'controls/'.$s_template.'.php'))
    {
      include($_path.'controls/'.$s_template.'.php');
    }
    if(is_file($_path.'templates/'.$s_template.'.php'))
    {
      include($_path.'templates/'.$s_template.'.php');
    }
  }
}

// TODO: Set Module
function getLocation($s_mod, $s_value=false)
{
  if(is_array($s_value)) $a_value = $s_value;
  else parse_str($s_value, $a_value);

  $a_mod = explode('/',$s_mod);
  switch(count($a_mod))
  {
    case 1 : $s_mod = sys::getSite().'/'.sys::getModule().'/'.$s_mod; break;
    case 2 : $s_mod = sys::getSite().'/'.$s_mod; break;
  }

  $s_value_query = ($s_value)?'?'.urldecode(http_build_query($a_value)):'';
  $s_url = sys::getURL().$s_mod.$s_value_query;
  return $s_url;
}

function isCurrentLocation($s_mod){
  return getLocation($s_mod)==sys::getCurrent();
}

function get_string_query($s_value)
{
  $s_value = str_replace('\\', '\\\\', $s_value);
  $s_value = str_replace("'", "''", $s_value);
  return $s_value;
}

function convertDate($ms_data, $s_buddhist=true)
{
  $ms_date = trim($ms_data);
  if(preg_match ("/^[0-9]{1,2}[\/-][0-9]{1,2}[\/-][0-9]{4} [0-9]{2}:[0-9]{2}+$/", $ms_date))
  {
    $a_info = explode(' ', $ms_date);
    $a_date =  preg_split("/[\/-]/", $a_info[0]);
    if(mb_strlen($a_date[1])==1) $a_date[1] = '0'.$a_date[1];
    if(mb_strlen($a_date[0])==1) $a_date[0] = '0'.$a_date[0];
    if($s_buddhist) $s_date = ($a_date[2]-543).'-'.$a_date[1].'-'.$a_date[0];
    else $s_date = $a_date[2].'-'.$a_date[1].'-'.$a_date[0];
    return $s_date.' '.$a_info[1].':00';
  }
  else if(preg_match ("/^[0-9]{1,2}[\/-][0-9]{1,2}[\/-][0-9]{4}+$/", $ms_date))
  {
    $a_date =  preg_split("/[\/-]/", $ms_date);
    if(mb_strlen($a_date[1])==1) $a_date[1] = '0'.$a_date[1];
    if(mb_strlen($a_date[0])==1) $a_date[0] = '0'.$a_date[0];
    if($s_buddhist) $s_date = ($a_date[2]-543).'-'.$a_date[1].'-'.$a_date[0];
    else $s_date = $a_date[2].'-'.$a_date[1].'-'.$a_date[0];
    return $s_date;
  }
  else return $ms_date;
}

function isDate($s_date)
{
  if(preg_match ("/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}+$/", $s_date) || preg_match ("/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}+$/", $s_date) || preg_match ("/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9] [0-9]{1,2}:[0-9]{1,2}+$/", $s_date))
  {
    $a_data = explode(' ', $s_date);
    $a_date = explode('/', $a_data[0]);
    $s_d = $a_date[0];
    $s_m = $a_date[1];
    $s_y = $a_date[2];
    return checkdate($s_m, $s_d, $s_y);
  }
  else if(preg_match ("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}+$/", $s_date) || preg_match ("/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}+$/", $s_date) || preg_match ("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{1,2}:[0-9]{1,2}+$/", $s_date))
  {
    $a_data = explode(' ', $s_date);
    $a_date = explode('-', $a_data[0]);
    $s_y = $a_date[0];
    $s_m = $a_date[1];
    $s_d = $a_date[2];
    return checkdate($s_m, $s_d, $s_y);
  }
  else if($s_date=='' || is_null($s_date))
  {
    return false;
  }
  return false;
}

function redirect($s_url)
{
  if (!headers_sent())    //If headers not sent yet... then do php redirect
  {
    header('Location: '.$s_url);
    exit;
  }
  else //If headers are sent... do java redirect... if java disabled, do html redirect.
  {
    echo '<script type="text/javascript">';
    echo 'window.location.href="'.$s_url.'";';
    echo '</script>';
    echo '<noscript>';
    echo '<meta http-equiv="refresh" content="0;url='.$s_url.'" />';
    echo '</noscript>';
    exit;
  }
}

function get($s_name, $s_defalut=null)
{
  $a_data = array_merge($_GET, $_POST);
  return ($a_data[$s_name])?$a_data[$s_name]:$s_defalut;
}

function getIP()
{
  $ipaddress = '';
  if (getenv('HTTP_CLIENT_IP'))
      $ipaddress = getenv('HTTP_CLIENT_IP');
  else if(getenv('HTTP_X_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
  else if(getenv('HTTP_X_FORWARDED'))
      $ipaddress = getenv('HTTP_X_FORWARDED');
  else if(getenv('HTTP_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_FORWARDED_FOR');
  else if(getenv('HTTP_FORWARDED'))
      $ipaddress = getenv('HTTP_FORWARDED');
  else if(getenv('REMOTE_ADDR'))
      $ipaddress = getenv('REMOTE_ADDR');
  else
      $ipaddress = 'UNKNOWN';
  return $ipaddress;
}

function showDate($s_date, $s_format='d/m/Y H:i')
{
  $a_month = array(
    'January' => 'มกราคม',
    'February' => 'กุมภาพันธ์',
    'March' => 'มีนาคม',
    'April' => 'เมษายน',
    'May' => 'พฤษภาคม',
    'June' => 'มิถุนายน',
    'July' => 'กรกฎาคม',
    'August' => 'สิงหาคม',
    'September' => 'กันยายน',
    'October' => 'ตุลาคม',
    'November' => 'พฤศจิกายน',
    'December' => 'ธันวาคม',
  );
  $a_sh_month = array(
    'Jan' => 'ม.ค.',
    'Feb' => 'ก.พ.',
    'Mar' => 'มี.ค.',
    'Apr' => 'เม.ย',
    'May' => 'พ.ค.',
    'Jun' => 'มิ.ย.',
    'Jul' => 'ก.ค.',
    'Aug' => 'ส.ค.',
    'Sep' => 'ก.ย.',
    'Oct' => 'ต.ค.',
    'Nov' => 'พ.ย.',
    'Dec' => 'ธ.ค.',
  );

  if($s_date == '0000-00-00 00:00:00' || $s_date == '0000-00-00' || $s_date == '00:00:00' || $s_date=='') return null;
  else if(isDate($s_date))
  {
    $s_data = date($s_format, strtotime($s_date));
    $s_year = date('Y', strtotime($s_date));
    $s_month = date('F', strtotime($s_date));
    $s_sh_month = date('M', strtotime($s_date));
    $s_data = str_replace($s_year, ($s_year+543), $s_data);
    if(preg_match ("/F/", $s_format))
    {
      $s_data = str_replace($s_month, $a_month[$s_month], $s_data);
    }
    if(preg_match ("/M/", $s_format))
    {
      $s_data = str_replace($s_sh_month, $a_sh_month[$s_sh_month], $s_data);
    }
    return trim($s_data);
  }
  return null;
}

function getDataArray($a_data, $s_key, $s_value)
{
  $a_rs = array();
  if(is_array($a_data))
  {
    foreach($a_data as $_item)
    {
      $a_rs[$_item[$s_key]] = $_item[$s_value];
    }
  }
  return $a_rs;
}

function convertSizeToBytes($size)
{
  if ( is_numeric( $size) ) {
     return $size;
  }
  $suffix = mb_substr($size, -1);
  $value = mb_substr($size, 0, -1);
  switch(mb_strtoupper($suffix)){
  case 'P':
      $value *= 1024;
  case 'T':
      $value *= 1024;
  case 'G':
      $value *= 1024;
  case 'M':
      $value *= 1024;
  case 'K':
      $value *= 1024;
      break;
  }
  return $value;
}

function formatSizeUnits($bytes)
{
  if ($bytes >= 1073741824)
  {
    $bytes = number_format($bytes / 1073741824, 2) . ' GB';
  }
  elseif ($bytes >= 1048576)
  {
    $bytes = number_format($bytes / 1048576, 2) . ' MB';
  }
  elseif ($bytes >= 1024)
  {
    $bytes = number_format($bytes / 1024, 2) . ' KB';
  }
  elseif ($bytes > 1)
  {
    $bytes = $bytes . ' bytes';
  }
  elseif ($bytes == 1)
  {
    $bytes = $bytes . ' byte';
  }
  else
  {
    $bytes = '0 bytes';
  }
  return $bytes;
}

function showWord($s_text, $s_limit=0, $s_type=false)
{
  if($s_limit)
  {
    $i_lenstr = mb_strlen($s_text);
    if($s_limit > 0 && $i_lenstr > $s_limit)
    {
      $s_str = mb_substr(nl2br(showText($s_text, $s_type)), 0, $s_limit);
      return $s_str.'&hellip;';
    }
    else return nl2br(showText($s_text, $s_type));
  }
  else
  {
    if($s_type) return showText($s_text, $s_type);
    else return nl2br(showText($s_text, $s_type));
  }
}

function showText($s_text, $s_type=false)
{
//  $s_text = portal::rudeWord($s_text);
  if($s_type) return $s_text;
  return htmlspecialchars($s_text, ENT_QUOTES);
}

function showNumber($s_data)
{
  if(is_numeric($s_data)) return number_format($s_data, 0, '.', ',');
  else return 0;
}

function showZero($s_text, $s_len=4)
{
  $s_count = mb_strlen($s_text);
  if($_i = $s_count <= $s_len)
  {
    $s_zero = str_repeat('0', ($s_len-$s_count));
  }
  return $s_zero.$s_text;
}

function showFloat($s_data, $s_decimal=2)
{
  if(is_numeric($s_data)) return number_format($s_data, $s_decimal, '.', ',');
  else return number_format(0, $s_decimal, '.', ',');
}

function getExtensionFile($s_fullname, $s_name)
{
  $idx = explode( '.', $s_fullname );
  $count_explode = count($idx);
  if($count_explode == 1)
  {
    $name = $s_name;
  }
  else
  {
    $idx = strtolower($idx[$count_explode-1]);
    $name = $s_name.'.'.$idx;
  }
  return $name;
}

function getExtension($s_fullname)
{
  $idx = explode( '.', $s_fullname );
  $count_explode = count($idx);
  if($count_explode == 1)
  {
    return '';
  }
  else
  {
    $idx = strtolower($idx[$count_explode-1]);
    return $idx;
  }
}


  function randomPassword($var_MaxLength = "8")
  {
    $var_Password = "";
    $var_Possible = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdfghijklmnopqrstuvwxyz";
    while(($i < $var_MaxLength)&&(mb_strlen($var_Possible) > 0))
    {
      $i++;
      $var_Character = mb_substr($var_Possible, mt_rand(0, mb_strlen($var_Possible)-1), 1);
      $var_Possible = preg_replace("/$var_Character/", "", $var_Possible);
      $var_Password .= $var_Character;
    }
    return $var_Password;
  }
?>