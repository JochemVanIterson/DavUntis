<?php
function JsonParser($file){
  $str = file_get_contents($file);
  $json = json_decode($str, true);
  return $json;
}
?>
