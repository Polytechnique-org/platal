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
 ***************************************************************************/

require_once('xorg.inc.php');
new_admin_page('admin/ax-xorg.tpl');

// liste des différences
$sql = "SELECT  u.promo,u.nom AS nom,u.prenom AS prenom,ia.nom AS nomax,ia.prenom AS prenomax,u.matricule AS mat,ia.matricule_ax AS matax
          FROM  auth_user_md5 AS u
    INNER JOIN  identification_ax AS ia ON u.matricule_ax = ia.matricule_ax
         WHERE  (SOUNDEX(u.nom) != SOUNDEX(ia.nom) AND SOUNDEX(CONCAT(ia.particule,u.nom)) != SOUNDEX(ia.nom)
                AND SOUNDEX(u.nom) != SOUNDEX(ia.nom_patro) AND SOUNDEX(CONCAT(ia.particule,u.nom)) != SOUNDEX(ia.nom_patro))
                OR u.prenom != ia.prenom OR (u.promo != ia.promo AND u.promo != ia.promo+1 AND u.promo != ia.promo-1)
      ORDER BY  u.promo,u.nom,u.prenom";
$page->mysql_assign($sql,'diffs','nb_diffs');

// gens à l'ax mais pas chez nous
$sql = "SELECT  ia.promo,ia.nom,ia.nom_patro,ia.prenom
          FROM  identification_ax as ia
     LEFT JOIN  auth_user_md5 AS u ON u.matricule_ax = ia.matricule_ax
         WHERE  u.nom IS NULL";
$page->mysql_assign($sql,'mank','nb_mank');

// gens chez nous et pas à l'ax
$sql = "SELECT promo,nom,prenom FROM auth_user_md5 WHERE matricule_ax IS NULL";
$page->mysql_assign($sql,'plus','nb_plus');


$page->run();
?>
