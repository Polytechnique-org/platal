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
        $client =& lists_xmlrpc(Session::getInt('id'), Session::get('password'));
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
            $page->trig("il n'y a pas d'utilisateur avec cet id");
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
// {{{ function get_user_details()

function &get_user_details($login, $from_uid = '')
{
    global $globals;
    $reqsql = "SELECT  u.prenom, u.nom, u.epouse,
                       IF(gp.nat='',gp.pays,gp.nat) AS nationalite, gp.a2 AS iso3166,
                       u.user_id, a.alias AS forlife, a2.alias AS bestalias,
                       u.matricule, u.perms IN ('admin','user') AS inscrit,
                       FIND_IN_SET('femme', u.flags) AS sexe, u.deces != 0 AS dcd, u.deces,
                       u.date, u.cv, sections.text AS section, u.mobile, u.web,
                       u.libre, u.promo, c.uid IS NOT NULL AS is_contact, p.x, p.y,

                       m.expertise != '' AS is_referent
                       
                 FROM  auth_user_md5  AS u
           INNER JOIN  aliases        AS a  ON (u.user_id=a.id AND a.type='a_vie')
           INNER JOIN  aliases        AS a2 ON (u.user_id=a2.id AND FIND_IN_SET('bestalias',a2.flags))
            LEFT JOIN  contacts       AS c  ON (c.uid = '$from_uid' and c.contact = u.user_id)
            LEFT JOIN  geoloc_pays    AS gp ON (gp.a2 = u.nationalite)
           INNER JOIN  sections             ON (sections.id = u.section)
            LEFT JOIN  photo          AS p  ON (p.uid = u.user_id) 
            LEFT JOIN  mentor         AS m  ON (m.uid = u.user_id)
                WHERE  a.alias = '$login'";

    $res  = $globals->db->query($reqsql);
    $user = mysql_fetch_assoc($res);
    mysql_free_result($res);

    $uid = $user['user_id'];

    $sql = "SELECT  e.entreprise, s.label as secteur , ss.label as sous_secteur , f.fonction_fr as fonction,
                    e.poste, e.adr1, e.adr2, e.adr3, e.cp, e.ville,
                    gp.pays, gr.name, e.tel, e.fax
              FROM  entreprises AS e
         LEFT JOIN  emploi_secteur AS s ON(e.secteur = s.id)
         LEFT JOIN  emploi_ss_secteur AS ss ON(e.ss_secteur = ss.id AND e.secteur = ss.secteur)
         LEFT JOIN  fonctions_def AS f ON(e.fonction = f.id)
         LEFT JOIN  geoloc_pays AS gp ON (gp.a2 = e.pays)
         LEFT JOIN  geoloc_region AS gr ON (gr.a2 = e.pays and gr.region = e.region)
             WHERE  e.uid = $uid
          ORDER BY  e.entrid";
    $res = $globals->db->query($sql);
    while($tmp = mysql_fetch_assoc($res)) {
        $user['adr_pro'][] = $tmp;
    }
    mysql_free_result($res);

    $sql = "SELECT  a.adr1,a.adr2,a.adr3,a.cp,a.ville,
                    gp.pays,gr.name AS region,a.tel,a.fax,
                    FIND_IN_SET('active', a.statut) AS active,
                    FIND_IN_SET('res-secondaire', a.statut) AS secondaire
              FROM  adresses AS a
         LEFT JOIN  geoloc_pays AS gp ON (gp.a2=a.pays)
         LEFT JOIN  geoloc_region AS gr ON (gr.a2=a.pays and gr.region=a.region)
             WHERE  uid={$user['user_id']} AND NOT FIND_IN_SET('pro',a.statut)
          ORDER BY  NOT FIND_IN_SET('active',a.statut), FIND_IN_SET('temporaire',a.statut), FIND_IN_SET('res-secondaire',a.statut)";
    $res = $globals->db->query($sql);
    while($tmp = mysql_fetch_assoc($res)) {
        $user['adr'][] = $tmp;
    }
    mysql_free_result($res);


    $sql = "SELECT  text
              FROM  binets_ins
         LEFT JOIN  binets_def ON binets_ins.binet_id = binets_def.id
             WHERE  user_id = {$user['user_id']}";
    $res = $globals->db->query($sql);
    while (list($binet) = mysql_fetch_row($res)) {
        $user['binets'][] = $binet;
    }
    if (mysql_num_rows($res)) {
        $user['binets_join'] = join(', ', $user['binets']);
    }
    mysql_free_result($res);

    $res = $globals->db->query("SELECT  text, url
                                  FROM  groupesx_ins
                             LEFT JOIN  groupesx_def ON groupesx_ins.gid = groupesx_def.id
                                 WHERE  guid = '{$user['user_id']}'");
    while (list($gxt,$gxu) = mysql_fetch_row($res)) {
        if ($gxu) {
            $user['gpxs'][] = "<a href=\"$gxu\">$gxt</a>";
        } else {
            $user['gpxs'][] = $gxt;
        }
    } 
    if (mysql_num_rows($res)) {
        $user['gpxs_join'] = join(', ', $user['gpxs']);
    }
    mysql_free_result($res);

    $res = $globals->db->query("SELECT  applis_def.text, applis_def.url, applis_ins.type
                                  FROM  applis_ins
                            INNER JOIN  applis_def ON applis_def.id = applis_ins.aid
                                 WHERE  uid='{$user['user_id']}'
                              ORDER BY  ordre");
    
    while (list($type, $txt, $url) = mysql_fetch_assoc($res)) {
        require_once('applis.func.inc.php');
        $user['applis_fmt'][] = applis_fmt($type, $txt, $url);
    }
    if (mysql_num_rows($res)) {
        $user['applis_join'] = join(', ', $user['applis_fmt']);
    }
    mysql_free_result($res);

    return $user;
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
