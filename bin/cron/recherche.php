#!/usr/bin/php5 -q
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

require('./connect.db.inc.php');

$res = XDB::iterRow('SELECT matricule,nom1,nom2,nom3,prenom1,prenom2,promo FROM recherche');

while (list($matricule,$nom1,$nom2,$nom3,$prenom1,$prenom2,$promo) = $res->next()) {
    XDB::execute(
            "INSERT INTO  recherche_soundex (matricule,nom1_soundex,nom2_soundex,nom3_soundex,prenom1_soundex,prenom2_soundex,promo)
                  VALUES  ({?},{?},{?},{?},{?},{?},{?})",
            $matricule, soundex_fr($nom1), soundex_fr($nom2), soundex_fr($nom3), soundex_fr($prenom1), soundex_fr($prenom2), $promo);
}

?>
