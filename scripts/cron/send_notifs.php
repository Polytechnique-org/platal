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
        $Id: send_notifs.php,v 1.3 2004-11-16 21:02:47 x2000habouzit Exp $
 ***************************************************************************/

require('./connect.db.inc.php');
require("../../include/notifs.inc.php");
require("diogenes.hermes.inc.php");
require("diogenes.misc.inc.php");

$all = new AllNotifs();

foreach($all->_data as $u) {
    $text =  "  ".($u['sexe'] ? 'Chère' : 'Cher')." {$u['prenom']},\n\n"
          .  "  Voici les évènements survenus dans la semaine écoulée,\n"
	  .  "et depuis ta dernière visite sur le site.\n\n"
	  .  "Tu trouveras les mêmes informations sur la page :\n"
	  .  "    $baseurl/carnet/panel.php\n\n";
    $text .= "------------------------------------------------------------\n\n";
    foreach($u['data'] as $cid=>$d) {
	$text .= "  {$all->_cats[$cid]['mail']} :\n\n";
	foreach($d as $promo=>$x) {
	    $text .= "    - (X{$x['promo']}) {$x['prenom']} {$x['nom']} le {$x['date']}\n";
	}
	$text .= "\n";
    }

    $text .= "------------------------------------------------------------\n\n"
           . "Tu recois ce mail car tu as activé la notification\n"
	   . "automatique par mail des évènements que tu surveilles.\n\n"
	   . "Tu peux changer cette option sur :\n"
	   . "    $baseurl/carnet/notifs.php\n\n"
	   . "-- \n"
	   . "L'équipe de Polytechnique.org";
    
    $mailer = new HermesMailer();
    $mailer->setFrom("Carnet Polytechnicien <support_carnet@polytechnique.org>");
    $mailer->addTo("\"{$u['prenom']} {$u['nom']}\" <{$u['forlife']}@polytechnique.org>");
    $mailer->setSubject("Notifications de la semaine ".date("W - Y"));
    $mailer->setTxtBody($text);
    $mailer->send();
}

?>
