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

global $adresses;
reset($adresses);

function insert_new_tel($adrid, $tel) {
    if ($tel['tel'] == "")
        return;
    XDB::execute( "INSERT INTO tels SET tel_type = {?}, tel_pub = {?},
                  tel = {?}, uid = {?}, adrid = {?}, telid = {?}",
                  $tel['tel_type'], $tel['tel_pub'], $tel['tel'],
                  S::v('uid', -1), $adrid, $tel['telid']);
}

foreach ($adresses as $adrid => $adr) {

    if ($adr['nouvelle'] != 'new') {
        // test si on vient de creer cette adresse dans verif_adresse.inc.php

        //construction des bits
        $statut = "";
        if ($adr["secondaire"])    $statut .= 'res-secondaire,';
        if ($adr["courrier"])      $statut .= 'courrier,';
        if ($adr["active"])        $statut .= 'active,';
        if ($adr["temporaire"])    $statut .= 'temporaire,';
        if (! empty($statut)) $statut = substr($statut, 0, -1);

        if ($adr["nouvelle"] == 'ajout') {
            //nouvelle adresse
            XDB::execute("INSERT INTO adresses SET adr1 = {?}, adr2 = {?},
                         adr3 = {?}, postcode = {?}, city = {?}, cityid = {?},
                         country = {?}, region = {?}, regiontxt = {?},
                         pub = {?}, datemaj = NOW(), statut = {?}, uid = {?},
                         adrid = {?}", $adr['adr1'], $adr['adr2'],
                         $adr['adr3'], $adr['postcode'], $adr['city'],
                         $adr['cityid'], $adr['country'], $adr['region'],
                         $adr['regiontxt'], $adr['pub'], $statut,
                         S::v('uid', -1), $adrid);
            $telsvalues = "";  		 
            foreach ($adr['tels'] as $tel) {
                insert_new_tel($adrid, $tel);
            }
        } else { 
            //c'est une mise à jour
            XDB::execute("UPDATE adresses SET adr1 = {?}, adr2 = {?},
                         adr3 = {?}, postcode = {?}, city = {?}, cityid = {?},
                         country = {?}, region = {?}, regiontxt = {?},
                         pub = {?}, datemaj = NOW(), statut = {?}
                         WHERE uid = {?} AND adrid = {?}", $adr['adr1'],
                         $adr['adr2'], $adr['adr3'], $adr['postcode'],
                         $adr['city'], $adr['cityid'], $adr['country'],
                         $adr['region'], $adr['regiontxt'], $adr['pub'],
                         $statut, S::v('uid', -1), $adrid);
            foreach ($adr['tels'] as $tel) {
                if ($tel['new_tel']) {
                    insert_new_tel($adrid, $tel);
                } else {
                    if ($tel['tel'] != "") {
                        XDB::execute(
                            "UPDATE tels SET
                            tel_type = {?},
                            tel_pub = {?},
                            tel = {?}
                            WHERE
                            uid = {?} AND
                            adrid = {?} AND
                            telid = {?}",
                            $tel['tel_type'],
                            $tel['tel_pub'],
                            $tel['tel'],
                            S::v('uid', -1),
                            $adrid,
                            $tel['telid']);
                    } else {
                        XDB::execute(
                            "DELETE FROM tels WHERE
                            uid = {?} AND
                            adrid = {?} AND
                            telid = {?}",
                            S::v('uid', -1),
                            $adrid,
                            $tel['telid']);
                    }
                }
            }
        }// fin nouvelle / ancienne adresse
    }//fin if nouvellement crée
}//fin foreach
?>
