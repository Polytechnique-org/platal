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
    $Id: user.func.inc.php,v 1.5 2004-11-22 07:40:17 x2000habouzit Exp $
 ***************************************************************************/

// {{{ function user_clear_all_subs()
/** kills the inscription of a user.
 * we still keep his birthdate, adresses, and personnal stuff
 * kills the entreprises, mentor, emails and lists subscription stuff
 */
function user_clear_all_subs($user_id, $really_del=true)
{
    // keep datas in : aliases, adresses, applis_ins, binets_ins, contacts, groupesx_ins, homonymes, identification_ax, photo
    // delete in     : auth_user_md5, auth_user_quick, competences_ins, emails, entreprises, langues_ins, mentor,
    //                 mentor_pays, mentor_secteurs, newsletter_ins, perte_pass, requests, user_changes, virtual_redirect, watch_sub
    // + delete maillists

    global $globals;
    $uid=intval($user_id);
    $res = $globals->db->query("select alias from aliases where type='a_vie' AND id=$uid");
    list($alias) = mysql_fetch_row($res);
    mysql_free_result($res);

    if ($really_del) {
	$globals->db->query("delete from emails where uid=$uid");
	$globals->db->query("delete from newsletter_ins where user_id=$uid");
    }

    $globals->db->query("delete from virtual_redirect where redirect ='$alias@m4x.org'");
    $globals->db->query("delete from virtual_redirect where redirect ='$alias@polytechnique.org'");

    $globals->db->query("update auth_user_md5 SET passwd='',smtppass='' WHERE user_id=$uid");
    $globals->db->query("update auth_user_quick SET watch_flags='' WHERE user_id=$uid");

    $globals->db->query("delete from competences_ins where uid=$user_id");
    $globals->db->query("delete from entreprises     where uid=$user_id");
    $globals->db->query("delete from langues_ins     where uid=$user_id");
    $globals->db->query("delete from mentor_pays    where uid=$user_id");
    $globals->db->query("delete from mentor_secteur where uid=$user_id");
    $globals->db->query("delete from mentor         where uid=$user_id");
    $globals->db->query("delete from perte_pass where uid=$uid");
    $globals->db->query("delete from requests where user_id=$uid");
    $globals->db->query("delete from user_changes where user_id=$uid");
    $globals->db->query("delete from watch_sub where uid=$uid");
    
    require_once('xml-rpc-client.inc.php');
    $client = new xmlrpc_client("http://{$_SESSION['uid']}:{$_SESSION['password']}@localhost:4949/polytechnique.org");
    $client->kill($alias, $really_del);
}

// }}}
// {{{ function inscription_forum_promo()

/** inscrit l'uid donnée au forum promo 
 * @param $uid UID
 * @param $promo promo
 * @return la reponse MySQL
 * @see step4.php
 */
function inscription_forum_promo($uid,$promo)
{
    global $globals;
    // récupération de l'id du forum promo
    $result=$globals->db->query("SELECT fid FROM forums.list WHERE nom='xorg.promo.x$promo'");
    if (!list($fid)=mysql_fetch_row($result)) { // pas de forum promo, il faut le créer
	$req_au=$globals->db->query("SELECT count(*) FROM auth_user_md5 WHERE promo='$promo' AND perms IN ('admin','user')");
	list($effau) = mysql_fetch_row($req_au);
	$req_id=$globals->db->query("SELECT count(*) FROM auth_user_md5 WHERE promo='$promo'");
	list($effid) = mysql_fetch_row($req_id);
	if (5*$effau>$effid) { // + de 20% d'inscrits
	    require_once("xorg.mailer.inc.php");
	    $mymail = new XOrgMailer('forums.promo.tpl');
	    $mymail->assign('promo', $promo);
	    $mymail->send();
	}
	$fid = false; 
    }
    mysql_free_result($result);
    if ($fid) {
	$globals->db->query("INSERT INTO forums.abos (fid,uid) VALUES ('$fid','$uid')");
	$res = !($globals->db->err());
    } else  $res = false;
    return $res;
} 

// }}}
// {{{ function inscription_forums()

/** inscrit UID aux forums par défaut
 * @param $uid UID
 * @return la reponse MySQL globale
 * @see step4.php
 */
function inscription_forums($uid)
{
    global $globals;
    $res = true;
    $cible = array('xorg.general','xorg.pa.emploi','xorg.pa.divers','xorg.pa.logements');
    while (list ($key, $val) = each ($cible)) {
	$result=$globals->db->query("SELECT fid FROM forums.list WHERE nom='$val'");
	list($fid)=mysql_fetch_row($result);
	$globals->db->query("INSERT INTO forums.abos (fid,uid) VALUES ('$fid','$uid')");
	$res = $res and !($globals->db->err());
    }
    return $res;
}

// }}}
// {{{ function inscription_listes_base()

/** inscrit l'uid donnée à la promo
 * @param $uid UID
 * @param $promo promo
 * @return reponse MySQL
 * @see admin/RegisterNewUser.php
 * @see step4.php
 */
function inscription_listes_base($uid,$pass,$promo)
{
    require_once('xml-rpc-client.inc.php');
    require_once('newsletter.inc.php');
    global $globals;
    // récupération de l'id de la liste promo
    $client = new xmlrpc_client("http://$uid:$pass@localhost:4949/polytechnique.org");
    $client->subscribe("promo$promo");
    subscribe_nl();
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
