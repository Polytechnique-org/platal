<?php

// validité du mobile
if (strlen(strtok($mobile,"<>{}@&#~\/:;?,!§*_`[]|%$^=")) < strlen($mobile))
{
  $str_error = $str_error."Le champ 'Téléphone mobile' contient un caractère interdit.<BR />"; 
}

// correction du champ web si vide
if ($web=="http://" or $web == '') {
  $web='';
} elseif (!preg_match("{^(https?|ftp)://[a-zA-Z0-9._%#+/?=&~-]+$}i", $web)) {
  // validité de l'url donnée dans web
  $str_error = $str_error."URL incorrecte dans le champ 'Page web perso', une url doit commencer par http:// ou https:// ou ftp:// et ne pas contenir de caractères interdits<BR />";
} else {
  $web = str_replace('&', '&amp;', $web);
}

//validité du champ libre
if (strlen(strtok($libre,"<>")) < strlen($libre))
{
  $str_error = $str_error."Le champ 'Complément libre' contient un caractère interdit.<BR />";
}

?>
