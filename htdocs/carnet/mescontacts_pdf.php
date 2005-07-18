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
new_skinned_page('index.tpl', AUTH_COOKIE);
require_once('contacts.pdf.inc.php');
require_once('user.func.inc.php');

$sql = "SELECT  a.alias
          FROM  aliases       AS a
    INNER JOIN  auth_user_md5 AS u ON ( a.id = u.user_id )
    INNER JOIN  contacts      AS c ON ( a.id = c.contact )
         WHERE  c.uid = {?} AND a.type='a_vie'";
if (Get::get('order') == "promo") {
    $sql .= " ORDER BY  u.promo, u.nom, u.prenom";
} else {
    $sql .= " ORDER BY  u.nom, u.prenom, u.promo";
}

$citer = $globals->xdb->iterRow($sql, Session::getInt('uid'));
$pdf   = new ContactsPDF();

while (list($alias) = $citer->next()) {
    $user = get_user_details($alias);
    $pdf->addContact($user, Env::has('photo'));
}
$pdf->Output();

?>
