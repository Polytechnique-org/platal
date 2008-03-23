<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

define('WATCH_FICHE', 1);
define('WATCH_INSCR', 2);
define('WATCH_DEATH', 3);
define('WATCH_BIRTH', 4);

// {{{ function inscription_notifs_base

function inscription_notifs_base($uid)
{
    XDB::execute('REPLACE INTO  watch_sub (uid,cid) SELECT {?},id FROM watch_cat', $uid);
}

// }}}
// {{{ function register_watch_op

function register_watch_op($uid, $cid, $date='', $info='')
{
    if (empty($date)) {
        $date = date('Y-m-d');
    };
    XDB::execute('REPLACE INTO  watch_ops (uid,cid,known,date,info)
                        VALUES  ({?}, {?}, NOW(), {?}, {?})',
                 $uid, $cid, $date, $info);
    if($cid == WATCH_FICHE) {
        if ($info) {
            register_profile_update($uid, $info);
        }
        XDB::execute('UPDATE auth_user_md5 SET DATE=NOW() WHERE user_id={?}', $uid);
    } elseif($cid == WATCH_INSCR) {
        XDB::execute('REPLACE INTO  contacts (uid,contact)
                            SELECT  uid,ni_id
                              FROM  watch_nonins
                             WHERE  ni_id={?}', $uid);
        XDB::execute('DELETE FROM watch_nonins WHERE ni_id={?}', $uid);
    }
    require_once 'xorg.misc.inc.php';
    update_NbNotifs();
}

// }}}
// {{{ function _select_notifs_base

function _select_notifs_base($table, $mail, $where)
{
    $cases = Array(
        'contacts'     => Array('wfield' => 'contact', 'ufield' => 'user_id', 'need_contact' => false,
            'freq_sql' => '',
            'contact_sql' => '1'
        ),
        'watch_promo'  => Array('wfield' => 'promo',   'ufield' => 'promo',   'need_contact' => true,
            'freq_sql' => ' AND ( wc.type = "basic" OR wc.type="near" AND (u.promo <= v.promo_sortie-2 AND u.promo_sortie >= v.promo+2) )',
            'contact_sql' => 'IF(c.contact IS NULL, 0, 1)'
        ),
        'watch_nonins' => Array('wfield' => 'ni_id',   'ufield' => 'user_id', 'need_contact' => true,
            'freq_sql' => '',
            'contact_sql' => 'IF(c.contact IS NULL, 0, 1)'
        )
    );

    $our   = $cases[$table];
    $sql = "
        (
            SELECT  u.promo, u.prenom, IF(u.nom_usage='',u.nom,u.nom_usage) AS nom,
                    u.deces != 0 AS dcd, (u.flags = 'femme') AS sexe,
                    a.alias AS bestalias,
                    wo.*,
                    {$our['contact_sql']} AS contact,
                    (u.perms IN('admin','user')) AS inscrit";
    if ($mail) {
        $sql.=",
            w.uid AS aid, v.prenom AS aprenom, IF(v.nom_usage='',v.nom,v.nom_usage) AS anom,
            b.alias AS abestalias, (v.flags='femme') AS asexe, q.core_mail_fmt AS mail_fmt";
    }

    $sql .= "
            FROM  $table          AS w
      INNER JOIN  auth_user_md5   AS u  ON(u.{$our['ufield']} = w.{$our['wfield']})
      INNER JOIN  auth_user_quick AS q  ON(q.user_id = w.uid)
      INNER JOIN  auth_user_md5   AS v  ON(v.user_id = q.user_id)";
    if ($mail) {
        $sql .="
      INNER JOIN  aliases         AS b  ON(b.id = q.user_id AND FIND_IN_SET('bestalias', b.flags))";
    }
    if ($our['need_contact']) {
        $sql .="
       LEFT JOIN  contacts        AS c  ON(c.uid = w.uid AND c.contact = u.user_id)";
    }

    $sql .="
      INNER JOIN  watch_ops       AS wo ON(wo.uid = u.user_id AND ".($mail ? 'wo.known > q.watch_last' : '( wo.known > {?} OR wo.date=NOW() )').")
      INNER JOIN  watch_sub       AS ws ON(ws.cid = wo.cid AND ws.uid = w.uid)
      INNER JOIN  watch_cat       AS wc ON(wc.id = wo.cid{$our['freq_sql']})
       LEFT JOIN  aliases         AS a  ON(a.id = u.user_id AND FIND_IN_SET('bestalias', a.flags))
          WHERE  $where
    )";

    return $sql;
}

// }}}
// {{{ function select_notifs

function select_notifs($mail, $uid=null, $last=null, $iterator=true)
{
    $where = $mail ? 'q.watch_flags=3' : 'w.uid = {?}';
    $sql   = _select_notifs_base('contacts',     $mail, $where.($mail?'':' AND (q.watch_flags=1 OR q.watch_flags=3)')) . " UNION DISTINCT ";
    $sql  .= _select_notifs_base('watch_promo',  $mail, $where) .  " UNION DISTINCT ";
    $sql  .= _select_notifs_base('watch_nonins', $mail, $where);

    if ($iterator) {
        return XDB::iterator($sql . ' ORDER BY cid, promo, date DESC, nom', $last, $uid, $last, $uid, $last, $uid);
    } else {
        return XDB::query($sql, $last, $uid, $last, $uid, $last, $uid);
    }
}

// }}}
// {{{

global $prf_desc;
$prf_desc = array('nom' => 'Son patronyme',
                  'freetext' => 'Le texte libre',
                  'mobile' => 'Son numéro de téléphone portable',
                  'nationalite' => 'Sa nationalité',
                  'nick' => 'Son surnom',
                  'web' => 'L\'adresse de son site web',
                  'appli1' => 'Son école d\'application',
                  'appli2' => 'Son école de post-application',
                  'addresses' => 'Ses adresses',
                  'section' => 'Sa section sportive',
                  'binets' => 'La liste de ses binets',
                  'medals' => 'Ses décorations',
                  'cv' => 'Son Curriculum Vitae',
                  'jobs' => 'Ses informations professionnelles',
                  'photo' => 'Sa photographie');

function get_profile_change_details($event, $limit) {
    global $prf_desc;
    $res = XDB::iterRow("SELECT  field
                           FROM  watch_profile
                          WHERE  uid = {?} AND ts > {?}
                       ORDER BY  ts DESC",
                         $event['uid'], $limit);
    if ($res->total() > 0) {
        $data = array();
        while (list($field) = $res->next()) {
            $data[] .= $prf_desc[$field];
        }
        return '<ul><li>' . implode('</li><li>', $data) . '</li></ul>';
    }
    return null;
}

// }}}
// {{{ function register_profile_update

function register_profile_update($uid, $field) {
    XDB::execute("REPLACE INTO  watch_profile (uid, ts, field)
                        VALUES  ({?}, NOW(), {?})",
                 $uid, $field);
}

// {{{ class AllNotifs

class AllNotifs
{
    public $_cats = Array();
    public $_data = Array();

    public function __construct()
    {
        $res = XDB::iterator("SELECT * FROM watch_cat");
        while($tmp = $res->next()) {
            $this->_cats[$tmp['id']] = $tmp;
        }

        // recupère tous les watchers, avec détails des watchers, a partir du
        // watch_last de chacun, seulement ceux qui sont surveillés, ordonnés
        $res = select_notifs(true);

        while($tmp = $res->next()) {
            $aid = $tmp['aid'];
            if (empty($this->_data[$aid])) {
                $this->_data[$aid] = Array("prenom" => $tmp['aprenom'], 'nom' => $tmp['anom'],
                    'bestalias'=>$tmp['abestalias'], 'sexe' => $tmp['asexe'], 'mail_fmt' => $tmp['mail_fmt'],
                    'dcd'=>$tmp['dcd']);
            }
            unset($tmp['aprenom'], $tmp['anom'], $tmp['abestalias'], $tmp['aid'], $tmp['asexe'], $tmp['mail_fmt'], $tmp['dcd']);
            $this->_data[$aid]['data'][$tmp['cid']][] = $tmp;
        }
    }
}

// }}}
// {{{ class Notifs

class Notifs
{
    public $_uid;
    public $_cats = Array();
    public $_data = Array();

    function __construct($uid, $up=false)
    {
        $this->_uid = $uid;

        $res = XDB::iterator("SELECT * FROM watch_cat");
        while($tmp = $res->next()) {
            $this->_cats[$tmp['id']] = $tmp;
        }

        $lastweek = date('YmdHis', time() - 7*24*60*60);

        // recupere les notifs du watcher $uid, sans detail sur le watcher,
        // depuis la semaine dernière, meme ceux sans surveillance, ordonnés
        $res = select_notifs(false, $uid, $lastweek);
        while($tmp = $res->next()) {
            if ($tmp['cid'] == WATCH_FICHE) {
                $tmp['data'] = get_profile_change_details($tmp, $lastweek);
            }
            $this->_data[$tmp['cid']][$tmp['promo']][] = $tmp;
        }

        if($up) {
            XDB::execute('UPDATE auth_user_quick SET watch_last=NOW() WHERE user_id={?}', $uid);
        }
    }
}

// }}}
// {{{ class Watch

class Watch
{
    public $_uid;
    public $_promos;
    public $_nonins;
    public $_cats = Array();
    public $_subs;
    public $watch_contacts;
    public $watch_mail;

    public function __construct($uid)
    {
        $this->_uid = $uid;
        $this->_promos = new PromoNotifs($uid);
        $this->_nonins = new NoninsNotifs($uid);
        $this->_subs = new WatchSub($uid);
        $res = XDB::query("SELECT  FIND_IN_SET('contacts',watch_flags),FIND_IN_SET('mail',watch_flags)
                             FROM  auth_user_quick
                            WHERE  user_id={?}", $uid);
        list($this->watch_contacts,$this->watch_mail) = $res->fetchOneRow();

        $res = XDB::iterator("SELECT * FROM watch_cat");
        while($tmp = $res->next()) {
            $this->_cats[$tmp['id']] = $tmp;
        }
    }

    public function saveFlags()
    {
        $flags = "";
        if ($this->watch_contacts)
            $flags = "contacts";
        if ($this->watch_mail)
            $flags .= ($flags ? ',' : '')."mail";
        XDB::execute('UPDATE auth_user_quick SET watch_flags={?} WHERE user_id={?}',
            $flags, $this->_uid);
    }

    public function cats()
    {
        return $this->_cats;
    }

    public function subs($i)
    {
        return $this->_subs->_data[$i];
    }

    public function promos()
    {
        return $this->_promos->toRanges();
    }

    public function nonins()
    {
        return $this->_nonins->_data;
    }
}

// }}}
// {{{ class WatchSub

class WatchSub
{
    public $_uid;
    public $_data = Array();

    public function __construct($uid)
    {
        $this->_uid = $uid;
        $res = XDB::iterRow('SELECT cid FROM watch_sub WHERE uid={?}', $uid);
        while(list($c) = $res->next()) {
            $this->_data[$c] = $c;
        }
    }

    public function update($ind)
    {
        $this->_data = Array();
        XDB::execute('DELETE FROM watch_sub WHERE uid={?}', $this->_uid);
        foreach (Env::v($ind) as $key=>$val) {
            XDB::query('INSERT INTO watch_sub SELECT {?},id FROM watch_cat WHERE id={?}', $this->_uid, $key);
            if(XDB::affectedRows()) {
                $this->_data[$key] = $key;
            }
        }
    }
}

// }}}
// {{{ class PromoNotifs

class PromoNotifs
{
    public $_uid;
    public $_data = Array();

    public function __construct($uid)
    {
        $this->_uid = $uid;
        $res = XDB::iterRow('SELECT promo FROM watch_promo WHERE uid={?} ORDER BY promo', $uid);
        while (list($p) = $res->next()) {
            $this->_data[intval($p)] = intval($p);
        }
    }

    public function add($p)
    {
        $promo = intval($p);
        XDB::execute('REPLACE INTO watch_promo (uid,promo) VALUES({?},{?})', $this->_uid, $promo);
        $this->_data[$promo] = $promo;
        asort($this->_data);
    }

    public function del($p)
    {
        $promo = intval($p);
        XDB::execute('DELETE FROM watch_promo WHERE uid={?} AND promo={?}', $this->_uid, $promo);
        unset($this->_data[$promo]);
    }

    public function addRange($_p1,$_p2)
    {
        $p1 = intval($_p1);
        $p2 = intval($_p2);
        $values = Array();
        for($i = min($p1,$p2); $i<=max($p1,$p2); $i++) {
            $values[] = "('{$this->_uid}',$i)";
            $this->_data[$i] = $i;
        }
        XDB::execute('REPLACE INTO watch_promo (uid,promo) VALUES '.join(',',$values));
        asort($this->_data);
    }

    public function delRange($_p1,$_p2)
    {
        $p1 = intval($_p1);
        $p2 = intval($_p2);
        $where = Array();
        for($i = min($p1,$p2); $i<=max($p1,$p2); $i++) {
            $where[] = "promo=$i";
            unset($this->_data[$i]);
        }
        XDB::execute('DELETE FROM watch_promo WHERE uid={?} AND ('.join(' OR ',$where).')', $this->_uid);
    }

    public function toRanges()
    {
        $ranges = Array();
        $I = Array();
        foreach($this->_data as $promo) {
            if(!isset($I[0])) {
                $I = Array($promo,$promo);
            }
            elseif($I[1]+1 == $promo) {
                $I[1] ++;
            }
            else {
                $ranges[] = $I;
                $I = Array($promo,$promo);
            }
        }
        if(isset($I[0])) $ranges[] = $I;
        return $ranges;
    }
}

// }}}
// {{{ class NoninsNotifs

class NoninsNotifs
{
    public $_uid;
    public $_data = Array();

    public function __construct($uid)
    {
        $this->_uid = $uid;
        $res = XDB::iterator("SELECT  u.prenom,IF(u.nom_usage='',u.nom,u.nom_usage) AS nom, u.promo, u.user_id
                                FROM  watch_nonins  AS w
                          INNER JOIN  auth_user_md5 AS u ON (u.user_id = w.ni_id)
                               WHERE  w.uid = {?}
                            ORDER BY  promo,nom", $uid);
        while($tmp = $res->next()) {
            $this->_data[$tmp['user_id']] = $tmp;
        }
    }

    public function del($p)
    {
        unset($this->_data["$p"]);
        XDB::execute('DELETE FROM  watch_nonins WHERE uid={?} AND ni_id={?}', $this->_uid, $p);
    }

    public function add($p)
    {
        XDB::execute('INSERT INTO  watch_nonins (uid,ni_id) VALUES({?},{?})', $this->_uid, $p);
        $res = XDB::query('SELECT  prenom,IF(nom_usage="",nom,nom_usage) AS nom,promo,user_id
                             FROM  auth_user_md5
                            WHERE  user_id={?}', $p);
        $this->_data["$p"] = $res->fetchOneAssoc();
    }
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
