<?php

function smarty_insert_serv_to_str($params, &$smarty) {
    $flags = explode(',',implode(',',$params));
    $ret = Array();
    foreach($flags as $flag)
        switch($flag) {
            case "web":
              $ret[] = "site web";
              break;
            case "mail":
              $ret[] = "redirection mail";
              break;
            case "smtp":
              $ret[] = "serveur s&eacute;curis&eacute; d'envoi de mails";
              break;
            case "nntp":
              $ret[] = "serveur des forums de discussion";
              break;
          }
    return implode(', ',$ret);
}

?>
