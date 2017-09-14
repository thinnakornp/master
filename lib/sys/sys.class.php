<?php

class sys
{
  protected static $_agent;
  protected static $_platform;
  protected static $_browser;
  protected static $_version;
  protected static $_ip;
  protected static $_port;
  protected static $_protocal;
  protected static $_domain;
  protected static $_name;
  protected static $_path;
  protected static $_url;
  protected static $_site;
  protected static $_module;
  protected static $_page;
  protected static $_cache;
  protected static $_cache_time;

  public static function init($s_path=false)
  {
    sysBrowser::Browser();
    self::$_path = $s_path;
    self::$_agent = sysBrowser::getUserAgent();
    self::$_platform = sysBrowser::getPlatform();
    self::$_browser = sysBrowser::getBrowser();
    self::$_version = sysBrowser::getVersion();
    self::$_ip = $_SERVER['REMOTE_ADDR'];
    self::$_port = (isset($_SERVER['SERVER_PORT']))?$_SERVER['SERVER_PORT']:'80';
    self::$_protocal = (isset($_SERVER['HTTPS']))?'https':'http';
    $s_host = $_SERVER['HTTP_HOST'];
    if((self::$_port != 80) && (self::$_port != 443) && (mb_substr($s_host, mb_strlen($s_host) - mb_strlen(self::$_port)) != self::$_port))
      self::$_domain = $s_host.':'.self::$_port;
    else self::$_domain = $s_host;

    $s_name = $_SERVER['SCRIPT_NAME'];
    $s_url = $_SERVER['REQUEST_URI'];
    if(self::$_path)
    {
      $s_temp = '/^\/'.addcslashes(self::$_path, '/').'\//i';
      $s_url = preg_replace($s_temp, '', $s_url, 1);
      $s_name = preg_replace($s_temp, '', $s_name, 1);
    }
    if((preg_match('@^\/@i', $s_url)))
    {
      $s_len = mb_strlen($s_url);
      $s_url = mb_substr($s_url, 1, $s_len);
    }
    self::$_url = $s_url;
    self::$_name = $s_name;
    self::setModules();
    self::preview();
  }

  private static function setModules()
  {
    $s_method = strtok(self::$_url, '?');
    if($s_method)
    {
      $a_method = explode('/', $s_method);
      if(count($a_method) <3)
      {
        self::setDefaultMod();
        return false;
      }
      $chk_site = (is_dir(PATH.'/apps/'.$a_method[0]))?true:false;
      $chk_module = (is_dir(PATH.'/apps/'.$a_method[0].'/'.$a_method[1]))?true:false;
      $chk_page = (is_file(PATH.'/apps/'.$a_method[0].'/'.$a_method[1].'/views/'.$a_method[2].'.php') || is_file(PATH.'/apps/'.$a_method[0].'/'.$a_method[1].'/actions/'.$a_method[2].'.php'))?true:false;
      if($chk_site && $chk_module)
      {
        self::$_site = $a_method[0];
        self::$_module = $a_method[1];
        include(PATH.'/apps/'.$a_method[0].'/'.$a_method[1].'/config.inc.php');
        if($chk_page)
        {
          self::$_page = $a_method[2];
        }
        else
        {
          self::$_page = SYS_MOD_DEFAULT;
        }
      }
      else
      {
        self::setDefaultMod();
      }
    }
    else
    {
      self::setDefaultMod();
    }
  }

  private static function setDefaultMod()
  {
    self::$_site = SYS_SITE;
    self::$_module = SYS_MODULE;
    self::$_page = SYS_PAGE;
  }

  public static function getSite()
  {
    return self::$_site;
  }

  public static function getModule()
  {
    return self::$_module;
  }

  public static function getPage()
  {
    return self::$_page;
  }

  private static function preview()
  {
    $_path = PATH.'/apps/'.self::$_site.'/'.self::$_module.'/';
    if(($_POST || $_FILES) && is_file($_path.'actions/'.self::$_page.'.php'))
    {
      include($_path.'actions/'.self::$_page.'.php');
    }
    if($_cache = sys::getCache() && SYSTEM_CACHE)
    {
      $_cache_time = (defined('SYS_CACHE'))?SYS_CACHE:60;
      if(file_exists('cache/'.$_cache) and ((time()-filemtime('cache/'.$_cache)) < $_cache_time))
      {
        include('cache/'.$_cache);
      }
      else
      {
        include($_path.'/views/'.self::$_page.'.php');
        sysFile::forceDirectory('cache/');
        file_put_contents('cache/'.$_cache, ob_get_contents());
      }
    }
    else
    {
/*
      if(is_file($_path.'/controls/'.self::$_page.'.php'))
      {
        include($_path.'/controls/'.self::$_page.'.php');
      }
*/
      include($_path.'/views/'.self::$_page.'.php');
      if(self::$_cache && SYSTEM_CACHE)
      {
        $s_cache_file = 'cache/'.self::$_cache;
        sysFile::forceDirectory('cache/');
        file_put_contents($s_cache_file, ob_get_contents());
      }
    }
  }

  public static function setCache($a_value=false)
  {
    $_cache = self::$_site.'/'.self::$_module.'/'.self::$_page;
    $_value = false;
    if($a_value)
    {
      foreach($a_value as $_item)
      {
        $_value[$_item] = $_GET[$_item];
      }
    }
    if($_value)
    {
      $_cache.= '?'.http_build_query($_value);
    }
    self::$_cache = base64_encode($_cache);
  }

  public static function getCache()
  {
    $_cache = self::$_site.'/'.self::$_module.'/'.self::$_page;
    if($_GET)
    {
      $_cache.= '?'.http_build_query($_GET);
    }
    if(is_file('cache/'.base64_encode($_cache)))
    {
      return base64_encode($_cache);
    }
    return false;
  }

  public static function debug()
  {
    echo 'Path:'.self::$_path.'<br />';
    echo 'Agent:'.self::$_agent.'<br />';
    echo 'Platform:'.self::$_platform.'<br />';
    echo 'Browser:'.self::$_browser.'<br />';
    echo 'Version:'.self::$_version.'<br />';
    echo 'IP:'.self::$_ip.'<br />';
    echo 'Port:'.self::$_port.'<br />';
    echo 'Protocal:'.self::$_protocal.'<br />';
    echo 'URL:'.self::$_url.'<br />';
    echo 'Filename:'.self::$_name.'<br />';
    echo 'Site:'.self::$_site.'<br />';
    echo 'Module:'.self::$_module.'<br />';
    echo 'Page:'.self::$_page.'<br />';
  }

  public static function getURL()
  {
    $s_url = self::$_protocal.'://'.self::$_domain.'/';
    if(self::$_path) $s_url.= self::$_path.'/';
    return $s_url;
  }

  final public static function getCurrent()
  {
    return self::getURL().self::$_site.'/'.self::$_module.'/'.self::$_page;
  }

  final public static function getModCurrent()
  {
    return self::$_site.'/'.self::$_module.'/'.self::$_page;
  }

  final public static function getValue($s_ext = false)
  {
    if($s_ext)
    {
      $a_data = $_GET;
      $a_ext = explode(',', $s_ext);
      foreach($a_ext as $_item)
      {
        unset($a_data[$_item]);
      }
      return $a_data;
    }
    return false;
  }

}


?>