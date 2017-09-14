<?php

class sysCache
{
  private static $_cache=false;

  final public static function fetchCache($path, $name, $encrypt_file_name = true)
  {
    $cacheFile = self::cachePath($path, $name, $encrypt_file_name);
    if(file_exists($cacheFile))
    {
      $cacheContent = file_get_contents($cacheFile);
      return unserialize($cacheContent);
    }
    else return false;
  }

  final public static function fetch($path, $name, $refreshSeconds = 0, $encrypt_file_name = false)
  {
    self::$_cache = false;
    if(!$refreshSeconds) $refreshSeconds = 1800;
    $cacheFile = self::cachePath($path, $name, $encrypt_file_name);
    if(file_exists($cacheFile) and ((time()-filemtime($cacheFile)) < $refreshSeconds))
    {
      $cacheContent = file_get_contents($cacheFile);
      self::$_cache = true;
      return unserialize($cacheContent);
    }
    else return false;
  }

  public static function cache()
  {
    return self::$_cache;
  }

  final public static function save($path, $name, $cacheContent = '', $encrypt_file_name = false)
  {
    $cacheFile = self::cachePath($path, $name, $encrypt_file_name);
    self::savetofile($cacheFile, $cacheContent);
  }

  final private static function cachePath($path, $name, $encrypt_file_name = false)
  {
    $cacheFolder = $path.'/';
    if(!$cacheFolder) $cacheFolder = trim($_SERVER['DOCUMENT_ROOT'],'/').'/cache/';
    if($encrypt_file_name) $filename = md5(strtolower(trim($name)));
    else $filename = strtolower(trim($name));
    return $cacheFolder.$filename.'.cache';
  }

  final public static function savetofile($filename, $data)
  {
    $dir = trim(dirname($filename),'/');
    self::forceDirectory($dir);
    $file = fopen($filename, 'w');
    if($data)
    {
      fwrite($file, serialize($data)); fclose($file);
    }
    else
    {
      fwrite($file, null); fclose($file);
    }
  }

  final private static function forceDirectory($dir)
  {
    return is_dir($dir) or (self::forceDirectory(dirname($dir)) and mkdir($dir, 0777));
  }

}

?>