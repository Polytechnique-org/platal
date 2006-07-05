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

// {{{ config HOOK

// {{{ class SkinConfig

class BananaConfig
{
    var $server       = 'localhost';
    var $port         = 119;
    var $password     = '***';
    var $web_user     = '***';
    var $web_pass     = '***';

    var $table_prefix = 'banana_';
}

// }}}

function banana_config()
{
    global $globals;
    $globals->banana = new BananaConfig;
}

// }}}
// {{{ menu HOOK

function banana_menu()
{
    global $globals;
    $globals->menu->addPrivateEntry(XOM_SERVICES, 10, 'Forums & PA', 'banana/');
}

// }}}
// {{{ subscribe HOOK

function banana_subscribe($forlife, $uid, $promo, $password)
{
    global $globals;
    
    $cible = array('xorg.general','xorg.pa.emploi','xorg.pa.divers','xorg.pa.logements');
    $p_for = "xorg.promo.x$promo";
    
    // récupération de l'id du forum promo
    $res = $globals->xdb->query("SELECT fid FROM forums.list WHERE nom={?}", $p_for);
    if ($res->numRows()) {
        $cible[] = $p_for;
    } else { // pas de forum promo, il faut le créer
	$res = $globals->xdb->query("SELECT  SUM(perms IN ('admin','user') AND deces=0),COUNT(*)
                                       FROM  auth_user_md5 WHERE promo={?}", $promo);
	list($effau, $effid) = $res->fetchOneRow();
	if (5*$effau>$effid) { // + de 20% d'inscrits
	    require_once("xorg.mailer.inc.php");
	    $mymail = new XOrgMailer('mails/forums.promo.tpl');
	    $mymail->assign('promo', $promo);
	    $mymail->send();
	}
    }

    while (list ($key, $val) = each ($cible)) {
        $globals->xdb->execute("INSERT INTO  forums.abos (fid,uid)
                                     SELECT  fid,{?} FROM forums.list WHERE nom={?}", $uid, $val);
    }
}

// }}}
 
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
