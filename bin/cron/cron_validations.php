#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

/** Check if there is some pending validations,
 *  and if so sends notification email.
 */

$M_PERIOD = "INTERVAL 3 HOUR"; // 3 hour lap (old validations)
$R_PERIOD = "INTERVAL 6 HOUR"; // 6 hour lap (very old validations)

require 'connect.db.inc.php';
require 'plmailer.php';

$res = XDB::query("SELECT  count(r.stamp), UNIX_TIMESTAMP(MIN(r.stamp)),
                           sum(r.stamp < NOW() - $M_PERIOD), sum(r.stamp < NOW() - $R_PERIOD)
                     FROM  requests AS r");
list($nb, $age, $nbold, $nbveryold) = $res->fetchOneRow();

$age = (time() - intval($age)) / 86400;
$head = '';
if ($age > 15) {
    $head = "[Autodestruction du serveur] ";
} elseif ($age > 7) {
    $head = "[Armageddon imminent] ";
} elseif ($age > 5) {
    $head = "[Guerre nucléaire] ";
} elseif ($age > 3) {
    $head = "[ET Téléphone maison] ";
} elseif ($age > 1) {
    $head = "[Réveil !] ";
} elseif (!empty($nbveryold)) {
    $head = "[Urgent] ";
}


if (empty($nb)) {
    exit;
}

$plural = $nb == 1 ? '' : 's';

$mymail = new PlMailer();
$mymail->setFrom('validation@' . $globals->mail->domain);
$mymail->addTo('validation@' . $globals->mail->domain);
$mymail->setSubject($head . "Il y a $nb validation$plural non effectuée$plural");

$message =
	"Il y a $nb validation$plural à effectuer\n"
	. (empty($nbold) ? '' : "dont $nbold depuis le dernier mail !!!\n")
	. (empty($nbveryold) ? '' : "et dont *$nbveryold* " . ($nbveryold == 1 ? 'est' : 'sont') . ' en retard de plus de 6h !!!')
	. "\n"
	. "https://www.polytechnique.org/admin/validate\n\n"
  . "Par catégorie :\n";
$res = XDB::iterRow('SELECT  type, count(*)
                       FROM  requests
                   GROUP BY  type
                   ORDER BY  type');
while (list($type, $nb) = $res->next()) {
    $message .= "- $type : $nb\n";
}

$message = wordwrap($message, 78);
$mymail->setTxtBody($message);
$mymail->send();

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
