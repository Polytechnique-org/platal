<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: verif_general.inc.php,v 1.3 2004-08-31 14:48:57 x2000habouzit Exp $
 ***************************************************************************/


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

$mobile_public = (isset($_REQUEST['mobile_public']));
$mobile_ax = (isset($_REQUEST['mobile_ax']));
$libre_public = (isset($_REQUEST['libre_public']));
$web_public = (isset($_REQUEST['web_public']));

?>
