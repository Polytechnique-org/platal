#!/usr/bin/php4 -q
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
        $Id: cron_validations.php,v 1.2 2004-10-02 15:34:53 x2000habouzit Exp $
 ***************************************************************************/
/* vim: set sw=4 ts=4 sts=4 tw=100:
 * vérifie qu'il n'y a pas de validations en cours, et maile si c'est le cas
 * 
 * $Id: cron_validations.php,v 1.2 2004-10-02 15:34:53 x2000habouzit Exp $
*/ 

$M_PERIOD = "INTERVAL 3 HOUR"; // période d'envoi des mails de 3h
$R_PERIOD = "INTERVAL 6 HOUR"; // période de réponse moyenne de 6h

require("../../../diogenes/lib/diogenes.mailer.inc.php");
require('./connect.db.inc.php');

$sql = mysql_query("SELECT count(stamp), sum(stamp < NOW() - $M_PERIOD), sum(stamp < NOW() - $R_PERIOD) FROM x4dat.requests");
list($nb,$nbold,$nbveryold) = mysql_fetch_row($sql);
mysql_free_result($sql);

if(empty($nb))
	exit;

$mymail = new DiogenesMailer('validation@polytechnique.org', 
		"validation@polytechnique.org",
		(empty($nbveryold)?"":"[urgent] ")."il y a $nb validations non effectuées",
		false, "");

$message =
	"il y a $nb validation à effectuer \n"
	.(empty($nbold)?"":"dont $nbold depuis le dernier mail !!!\n")
	.(empty($nbveryold)?"":"et dont *$nbveryold* sont en retard de plus de 6h !!!")
	."\n"
	."https://www.polytechnique.org/admin/valider.php\n";

$message = wordwrap($message,78);  
$mymail->setBody($message);
$mymail->send();
?>
