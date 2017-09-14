<?php

$s_json = file_get_contents(PATH.'api/setting.json');
$a_json = json_decode($s_json, true);

foreach((array)$a_json as $item)
{
  include($item);
}

?>