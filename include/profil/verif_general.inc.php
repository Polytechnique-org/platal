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
        $Id: verif_general.inc.php,v 1.5 2004-08-31 16:04:11 x2000habouzit Exp $
 ***************************************************************************/


// validité du mobile
if (strlen(strtok($mobile,"<>{}@&#~\/:;?,!§*_`[]|%$^=")) < strlen($mobile)) {
    $errs[] = "Le champ 'Téléphone mobile' contient un caractère interdit."; 
}

// correction du champ web si vide
if ($web=="http://" or $web == '') {
    $web='';
} elseif (!preg_match("{^(https?|ftp)://[a-zA-Z0-9._%#+/?=&~-]+$}i", $web)) {
    // validité de l'url donnée dans web
    $errs[] = "URL incorrecte dans le champ 'Page web perso', une url doit commencer par http:// ou https:// ou ftp:// et ne pas contenir de caractères interdits";
} else {
    $web = str_replace('&', '&amp;', $web);
}

//validité du champ libre
if (strlen(strtok($libre,"<>")) < strlen($libre))
{
    $errs[] = "Le champ 'Complément libre' contient un caractère interdit.";
}

?>
