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
    
    include_once('lists.inc.php');
    if (function_exists(lists_xmlrpc)) {
        $client =& lists_xmlrpc($_SESSION['uid'], $_SESSION['password']);
        $client->kill($alias, $really_del);
    }
}

// }}}
// {{{ function get_user_login()

function get_user_login($data, $get_forlife = false) {
    global $globals, $page;

    if (preg_match(',^[0-9]*$,', $data)) {
        $res = $globals->db->query("SELECT alias FROM aliases WHERE type='a_vie' AND id=$data");
        if (!mysql_num_rows($res)) {
            $alias = false;
        } else {
            list($alias) = mysql_fetch_row($res);
        }
        mysql_free_result($res);
        return $alias;
    }

    $data = trim(strtolower($data));

    if (strstr($data, '@')===false) {
        $data = $data.'@'.$globals->mail->domain;
    }
    
    list($mbox, $fqdn) = split('@', $data);
    if ($fqdn == $globals->mail->domain || $fqdn == $globals->mail->domain2) {

        $res = $globals->db->query("SELECT  a.alias
                                      FROM  aliases AS a
                                INNER JOIN  aliases AS b ON (a.id = b.id AND b.type IN ('alias', 'a_vie') AND b.alias='$mbox')
                                     WHERE  a.type = 'a_vie'");
        if (mysql_num_rows($res)) {
            if ($get_forlife) {
                list($alias) = mysql_fetch_row($res);
            } else {
                $alias = $mbox;
            }
        } else {
            $page->trig("il n'y a pas d'utilisateur avec ce login");
            $alias = false;
        }
        mysql_free_result($res);
        return $alias;

    } elseif ($fqdn == $globals->mail->alias_dom || $fqdn == $globals->mail->alias_dom2) {
    
        $res = $globals->db->query("SELECT  redirect
                                      FROM  virtual_redirect
                                INNER JOIN  virtual USING(vid)
                                     WHERE  alias='$mbox@{$globals->mail->alias_dom}'");
        if (list($redir) = mysql_fetch_row($res)) {
            list($alias) = split('@', $redir);
        } else {
            $page->trig("il n'y a pas d'utilisateur avec cet alias");
            $alias = false;
        }
        mysql_free_result($res);
        return $alias;

    } else {

        $res = $globals->db->query("SELECT  alias
                                      FROM  aliases AS a
                                INNER JOIN  emails  AS e ON e.uid=a.id
                                     WHERE  e.email='$data' AND a.type='a_vie'");
        switch ($i = mysql_num_rows($res)) {
            case 0:
                $page->trig("il n'y a pas d'utilisateur avec cette addresse mail");
                $alias = false;
                break;
                
            case 1:
                list($alias) = mysql_fetch_row($res);
                break;
                
            default:
                $alias = false;
                if (has_perms()) {
                    $aliases = Array();
                    while (list($a) = mysql_fetch_row($res)) $aliases[] = $a;
                    $page->trig("Il y a $i utilisateurs avec cette adresse mail : ".join(', ', $aliases));
                }
        }
        mysql_free_result($res);
        return $alias;
    }
}

// }}}
// {{{ function get_user_forlife()

function get_user_forlife($data) {
    return get_user_login($data, true);
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
