<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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
 ***************************************************************************/
 
function strmatch_whole_words($nouveau, $ancien) {
    $nouveau = strtoupper($nouveau);
    $ancien = strtoupper($ancien);
    $len_nouveau = strlen($nouveau);
    return (($i = strpos($ancien, $nouveau)) !== false && ($i == 0 || $ancien{$i-1} == ' ' || $ancien{$i-1} == '-') && ($i + $len_nouveau == strlen($ancien) || $ancien{$i + $len_nouveau} == ' ' || $ancien{$i+$len_nouveau} == '-'));
}

// validite du nom
if ($nom != $nom_anc &&
    !strmatch_whole_words($nom_comp, $nom_anc_comp) &&
    ($nom_anc_comp == $nom_ini || !strmatch_whole_words($nom_comp, $nom_ini))) {
    $page->trig("Le nom que tu as choisi ($nom) est trop loin de ton nom initial ($nom_ini)".(($nom_ini==$nom_anc_comp)?"":" et de ton nom précédent ($nom_anc)"));
}

// validite du prenom
if ($prenom != $prenom_anc &&
    !strmatch_whole_words($prenom_comp, $prenom_anc_comp) &&
    ($prenom_anc_comp == $prenom_ini || !strmatch_whole_words($prenom_comp, $prenom_ini))) {
    $page->trig("Le prénom que tu as choisi ($prenom) est trop loin de ton prénom initial ($prenom_ini)".(($prenom_ini==$prenom_anc_comp)?"":" et de ton prénom précédent ($prenom_anc)"));
}

// validité du mobile
if (strlen(strtok($mobile,"<>{}@&#~\/:;?,!§*_`[]|%$^=")) < strlen($mobile)) {
    $page->trig("Le champ 'Téléphone mobile' contient un caractère interdit.");
}

// correction du champ web si vide
if ($web=="http://" or $web == '') {
    $web='';
} elseif (!preg_match("{^(https?|ftp)://[a-zA-Z0-9._%#+/?=&~-]+$}i", $web)) {
    // validité de l'url donnée dans web
    $page->trig("URL incorrecte dans le champ 'Page web perso', une url doit commencer par
                    http:// ou https:// ou ftp:// et ne pas contenir de caractères interdits");
} else {
    $web = str_replace('&', '&amp;', $web);
}

//validité du champ libre
if (strlen(strtok($freetext,"<>")) < strlen($freetext))
{
    $page->trig("Le champ 'Complément libre' contient un caractère interdit.");
}

// vim:set et sws=4 sts=4 sw=4:
?>
