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

// {{{ function user_clear_all_subs()
/** kills the inscription of a user.
 * we still keep his birthdate, adresses, and personnal stuff
 * kills the entreprises, mentor, emails and lists subscription stuff
 */
function user_clear_all_subs($user_id, $really_del=true)
{
    // keep datas in : aliases, adresses, tels, applis_ins, binets_ins, contacts, groupesx_ins, homonymes, identification_ax, photo
    // delete in     : auth_user_md5, auth_user_quick, competences_ins, emails, entreprises, langues_ins, mentor,
    //                 mentor_pays, mentor_secteurs, newsletter_ins, perte_pass, requests, user_changes, virtual_redirect, watch_sub
    // + delete maillists

    global $globals;
    $uid   = intval($user_id);
    $res   = XDB::query("SELECT alias FROM aliases WHERE type='a_vie' AND id={?}", $uid);
    $alias = $res->fetchOneCell();

    if ($really_del) {
	XDB::execute("DELETE FROM emails WHERE uid={?}", $uid);
	XDB::execute("DELETE FROM newsletter_ins WHERE user_id={?}", $uid);
    }

    XDB::execute("DELETE FROM virtual_redirect WHERE redirect = {?}", $alias.'@'.$globals->mail->domain);
    XDB::execute("DELETE FROM virtual_redirect WHERE redirect = {?}", $alias.'@'.$globals->mail->domain2);

    XDB::execute("UPDATE auth_user_md5   SET password='',smtppass='' WHERE user_id={?}", $uid);
    XDB::execute("UPDATE auth_user_quick SET watch_flags='' WHERE user_id={?}", $uid);

    XDB::execute("DELETE FROM competences_ins WHERE uid={?}", $uid);
    XDB::execute("DELETE FROM entreprises     WHERE uid={?}", $uid);
    XDB::execute("DELETE FROM langues_ins     WHERE uid={?}", $uid);
    XDB::execute("DELETE FROM mentor_pays     WHERE uid={?}", $uid);
    XDB::execute("DELETE FROM mentor_secteur  WHERE uid={?}", $uid);
    XDB::execute("DELETE FROM mentor          WHERE uid={?}", $uid);
    XDB::execute("DELETE FROM perte_pass      WHERE uid={?}", $uid);
    XDB::execute("DELETE FROM requests        WHERE user_id={?}", $uid);
    XDB::execute("DELETE FROM user_changes    WHERE user_id={?}", $uid);
    XDB::execute("DELETE FROM watch_sub       WHERE uid={?}", $uid);

    $mmlist = new MMList(S::v('id'), S::v('password'));
    $mmlist->kill($alias, $really_del);
}

// }}}
// {{{ function get_user_login()

