<?php
function smarty_modifier_url($string)
{
  $chemins = Array('.', '..', '/');
  foreach ($chemins as $ch) {
    if (file_exists("$ch/login.php") || file_exists("$ch/public/login.php"))
      return "$ch/$string";
  }
  return "";
}
?>
