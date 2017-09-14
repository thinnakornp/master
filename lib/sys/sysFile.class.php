<?php

class sysFile
{
  protected static $_trim=true;

  public static function autoTrim($s_type=true)
  {
    self::$_trim = $s_type;
  }

  public static function clearFolder($s_dir)
  {
    $files = glob( $s_dir . '*', GLOB_MARK );
    foreach( $files as $file )
    {
      if( substr( $file, -1 ) == '/' )
        self::clearFolder( $file );
      else
        unlink( $file );
    }
    rmdir( $s_dir );
  }

  public static function forceDirectory($s_dir)
  {
    return is_dir($s_dir) or (self::forceDirectory(dirname($s_dir)) and mkdir($s_dir, 0777));
  }

  public static function saveFile($s_filename, $s_data, $s_mode='w')
  {
    $a_dir = trim(dirname($s_filename),'/');
    self::forceDirectory($a_dir);
    $file = fopen($s_filename, $s_mode);
    if($s_data)
    {
      if(self::$_trim) fwrite($file, trim($s_data));
      else fwrite($file, $s_data);
      fclose($file);
    }
    else
    {
      fwrite($file, null);
      fclose($file);
    }
  }

  public static function load($s_link)
  {
    $s_link = str_replace(' ', '%20', $s_link);
    return file_get_contents($s_link);
  }

  public static function readFile($s_filename)
  {
    if(is_file($s_filename))
    {
      return file_get_contents($s_filename);
    }
    else return false;
  }

  public static function xcopy($s_source, $s_dest)
  {
    if(is_dir($s_source))
    {
      self::forceDirectory($s_dest);
      $myDirectory = opendir($s_source);
      while($entryName = readdir($myDirectory))
      {
        if(is_file($s_source.'/'.$entryName))
        {
          copy($s_source.'/'.$entryName, $s_dest.'/'.$entryName);
        }
        else if(is_dir($s_source.'/'.$entryName) && $entryName!='.' && $entryName != '..')
        {
          self::xcopy($s_source.'/'.$entryName, $s_dest.'/'.$entryName);
        }
      }
      closedir($myDirectory);
    }
  }

  public static function renameDir($s_source, $s_new, $s_temp='_temp')
  {
    if(is_dir($s_new))
    {
      rename($s_source, $s_source.$s_temp);
      rename($s_new, $s_source);
    }
  }

}

?>