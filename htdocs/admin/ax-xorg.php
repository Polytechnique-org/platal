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
        $Id: ax-xorg.php,v 1.3 2004-08-31 10:03:29 x2000habouzit Exp $
 ***************************************************************************/

require('auto.prepend.inc.php');
new_admin_page('admin/ax-xorg.tpl');

// liste des différences
$sql = "SELECT i.promo,i.nom AS nom,i.prenom AS prenom,ia.nom AS nomax,ia.prenom AS prenomax,i.matricule AS mat,ia.matricule_ax AS matax
        FROM identification AS i
        INNER JOIN identification_ax AS ia ON i.matricule_ax = ia.matricule_ax
        WHERE (SOUNDEX(i.nom) != SOUNDEX(ia.nom) AND SOUNDEX(CONCAT(ia.particule,i.nom)) != SOUNDEX(ia.nom)
            AND SOUNDEX(i.nom) != SOUNDEX(ia.nom_patro) AND SOUNDEX(CONCAT(ia.particule,i.nom)) != SOUNDEX(ia.nom_patro))
            OR i.prenom != ia.prenom
	    OR (i.promo != ia.promo AND i.promo != ia.promo+1 AND i.promo != ia.promo-1)
	    ORDER BY i.promo,i.nom,i.prenom";
$page->mysql_assign($sql,'diffs','nb_diffs');

// gens à l'ax mais pas chez nous
$sql = "SELECT ia.promo,ia.nom,ia.nom_patro,ia.prenom
        FROM identification_ax as ia
        LEFT JOIN identification AS i ON i.matricule_ax = ia.matricule_ax
        WHERE i.nom IS NULL";
$page->mysql_assign($sql,'mank','nb_mank');

// gens chez nous et pas à l'ax
$sql = "SELECT promo,nom,prenom FROM identification WHERE matricule_ax IS NULL";
$page->mysql_assign($sql,'plus','nb_plus');


$page->run();
?>
