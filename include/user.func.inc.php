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
    $uid   = intval($user_id);
    $res   = $globals->xdb->query("SELECT alias FROM aliases WHERE type='a_vie' AND id={?}", $uid);
    $alias = $res->fetchOneCell();

    if ($really_del) {
	$globals->xdb->execute("DELETE FROM emails WHERE uid={?}", $uid);
	$globals->xdb->execute("DELETE FROM newsletter_ins WHERE user_id={?}", $uid);
    }

    $globals->xdb->execute("DELETE FROM virtual_redirect WHERE redirect = {?}", $alias.'@'.$globals->mail->domain);
    $globals->xdb->execute("DELETE FROM virtual_redirect WHERE redirect = {?}", $alias.'@'.$globals->mail->domain2);

    $globals->xdb->execute("UPDATE auth_user_md5   SET passwd='',smtppass='' WHERE user_id={?}", $uid);
    $globals->xdb->execute("UPDATE auth_user_quick SET watch_flags='' WHERE user_id={?}", $uid);

    $globals->xdb->execute("DELETE FROM competences_ins WHERE uid={?}", $uid);
    $globals->xdb->execute("DELETE FROM entreprises     WHERE uid={?}", $uid);
    $globals->xdb->execute("DELETE FROM langues_ins     WHERE uid={?}", $uid);
    $globals->xdb->execute("DELETE FROM mentor_pays     WHERE uid={?}", $uid);
    $globals->xdb->execute("DELETE FROM mentor_secteur  WHERE uid={?}", $uid);
    $globals->xdb->execute("DELETE FROM mentor          WHERE uid={?}", $uid);
    $globals->xdb->execute("DELETE FROM perte_pass      WHERE uid={?}", $uid);
    $globals->xdb->execute("DELETE FROM requests        WHERE user_id={?}", $uid);
    $globals->xdb->execute("DELETE FROM user_changes    WHERE user_id={?}", $uid);
    $globals->xdb->execute("DELETE FROM watch_sub       WHERE uid={?}", $uid);
    
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
        $res = $globals->xdb->query("SELECT alias FROM aliases WHERE type='a_vie' AND id={?}", $data);
        if ($res->numRows()) {
            return $res->fetchOneCell();
        } else {
            $page->trig("il n'y a pas d'utilisateur avec cet id");
            return false;
        }
    }

    $data = trim(strtolower($data));

    if (strstr($data, '@')===false) {
        $data = $data.'@'.$globals->mail->domain;
    }
    
    list($mbox, $fqdn) = split('@', $data);
    if ($fqdn == $globals->mail->domain || $fqdn == $globals->mail->domain2) {

        $res = $globals->xdb->query("SELECT  a.alias
                                       FROM  aliases AS a
                                 INNER JOIN  aliases AS b ON (a.id = b.id AND b.type IN ('alias', 'a_vie') AND b.alias={?})
                                      WHERE  a.type = 'a_vie'", $mbox);
        if ($res->numRows()) {
            return $get_forlife ? $res->fetchOneCell() : $mbox;
        } else {
            $page->trig("il n'y a pas d'utilisateur avec ce login");
            return false;
        }

    } elseif ($fqdn == $globals->mail->alias_dom || $fqdn == $globals->mail->alias_dom2) {
    
        $res = $globals->xdb->query("SELECT  redirect
                                       FROM  virtual_redirect
                                 INNER JOIN  virtual USING(vid)
                                      WHERE  alias={?}", $mbox.'@'.$globals->mail->alias_dom);
        if ($redir = $res->fetchOneCell()) {
            list($alias) = split('@', $redir);
        } else {
            $page->trig("il n'y a pas d'utilisateur avec cet alias");
            $alias = false;
        }
        return $alias;

    } else {

        $res = $globals->xdb->query("SELECT  alias
                                       FROM  aliases AS a
                                 INNER JOIN  emails  AS e ON e.uid=a.id
                                      WHERE  e.email={?} AND a.type='a_vie'", $data);
        switch ($i = $res->numRows()) {
            case 0:
                $page->trig("il n'y a pas d'utilisateur avec cette addresse mail");
                return false;
                
            case 1:
                return $res->fetchOneCell();
                
            default:
                if (has_perms()) {
                    $aliases = $res->fetchColumn();
                    $page->trig("Il y a $i utilisateurs avec cette adresse mail : ".join(', ', $aliases));
                } else {
                    $res->free();
                }
        }
    }
    
    return false;
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
    $reqsql = "SELECT  u.user_id, u.promo, u.prenom, u.nom, u.epouse, u.date, u.cv,
                       u.perms IN ('admin','user') AS inscrit,  FIND_IN_SET('femme', u.flags) AS sexe, u.deces != 0 AS dcd, u.deces,
                       q.profile_nick AS nickname, q.profile_from_ax, q.profile_mobile AS mobile, q.profile_web AS web, q.profile_freetext AS freetext,
                       q.profile_mobile_pub AS mobile_pub, q.profile_web_pub AS web_pub, q.profile_freetext_pub AS freetext_pub,
                       q.profile_medals_pub AS medals_pub,
                       IF(gp.nat='',gp.pays,gp.nat) AS nationalite, gp.a2 AS iso3166,
                       a.alias AS forlife, a2.alias AS bestalias,
                       c.uid IS NOT NULL AS is_contact,
                       s.text AS section, p.x, p.y, p.pub AS photo_pub,
                       m.expertise != '' AS is_referent
                       
                 FROM  auth_user_md5   AS u
           INNER JOIN  auth_user_quick AS q  USING(user_id)
           INNER JOIN  aliases         AS a  ON (u.user_id=a.id AND a.type='a_vie')
           INNER JOIN  aliases         AS a2 ON (u.user_id=a2.id AND FIND_IN_SET('bestalias',a2.flags))
            LEFT JOIN  contacts        AS c  ON (c.uid = {?} and c.contact = u.user_id)
            LEFT JOIN  geoloc_pays     AS gp ON (gp.a2 = u.nationalite)
           INNER JOIN  sections        AS s  ON (s.id  = u.section)
            LEFT JOIN  photo           AS p  ON (p.uid = u.user_id) 
            LEFT JOIN  mentor          AS m  ON (m.uid = u.user_id)
                WHERE  a.alias = {?}";
    $res  = $globals->xdb->query($reqsql, $from_uid, $login);
    $user = $res->fetchOneAssoc();
    $uid  = $user['user_id'];

    $sql  = "SELECT  e.entreprise, s.label as secteur , ss.label as sous_secteur , f.fonction_fr as fonction,
                     e.poste, e.adr1, e.adr2, e.adr3, e.cp, e.ville,
                     gp.pays, gr.name AS region, e.tel, e.fax, e.mobile, e.entrid,
                     e.pub, e.tel_pub, e.email, e.email_pub, e.web
               FROM  entreprises AS e
          LEFT JOIN  emploi_secteur AS s ON(e.secteur = s.id)
          LEFT JOIN  emploi_ss_secteur AS ss ON(e.ss_secteur = ss.id AND e.secteur = ss.secteur)
          LEFT JOIN  fonctions_def AS f ON(e.fonction = f.id)
          LEFT JOIN  geoloc_pays AS gp ON (gp.a2 = e.pays)
          LEFT JOIN  geoloc_region AS gr ON (gr.a2 = e.pays and gr.region = e.region)
              WHERE  e.uid = {?}
           ORDER BY  e.entrid";
    $res  = $globals->xdb->query($sql, $uid);
    $user['adr_pro'] = $res->fetchAllAssoc();

    $sql  = "SELECT  a.adr1,a.adr2,a.adr3,a.cp,a.ville,
                     gp.pays,gr.name AS region,a.tel,a.fax,
                     FIND_IN_SET('active', a.statut) AS active, a.adrid,
                     FIND_IN_SET('res-secondaire', a.statut) AS secondaire,
                     a.pub, a.tel_pub
               FROM  adresses AS a
          LEFT JOIN  geoloc_pays AS gp ON (gp.a2=a.pays)
          LEFT JOIN  geoloc_region AS gr ON (gr.a2=a.pays and gr.region=a.region)
              WHERE  uid= {?} AND NOT FIND_IN_SET('pro',a.statut)
           ORDER BY  NOT FIND_IN_SET('active',a.statut), FIND_IN_SET('temporaire',a.statut), FIND_IN_SET('res-secondaire',a.statut)";
    $res  = $globals->xdb->query($sql, $uid);
    $user['adr'] = $res->fetchAllAssoc();

    $sql  = "SELECT  text
               FROM  binets_ins
          LEFT JOIN  binets_def ON binets_ins.binet_id = binets_def.id
              WHERE  user_id = {?}";
    $res  = $globals->xdb->query($sql, $uid);
    $user['binets']      = $res->fetchColumn();
    $user['binets_join'] = join(', ', $user['binets']);

    $res  = $globals->xdb->iterRow("SELECT  text, url
                                      FROM  groupesx_ins
                                 LEFT JOIN  groupesx_def ON groupesx_ins.gid = groupesx_def.id
                                     WHERE  guid = {?}", $uid);
    $user['gpxs'] = Array();
    while (list($gxt, $gxu) = $res->next()) {
        $user['gpxs'][] = $gxu ? "<a href=\"$gxu\">$gxt</a>" : $gxt;
    } 
    $user['gpxs_join'] = join(', ', $user['gpxs']);

    $res = $globals->xdb->iterRow("SELECT  applis_def.text, applis_def.url, applis_ins.type
                                     FROM  applis_ins
                               INNER JOIN  applis_def ON applis_def.id = applis_ins.aid
                                    WHERE  uid={?}
                                 ORDER BY  ordre", $uid);
    
    $user['applis_fmt'] = Array();
    while (list($txt, $url, $type) = $res->next()) {
        require_once('applis.func.inc.php');
        $user['applis_fmt'][] = applis_fmt($type, $txt, $url);
    }
    $user['applis_join'] = join(', ', $user['applis_fmt']);

    $res = $globals->xdb->iterator("SELECT  m.id, m.text AS medal, m.type, m.img, s.gid, g.text AS grade
                                      FROM  profile_medals_sub    AS s
                                INNER JOIN  profile_medals        AS m ON ( s.mid = m.id )
                                LEFT  JOIN  profile_medals_grades AS g ON ( s.mid = g.mid AND s.gid = g.gid )
                                     WHERE  s.uid = {?}", $uid);
    $user['medals'] = Array();
    while ($tmp = $res->next()) {
        $user['medals'][] = $tmp;
    }

    return $user;
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