function get_user_login($data, $get_forlife = false) {
    global $globals, $page;

    if (is_numeric($data)) {
        $res = XDB::query("SELECT alias FROM aliases WHERE type='a_vie' AND id={?}", $data);
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
    
    list($mbox, $fqdn) = explode('@', $data);
    if ($fqdn == $globals->mail->domain || $fqdn == $globals->mail->domain2) {

        $res = XDB::query("SELECT  a.alias
                             FROM  aliases AS a
                       INNER JOIN  aliases AS b ON (a.id = b.id AND b.type IN ('alias', 'a_vie') AND b.alias={?})
                            WHERE  a.type = 'a_vie'", $mbox);
        if ($res->numRows()) {
            return $get_forlife ? $res->fetchOneCell() : $mbox;
        }

        if (preg_match('/^(.*)\.([0-9]{4})$/', $mbox, $matches)) {
            $res = XDB::query("SELECT  a.alias
                                 FROM  aliases AS a
                           INNER JOIN  aliases AS b ON (a.id = b.id AND b.type IN ('alias', 'a_vie') AND b.alias={?})
                           INNER JOIN  auth_user_md5 AS u ON (a.id = u.user_id AND promo = {?})
                                WHERE  a.type = 'a_vie'", $matches[1], $matches[2]);
            if ($res->numRows() == 1) {
                return $res->fetchOneCell();
            }
        }
        $page->trig("il n'y a pas d'utilisateur avec ce login");
        return false;

    } elseif ($fqdn == $globals->mail->alias_dom || $fqdn == $globals->mail->alias_dom2) {
    
        $res = XDB::query("SELECT  redirect
                             FROM  virtual_redirect
                       INNER JOIN  virtual USING(vid)
                            WHERE  alias={?}", $mbox.'@'.$globals->mail->alias_dom);
        if ($redir = $res->fetchOneCell()) {
            list($alias) = explode('@', $redir);
        } else {
            $page->trig("il n'y a pas d'utilisateur avec cet alias");
            $alias = false;
        }
        return $alias;

    } else {

        $res = XDB::query("SELECT  alias
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
                if (S::has_perms()) {
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
// {{{ function get_users_forlife_list()

function get_users_forlife_list($members, $strict = false)
{
    if (strlen(trim($members)) == 0) {
        return null;
    }
    $members = explode(' ', $members);
    if ($members) {
        $list = array();
        foreach ($members as $i => $alias) {
            if (($login = get_user_forlife($alias)) !== false) {
                $list[$i] = $login;
            } else if(!$strict) {
                $list[$i] = $alias;
            }
        }
        return $list;
    }
    return null;
}

// }}}
// {{{ function has_user_right()
function has_user_right($pub, $view = 'private') {
    if ($pub == $view) return true;
    // all infos available for private 
    if ($view == 'private') return true;
    // public infos available for all 
    if ($pub == 'public') return true;
    // here we have view = ax or public, and pub = ax or private, and pub != view
    return false;    
}
// }}}
// {{{ function get_user_details_pro()

function get_user_details_pro($uid, $view = 'private')
{
    $sql  = "SELECT  e.entreprise, s.label as secteur , ss.label as sous_secteur , f.fonction_fr as fonction,
                     e.poste, e.adr1, e.adr2, e.adr3, e.postcode, e.city, e.entrid,
                     gp.pays AS countrytxt, gr.name AS region, e.tel, e.fax, e.mobile, e.entrid,
                     e.pub, e.adr_pub, e.tel_pub, e.email, e.email_pub, e.web
               FROM  entreprises AS e
          LEFT JOIN  emploi_secteur AS s ON(e.secteur = s.id)
          LEFT JOIN  emploi_ss_secteur AS ss ON(e.ss_secteur = ss.id AND e.secteur = ss.secteur)
          LEFT JOIN  fonctions_def AS f ON(e.fonction = f.id)
          LEFT JOIN  geoloc_pays AS gp ON (gp.a2 = e.country)
          LEFT JOIN  geoloc_region AS gr ON (gr.a2 = e.country and gr.region = e.region)
              WHERE  e.uid = {?}
           ORDER BY  e.entrid";
    $res  = XDB::query($sql, $uid);
    $all_pro = $res->fetchAllAssoc();
    foreach ($all_pro as $i => $pro) {
        if (!has_user_right($pro['pub'], $view))
            unset($all_pro[$i]);
        else {
            if (!has_user_right($pro['adr_pub'], $view)) {
                if ($pro['adr1'] == '' &&
                    $pro['adr2'] == '' &&
                    $pro['adr3'] == '' &&
                    $pro['postcode'] == '' &&
                    $pro['city'] == '' &&
                    $pro['countrytxt'] == '' &&
                    $pro['region'] == '') {
                    $all_pro[$i]['adr_pub'] = $view;
                } else {
                    $all_pro[$i]['adr1'] = '';
                    $all_pro[$i]['adr2'] = '';
                    $all_pro[$i]['adr3'] = '';
                    $all_pro[$i]['postcode'] = '';
                    $all_pro[$i]['city'] = '';
                    $all_pro[$i]['countrytxt'] = '';
                    $all_pro[$i]['region'] = '';
                }
            }
            if (!has_user_right($pro['tel_pub'], $view)) {
                // if no tel was defined, then the viewer will be able to write it
                if ($pro['tel'] == '' && 
                    $pro['fax'] == '' &&
                    $pro['mobile'] == '') {
                    $all_pro[$i]['tel_pub'] = $view;
                } else {
                    $all_pro[$i]['tel'] = '';
                    $all_pro[$i]['fax'] = '';
                    $all_pro[$i]['mobile'] = '';
                }
            }
            if (!has_user_right($pro['email_pub'], $view)) {
                if ($pro['email'] == '')
                    $all_pro[$i]['email_pub'] = $view;
                else
                    $all_pro[$i]['email'] = '';
            }
            if ($all_pro[$i]['adr1'] == '' &&
                $all_pro[$i]['adr2'] == '' &&
                $all_pro[$i]['adr3'] == '' &&
                $all_pro[$i]['postcode'] == '' &&
                $all_pro[$i]['city'] == '' &&
                $all_pro[$i]['countrytxt'] == '' &&
                $all_pro[$i]['region'] == '' &&
                $all_pro[$i]['entreprise'] == '' &&
                $all_pro[$i]['fonction'] == '' &&
                $all_pro[$i]['secteur'] == '' &&
                $all_pro[$i]['poste'] == '' &&
                $all_pro[$i]['tel'] == '' &&
                $all_pro[$i]['fax'] == '' &&
                $all_pro[$i]['mobile'] == '' &&
                $all_pro[$i]['email'] == '')
                unset($all_pro[$i]);
        }
    }
    if (!count($all_pro)) return false;
    return $all_pro;
}

// }}}
function get_user_details_adr($uid, $view = 'private') {
    $sql  = "SELECT  a.adrid, a.adr1,a.adr2,a.adr3,a.postcode,a.city,
                     gp.pays AS countrytxt,a.region, a.regiontxt,
                     FIND_IN_SET('active', a.statut) AS active, a.adrid,
                     FIND_IN_SET('res-secondaire', a.statut) AS secondaire,
                     a.pub, gp.display
               FROM  adresses AS a
          LEFT JOIN  geoloc_pays AS gp ON (gp.a2=a.country)
              WHERE  uid= {?} AND NOT FIND_IN_SET('pro',a.statut)
           ORDER BY  NOT FIND_IN_SET('active',a.statut), FIND_IN_SET('temporaire',a.statut), FIND_IN_SET('res-secondaire',a.statut)";
    $res  = XDB::query($sql, $uid);
    $all_adr = $res->fetchAllAssoc();
    $adrid_index = array();
    foreach ($all_adr as $i => $adr) {
        if (!has_user_right($adr['pub'], $view))
            unset($all_adr[$i]);
        else
            $adrid_index[$adr['adrid']] = $i;
    }
    
    $sql = "SELECT  t.adrid, t.tel_pub, t.tel_type, t.tel, t.telid
              FROM  tels AS t
        INNER JOIN  adresses AS a ON (a.uid = t.uid) AND (a.adrid = t.adrid)
             WHERE  t.uid = {?} AND NOT FIND_IN_SET('pro',a.statut)
          ORDER BY  t.adrid, t.tel_type DESC, t.telid";
    $restel = XDB::iterator($sql, $uid);
    while ($nexttel = $restel->next()) {
        if (has_user_right($nexttel['tel_pub'], $view)) {
            $adrid = $nexttel['adrid'];
            unset($nexttel['adrid']);
            if (isset($adrid_index[$adrid])) {
                if (!isset($all_adr[$adrid_index[$adrid]]['tels'])) 
                    $all_adr[$adrid_index[$adrid]]['tels'] = array($nexttel);
                else
                    $all_adr[$adrid_index[$adrid]]['tels'][] = $nexttel;
            }
        }
    }
    return $all_adr;
}
// {{{ function get_user_details()

function &get_user_details($login, $from_uid = '', $view = 'private')
{
    $reqsql = "SELECT  u.user_id, u.promo, u.promo_sortie, u.prenom, u.nom, u.nom_usage, u.date, u.cv,
                       u.perms IN ('admin','user','disabled') AS inscrit,  FIND_IN_SET('femme', u.flags) AS sexe, u.deces != 0 AS dcd, u.deces,
                       q.profile_nick AS nickname, q.profile_from_ax, q.profile_mobile AS mobile, q.profile_web AS web, q.profile_freetext AS freetext,
                       q.profile_mobile_pub AS mobile_pub, q.profile_web_pub AS web_pub, q.profile_freetext_pub AS freetext_pub,
                       q.profile_medals_pub AS medals_pub,
                       IF(gp.nat='',gp.pays,gp.nat) AS nationalite, gp.a2 AS iso3166,
                       a.alias AS forlife, a2.alias AS bestalias,
                       c.uid IS NOT NULL AS is_contact,
                       s.text AS section, p.x, p.y, p.pub AS photo_pub,
                       u.matricule_ax,
                       m.expertise != '' AS is_referent,
                       COUNT(e.email) > 0 AS actif
                 FROM  auth_user_md5   AS u
           INNER JOIN  auth_user_quick AS q  USING(user_id)
           INNER JOIN  aliases         AS a  ON (u.user_id=a.id AND a.type='a_vie')
           INNER JOIN  aliases         AS a2 ON (u.user_id=a2.id AND FIND_IN_SET('bestalias',a2.flags))
            LEFT JOIN  contacts        AS c  ON (c.uid = {?} and c.contact = u.user_id)
            LEFT JOIN  geoloc_pays     AS gp ON (gp.a2 = u.nationalite)
           INNER JOIN  sections        AS s  ON (s.id  = u.section)
            LEFT JOIN  photo           AS p  ON (p.uid = u.user_id) 
            LEFT JOIN  mentor          AS m  ON (m.uid = u.user_id)
            LEFT JOIN  emails          AS e  ON (e.uid = u.user_id AND e.flags='active')
                WHERE  a.alias = {?}
             GROUP BY  u.user_id";
    $res  = XDB::query($reqsql, $from_uid, $login);
    $user = $res->fetchOneAssoc();
    $uid  = $user['user_id'];
    // hide orange status, cv, nickname, section
    if (!has_user_right('private', $view)) {
        $user['promo_sortie'] = $user['promo'] + 3;
        $user['cv'] = '';
        $user['nickname'] = '';
        $user['section'] = '';
    }
    // hide mobile
    if (!has_user_right($user['mobile_pub'], $view)) {
        if ($user['mobile'] == '')
            $user['mobile_pub'] = $view;
        else
            $user['mobile'] = '';
    }
    // hide web
    if (!has_user_right($user['web_pub'], $view)) {
        if ($user['web'] == '')
            $user['web_pub'] = $view;
        else
            $user['web'] = '';
    }
    // hide freetext
    if (!has_user_right($user['freetext_pub'], $view)) {
        if ($user['freetext'] == '')
            $user['freetext_pub'] = $view;
        else
            $user['freetext'] = '';
    }

    $user['adr_pro'] = get_user_details_pro($uid, $view);
    $user['adr']     = get_user_details_adr($uid, $view);

    if (has_user_right('private', $view)) {
        $sql  = "SELECT  text
                   FROM  binets_ins
              LEFT JOIN  binets_def ON binets_ins.binet_id = binets_def.id
                  WHERE  user_id = {?}";
        $res  = XDB::query($sql, $uid);
        $user['binets']      = $res->fetchColumn();
        $user['binets_join'] = join(', ', $user['binets']);
    
        $res  = XDB::iterRow("SELECT  text, url
                                FROM  groupesx_ins
                           LEFT JOIN  groupesx_def ON groupesx_ins.gid = groupesx_def.id
                               WHERE  guid = {?}", $uid);
        $user['gpxs'] = Array();
        $user['gpxs_name'] = Array();
        while (list($gxt, $gxu) = $res->next()) {
            $user['gpxs'][] = $gxu ? "<a href=\"$gxu\">$gxt</a>" : $gxt;
            $user['gpxs_name'][] = $gxt;
        } 
        $user['gpxs_join'] = join(', ', $user['gpxs']);
    }

    $res = XDB::iterRow("SELECT  applis_def.text, applis_def.url, applis_ins.type
                           FROM  applis_ins
                     INNER JOIN  applis_def ON applis_def.id = applis_ins.aid
                          WHERE  uid={?}
                       ORDER BY  ordre", $uid);
    
    $user['applis_fmt'] = Array();
    $user['formation'] = Array();
    while (list($txt, $url, $type) = $res->next()) {
        $user['formation'][] = $txt." ".$type;
        require_once('applis.func.inc.php');
        $user['applis_fmt'][] = applis_fmt($type, $txt, $url);
    }
    $user['applis_join'] = join(', ', $user['applis_fmt']);

    if (has_user_right($user['medals_pub'], $view)) {
        $res = XDB::iterator("SELECT  m.id, m.text AS medal, m.type, m.img, s.gid, g.text AS grade
                                FROM  profile_medals_sub    AS s
                          INNER JOIN  profile_medals        AS m ON ( s.mid = m.id )
                           LEFT JOIN  profile_medals_grades AS g ON ( s.mid = g.mid AND s.gid = g.gid )
                               WHERE  s.uid = {?}", $uid);
        $user['medals'] = Array();
        while ($tmp = $res->next()) {
            $user['medals'][] = $tmp;
        }
    }

    return $user;
}
// }}}
// {{{ function add_user_address()
function add_user_address($uid, $adrid, $adr) {
    XDB::execute(
        "INSERT INTO adresses (`uid`, `adrid`, `adr1`, `adr2`, `adr3`, `postcode`, `city`, `country`, `datemaj`, `pub`) (
        SELECT u.user_id, {?}, {?}, {?}, {?}, {?}, {?}, gp.a2, NOW(), {?}
            FROM auth_user_md5 AS u
            LEFT JOIN geoloc_pays AS gp ON (gp.pays LIKE {?} OR gp.country LIKE {?} OR gp.a2 LIKE {?})
            WHERE u.user_id = {?}
            LIMIT 1)",
        $adrid, $adr['adr1'], $adr['adr2'], $adr['adr3'], $adr['postcode'], $adr['city'], $adr['pub'], $adr['countrytxt'], $adr['countrytxt'], $adr['countrytxt'], $uid);
    if (isset($adr['tels']) && is_array($adr['tels'])) {
        $telid = 0;
        foreach ($adr['tels'] as $tel) if ($tel['tel']) {
            add_user_tel($uid, $adrid, $telid, $tel);
            $telid ++;
        }
    }
}
// }}}
// {{{ function update_user_address()
function update_user_address($uid, $adrid, $adr) {
    // update address
    XDB::execute(
        "UPDATE adresses AS a LEFT JOIN geoloc_pays AS gp ON (gp.pays = {?}) 
        SET `adr1` = {?}, `adr2` = {?}, `adr3` = {?},
        `postcode` = {?}, `city` = {?}, a.`country` = gp.a2, `datemaj` = NOW(), `pub` = {?}
        WHERE adrid = {?} AND uid = {?}",
        $adr['country_txt'],
        $adr['adr1'], $adr['adr2'], $adr['adr3'],
        $adr['postcode'], $adr['city'], $adr['pub'], $adrid, $uid);
    if (isset($adr['tels']) && is_array($adr['tels'])) {
        $res = XDB::query("SELECT telid FROM tels WHERE uid = {?} AND adrid = {?} ORDER BY telid", $uid, $adrid);
        $telids = $res->fetchColumn();
        foreach ($adr['tels'] as $tel) {
            if (isset($tel['telid']) && isset($tel['remove']) && $tel['remove']) {
                remove_user_tel($uid, $adrid, $tel['telid']);
                if (isset($telids[$tel['telid']])) unset($telids[$tel['telid']]);
            } else if (isset($tel['telid'])) {
                update_user_tel($uid, $adrid, $tel['telid'], $tel);
            } else {
                for ($telid = 0; isset($telids[$telid]) && ($telids[$telid] == $telid); $telid++);
                add_user_tel($uid, $adrid, $telid, $tel);
            }
        }
    }
}
// }}}
// {{{ function remove_user_address()
function remove_user_address($uid, $adrid) {
    XDB::execute("DELETE FROM adresses WHERE adrid = {?} AND uid = {?}", $adrid, $uid);
    XDB::execute("DELETE FROM tels WHERE adrid = {?} AND uid = {?}", $adrid, $uid);
}
// }}}
// {{{ function add_user_tel()
function add_user_tel($uid, $adrid, $telid, $tel) {
    XDB::execute(
        "INSERT INTO tels SET uid = {?}, adrid = {?}, telid = {?}, tel = {?}, tel_type = {?}, tel_pub = {?}",
        $uid, $adrid, $telid, $tel['tel'], $tel['tel_type'], $tel['tel_pub']);
}
// }}}
// {{{ function update_user_tel()
function update_user_tel($uid, $adrid, $telid, $tel) {
    XDB::execute(
        "UPDATE tels SET tel = {?}, tel_type = {?}, tel_pub = {?}
        WHERE telid = {?} AND adrid = {?} AND uid = {?}",
        $tel['tel'], $tel['tel_type'], $tel['tel_pub'],
        $telid, $adrid, $uid);
}
// }}}
// {{{ function remove_user_tel()
function remove_user_tel($uid, $adrid, $telid) {
    XDB::execute("DELETE FROM tels WHERE telid = {?} AND adrid = {?} AND uid = {?}",
                 $telid, $adrid, $uid);
}
// }}}
// {{{ function add_user_pro()
function add_user_pro($uid, $entrid, $pro) {
    XDB::execute(
        "INSERT INTO entreprises (`uid`, `entrid`, `entreprise`, `poste`, `secteur`, `ss_secteur`, `fonction`,
            `adr1`, `adr2`, `adr3`, `postcode`, `city`, `country`, `region`, `tel`, `fax`, `mobile`, `email`, `web`, `pub`, `adr_pub`, `tel_pub`, `email_pub`)
        SELECT u.user_id, {?}, {?}, {?}, s.id, ss.id, f.id,
        {?}, {?}, {?}, {?}, {?}, gp.a2, gr.region, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}
        FROM auth_user_md5 AS u
            LEFT JOIN  emploi_secteur AS s ON(s.label LIKE {?})
            LEFT JOIN  emploi_ss_secteur AS ss ON(s.id = ss.secteur AND ss.label LIKE {?})
            LEFT JOIN  fonctions_def AS f ON(f.fonction_fr LIKE {?} OR f.fonction_en LIKE {?})
            LEFT JOIN  geoloc_pays AS gp ON (gp.country LIKE {?} OR gp.pays LIKE {?})
            LEFT JOIN  geoloc_region AS gr ON (gr.a2 = gp.a2 AND gr.name LIKE {?})
        WHERE u.user_id = {?}
        LIMIT 1",
        $entrid, $pro['entreprise'], $pro['poste'],
        $pro['adr1'], $pro['adr2'], $pro['adr3'], $pro['postcode'], $pro['city'], $pro['tel'], $pro['fax'], $pro['mobile'], $pro['email'], $pro['web'], $pro['pub'], $pro['adr_pub'], $pro['tel_pub'], $pro['email_pub'],
        $pro['secteur'], $pro['sous_secteur'], $pro['fonction'], $pro['fonction'],
        $pro['countrytxt'], $pro['countrytxt'], $pro['region'],
        $uid);
}
// }}}
// {{{ function update_user_pro()
function update_user_pro($uid, $entrid, $pro) {
    $join = "";
    $set = "";
    $args_join = array();
    $args_set = array();
    
    $join .= "LEFT JOIN  emploi_secteur AS s ON(s.label LIKE {?})
            LEFT JOIN  emploi_ss_secteur AS ss ON(s.id = ss.secteur AND ss.label LIKE {?})
            LEFT JOIN  fonctions_def AS f ON(f.fonction_fr LIKE {?} OR f.fonction_en LIKE {?})";
    $args_join[] = $pro['secteur'];
    $args_join[] = $pro['sous_secteur'];
    $args_join[] = $pro['fonction'];
    $args_join[] = $pro['fonction'];
    $set .= ", e.`entreprise` = {?}, e.`secteur` = s.id, e.`ss_secteur` = ss.id, e.`fonction` = f.id, e.`poste`= {?}, e.`web` = {?}, e.`pub` = {?}";
    $args_set[] = $pro['entreprise'];
    $args_set[] = $pro['poste'];
    $args_set[] = $pro['web'];
    $args_set[] = $pro['pub'];
    
    if (isset($pro['adr1'])) {
        $join .= "LEFT JOIN  geoloc_pays AS gp ON (gp.country LIKE {?} OR gp.pays LIKE {?})
                LEFT JOIN  geoloc_region AS gr ON (gr.a2 = gp.a2 AND gr.name LIKE {?})";
        $args_join[] = $pro['countrytxt'];
        $args_join[] = $pro['countrytxt'];
        $args_join[] = $pro['region'];
        $set .= ", e.`adr1` = {?}, e.`adr2` = {?}, e.`adr3` = {?}, e.`postcode` = {?}, e.`city` = {?}, e.`country` = gp.a2, e.`region` = gr.region, e.`adr_pub` = {?}";
        $args_set[] = $pro['adr1'];
        $args_set[] = $pro['adr2'];
        $args_set[] = $pro['adr3'];
        $args_set[] = $pro['postcode'];
        $args_set[] = $pro['city'];
        $args_set[] = $pro['adr_pub'];
    }
    
    if (isset($pro['tel'])) {
        $set .= ", e.`tel` = {?}, e.`fax` = {?}, e.`mobile` = {?}, e.tel_pub = {?}";
        $args_set[] = $pro['tel'];
        $args_set[] = $pro['fax'];
        $args_set[] = $pro['mobile'];
        $args_set[] = $pro['tel_pub'];
    }
    if (isset($pro['email'])) {
        $set .= ", e.`email` = {?}, e.`email_pub` = {?}";
        $args_set[] = $pro['email'];
        $args_set[] = $pro['email_pub'];
    }    
    $query = "UPDATE entreprises AS e ".$join." SET ".substr($set,1)." WHERE e.uid = {?} AND e.entrid = {?}";
    $args_where = array($uid, $entrid);
    $args = array_merge(array($query), $args_join, $args_set, $args_where);
    call_user_func_array(array('XDB', 'execute'), $args);
}
// }}}
// {{{ function remove_user_pro()
function remove_user_pro($uid, $entrid) {
    XDB::execute("DELETE FROM entreprises WHERE entrid = {?} AND uid = {?}", $entrid, $uid);
}
// }}}
// {{{ function set_user_details()
function set_user_details_addresses($uid, $adrs) {
    $res = XDB::query("SELECT adrid FROM adresses WHERE uid = {?} AND adrid >= 1 ORDER BY adrid", $uid);
    $adrids = $res->fetchColumn();
    foreach ($adrs as $adr) {
        if (isset($adr['adrid']) && isset($adr['remove']) && $adr['remove']) {
            remove_user_address($uid, $adr['adrid']);
            if (isset($adrids[$adr['adrid']])) unset($adrids[$adr['adrid']]);
        } else if (isset($adr['adrid'])) {
            update_user_address($uid, $adr['adrid'], $adr);
        } else {
            for ($adrid = 1; isset($adrids[$adrid-1]) && ($adrids[$adrid-1] == $adrid); $adrid++);
            add_user_address($uid, $adrid, $adr);
            $adrids[$adrid-1] = $adrid;
        }
    }
    require_once 'geoloc.inc.php';
    localize_addresses($uid);
}
// }}}
// {{{ function set_user_details_pro()

function set_user_details_pro($uid, $pros)
{
    $res = XDB::query("SELECT entrid FROM entreprises WHERE uid = {?} ORDER BY entrid", $uid);
    $entrids = $res->fetchColumn();
    foreach ($pros as $pro) {
        if (isset($pro['entrid']) && isset($pro['remove']) && $pro['remove']) {
            remove_user_pro($uid, $pro['entrid']);
            if (isset($entrids[$pro['entrid']])) unset($entrids[$pro['entrid']]);
        } else if (isset($pro['entrid'])) {
            update_user_pro($uid, $pro['entrid'], $pro);
        } else {
            for ($entrid = 0; isset($entrids[$entrid]) && ($entrids[$entrid] == $entrid); $entrid++);
            add_user_pro($uid, $entrid, $pro);
        }
    }
}

// }}}
// {{{ function set_user_details()
function set_user_details($uid, $details) {
    if (isset($details['nom_usage'])) {
        XDB::execute("UPDATE auth_user_md5 SET nom_usage = {?} WHERE user_id = {?}", strtoupper($details['nom_usage']), $uid);
    }
    if (isset($details['mobile'])) {
        XDB::execute("UPDATE auth_user_quick SET profile_mobile = {?} WHERE user_id = {?}", $details['mobile'], $uid);
    }
    if (isset($details['nationalite'])) {
        XDB::execute(
            "UPDATE auth_user_md5 AS u
                INNER JOIN geoloc_pays     AS gp
            SET u.nationalite = gp.a2
            WHERE (gp.a2 = {?} OR gp.nat = {?})
                AND u.user_id = {?}",  $details['nationalite'], $details['nationalite'], $uid);
    }
    if (isset($details['adr']) && is_array($details['adr']))
        set_user_details_addresses($uid, $details['adr']);
    if (isset($details['adr_pro']) && is_array($details['adr_pro']))
        set_user_details_pro($uid, $details['adr_pro']);
    if (isset($details['binets']) && is_array($details['binets'])) {
        XDB::execute("DELETE FROM binets_ins WHERE user_id = {?}", $uid);
        foreach ($details['binets'] as $binet)
            XDB::execute(
            "INSERT INTO binets_ins (`user_id`, `binet_id`)
                SELECT {?}, id FROM binets_def WHERE text = {?} LIMIT 1",
                $uid, $binet);                
    }
    if (isset($details['gpxs']) && is_array($details['gpxs'])) {
        XDB::execute("DELETE FROM groupesx_ins WHERE user_id = {?}", $uid);
        foreach ($details['gpxs'] as $groupex) {
            if (preg_match('/<a href="[^"]*">([^<]+)</a>/', $groupex, $a)) $groupex = $a[1];
            XDB::execute(
            "INSERT INTO groupesx_ins (`user_id`, `binet_id`)
                SELECT {?}, id FROM groupesx_def WHERE text = {?} LIMIT 1",
                $uid, $groupex);
        }                
    }
    // applis
    // medals
}
// }}}
// {{{ function _user_reindex

function _user_reindex($uid, $keys, $muls) {
    foreach ($keys as $i => $key) {
        if ($key == '') {
            continue;
        }
        $toks  = preg_split('/[ \'\-]+/', $key);
        $token = "";
        $first = 5;
        while ($toks) {
            $token = strtolower(replace_accent(array_pop($toks) . $token));
            $score = ($toks ? 0 : 10 + $first) * $muls[$i];
            mysql_query("REPLACE INTO search_name (token, uid, score) VALUES('$token',$uid,$score)");
            $first = 0;
        }
    }
}

// }}}
// {{{ function user_reindex

function user_reindex($uid) {
    XDB::execute("DELETE FROM search_name WHERE uid={?}", $uid);
    $res = XDB::query("SELECT prenom, nom, nom_usage, profile_nick FROM auth_user_md5 INNER JOIN auth_user_quick USING(user_id) WHERE auth_user_md5.user_id = {?}", $uid);
    _user_reindex($uid, $res->fetchOneRow(), array(1,1,1,0.2));
}

// }}}

function set_new_usage($uid, $usage, $alias=false) { 
    XDB::execute("UPDATE auth_user_md5 set nom_usage={?} WHERE user_id={?}",$usage ,$uid);
    XDB::execute("DELETE FROM aliases WHERE FIND_IN_SET('usage',flags) AND id={?}", $uid);
    if ($alias && $usage) {
        XDB::execute("UPDATE aliases SET flags=flags & 255-1 WHERE id={?}", $uid);
        XDB::execute("INSERT INTO aliases VALUES({?}, 'alias', 'usage,bestalias', {?}, null)",
            $alias, $uid);
    }
    $r = XDB::query("SELECT alias FROM aliases WHERE FIND_IN_SET('bestalias', flags) AND id = {?}", $uid);
    if ($r->fetchOneCell() == "") {
        XDB::execute("UPDATE aliases SET flags = 1 | flags WHERE id = {?} LIMIT 1", $uid);
    }
    require_once 'user.func.inc.php';
    user_reindex($uid);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
