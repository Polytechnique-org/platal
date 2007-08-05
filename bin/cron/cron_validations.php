#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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
/* vim: set sw=4 ts=4 sts=4 tw=100:
 * vérifie qu'il n'y a pas de validations en cours, et maile si c'est le cas
*/ 

$M_PERIOD = "INTERVAL 3 HOUR"; // période d'envoi des mails de 3h
$R_PERIOD = "INTERVAL 6 HOUR"; // période de réponse moyenne de 6h

require('./connect.db.inc.php');
require('plmailer.php');

$res = XDB::query("SELECT count(stamp), sum(stamp < NOW() - $M_PERIOD), sum(stamp < NOW() - $R_PERIOD) FROM x4dat.requests");
list($nb,$nbold,$nbveryold) = $res->fetchOneRow();

if (empty($nb)) {
    exit;
}

$plural = $nb == 1 ? "" : "s";

$mymail = new PlMailer();
$mymail->setFrom('validation@' . $globals->mails->domain);
$mymail->addTo("validation@" . $globals->mails->domain);
$mymail->setSubject((empty($nbveryold)?"":"[urgent] ")."il y a $nb validation$plural non effectuée$plural");

$message =
	"il y a $nb validation$plural à effectuer \n"
	.(empty($nbold)?"":"dont $nbold depuis le dernier mail !!!\n")
	.(empty($nbveryold)?"":"et dont *$nbveryold* ".($nbveryold == 1 ? "est" : "sont")." en retard de plus de 6h !!!")
	."\n"
	."https://www.polytechnique.org/admin/validate\n\n"
        ."Par catégorie :\n";
$res = XDB::iterRow("SELECT  type, count(*)
                       FROM  x4dat.requests
                   GROUP BY  type
                   ORDER BY  type");
while (list($type, $nb) = $res->next()) {
    $message .= "- $type: $nb\n";
}

$message = wordwrap($message,78);  
$mymail->setTxtBody($message);
$mymail->send();
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
