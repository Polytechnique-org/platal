<?php

function error($_type) {
  global $locale,$css,$group;
  switch ($_type) {
    case "nntpsock":
      echo "<p class=\"error\">\n\t".$locale['error']['connect']."\n</p>";
      break;  
    case "nntpauth":
      echo "<p class=\"error\">\n\t".$locale['error']['credentials']
        ."\n</p>";
      break;
    case "nntpgroups":
      echo "<p class=\"{$css['normal']}\">";
      echo "\n".$locale['error']['nogroup']."\n";
      echo "</p>\n";
      break;
    case "nntpspool":
      echo "<div class=\"{$css['bananashortcuts']}\">\n";
      echo "[<a href=\"index.php\">Liste des forums</a>]\n";
      echo "</div>\n";
      echo "<p class=\"error\">\n\t".$locale['error']['group']."\n</p>";
      break;
    case "nntpart":
      echo "<div class=\"{$css['bananashortcuts']}\">\n";
      echo "[<a href=\"index.php\">Liste des forums</a>] \n";
      echo "[<a href=\"thread.php?group=$group\">$group</a>] \n";
      echo "</div>\n";
      echo "<p class=\"error\">\n\t".$locale['error']['post']."\n</p>";
      break;
  }
}

?>
