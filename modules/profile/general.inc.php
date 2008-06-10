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

class ProfileNom implements ProfileSetting
{
    private function matchWord($old, $new, $newLen) {
        return ($i = strpos($old, $new)) !== false
            && ($i == 0 || $old{$i-1} == ' ')
            && ($i + $newLen == strlen($old) || $old{$i + $newLen} == ' ');
    }

    private function prepareField($value)
    {
        $value = strtoupper(replace_accent($value));
        return preg_replace('/[^A-Z]/', ' ', $value);
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        $current = S::v($field);
        $init    = S::v($field . '_ini');
        if (is_null($value)) {
            return $current;
        }
        if ($value == $current || $value == $init) {
            return $value;
        }
        $ini = $this->prepareField($init);
        $old = $this->prepareField($current);
        $new = $this->prepareField($value);
        $newLen = strlen($new);
        $success = $this->matchWord($old, $new, $newLen)
                || $this->matchWord($ini, $new, $newLen)
                || ($field == 'nom' && $new == 'DE ' . $old);
        if (!$success) {
            global $page;
            $page->trigError("Le $field que tu as choisi ($value) est trop loin de ton $field initial ($init)"
                       . (($init == $current)? "" : " et de ton prénom précédent ($current)"));
        }
        return $success ? $value : $current;
    }

    public function save(ProfilePage &$page, $field, $new_value)
    {
        $_SESSION[$field] = $new_value;
    }
}

class ProfileSearchName implements ProfileSetting
{

    public function __construct()
    {
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
    }

    public function save(ProfilePage &$page, $field, $new_value)
    {
    }
}

class ProfileAppli implements ProfileSetting
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            return $page->values[$field];
        }
        return $value;
    }

    public function save(ProfilePage &$page, $field, $new_value)
    {
        $index = ($field == 'appli1' ? 0 : 1);
        if ($new_value['id'] > 0) {
            XDB::execute("REPLACE INTO  applis_ins
                                   SET  uid = {?}, aid = {?}, type = {?}, ordre = {?}",
                         S::i('uid'), $new_value['id'], $new_value['type'], $index);
        } else {
            XDB::execute("DELETE FROM  applis_ins
                                WHERE  uid = {?} AND ordre = {?}",
                         S::i('uid'), $index);
        }
    }
}

class ProfileNetworking implements ProfileSetting
{
    private $email;
    private $pub;
    private $web;

    public function __construct()
    {
        $this->email  = new ProfileEmail();
        $this->pub    = new ProfilePub();
        $this->web    = new ProfileWeb();
    }

    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        if (is_null($value)) {
            $value = array();
            $res = XDB::iterator("SELECT  n.address, n.network_type AS type, n.pub, m.name
                                    FROM  profile_networking AS n
                              INNER JOIN  profile_networking_enum AS m ON (n.network_type = m.network_type)
                                   WHERE  n.uid = {?}",
                                 S::i('uid'));
            while($network = $res->next()) {
                $value[] = $network;
            }
        }
        if (!is_array($value)) {
            $value = array();
        }
        $res = XDB::iterator("SELECT filter, network_type AS type
                                FROM profile_networking_enum;");
        $filters = array();
        while($filter = $res->next()) {
            $filters[$filter['type']] = $filter['filter'];
        }
        $success = true;
        foreach($value as $i=>&$network) {
            if(!isset($network['pub'])) {
                $network['pub'] = 'private';
            }
            $network['error'] = false;
            $network['pub'] = $this->pub->value($page, 'pub', $network['pub'], $s);
            $s = true;
            if ($filters[$network['type']] == 'web') {
                $network['address'] = $this->web->value($page, 'address', $network['address'], $s);
            } elseif ($filters[$network['type']] == 'email') {
                $network['address'] = $this->email->value($page, 'address', $network['address'], $s);
            }
            if (!$s) {
                $success = false;
                $network['error'] = true;
            }
        }
        return $value;
    }

    public function save(ProfilePage &$page, $field, $value)
    {
        XDB::execute("DELETE FROM profile_networking
                            WHERE uid = {?}",
                     S::i('uid'));
        if (!count($value)) {
            return;
        }
        $insert = array();
        foreach ($value as $id=>$network) {
            XDB::execute("INSERT INTO  profile_networking (uid, nwid, network_type, address, pub)
                               VALUES  ({?}, {?}, {?}, {?}, {?})",
                         S::i('uid'), $id, $network['type'], $network['address'], $network['pub']);
        }
    }
}

class ProfileGeneral extends ProfilePage
{
    protected $pg_template = 'profile/general.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['nom'] = $this->settings['prenom']
                               = new ProfileNom();
        $this->settings['naissance'] = new ProfileDate();
        $this->settings['mobile_pub']
                                  = $this->settings['freetext_pub']
                                  = $this->settings['photo_pub']
                                  = new ProfilePub();
        $this->settings['freetext']
                                  = $this->settings['nationalite']
                                  = $this->settings['nick']
                                  = $this->settings['yourself']
                                  = $this->settings['display_name']
                                  = $this->settings['sort_name']
                                  = $this->settings['tooltip_name']
                                  = null;
        $this->settings['synchro_ax']
                                  = new ProfileBool();
        $this->settings['mobile'] = new ProfileTel();
        $this->settings['networking'] = new ProfileNetworking();
        $this->settings['appli1']
                                  = $this->settings['appli2']
                                  = new ProfileAppli();
        $this->watched= array('nom' => true, 'freetext' => true, 'mobile' => true, 'networking' => true,
                       'appli1' => true, 'appli2' => true, 'nationalite' => true, 'nick' => true);
    }

    protected function _fetchData()
    {
        // Checkout all data...
        $res = XDB::query("SELECT  u.promo, u.promo_sortie, u.nom_usage, u.nationalite, u.naissance,
                                   q.profile_mobile as mobile, q.profile_mobile_pub as mobile_pub,
                                   q.profile_freetext as freetext, q.profile_freetext_pub as freetext_pub,
                                   q.profile_nick as nick, q.profile_from_ax as synchro_ax, u.matricule_ax,
                                   IF(a1.aid IS NULL, -1, a1.aid) as appli_id1, a1.type as appli_type1,
                                   IF(a2.aid IS NULL, -1, a2.aid) as appli_id2, a2.type as appli_type2,
                                   n.yourself, n.display AS display_name, n.sort AS sort_name,
                                   n.tooltip AS tooltip_name
                             FROM  auth_user_md5   AS u
                       INNER JOIN  auth_user_quick AS q  ON(u.user_id = q.user_id)
                       INNER JOIN  profile_names_display AS n ON(n.user_id = u.user_id)
                        LEFT JOIN  applis_ins      AS a1 ON(a1.uid = u.user_id and a1.ordre = 0)
                        LEFT JOIN  applis_ins      AS a2 ON(a2.uid = u.user_id and a2.ordre = 1)
                            WHERE  u.user_id = {?}", S::v('uid', -1));
        $this->values = $res->fetchOneAssoc();

        // Reformat formation data
        $this->values['appli1'] = array('id'    => $this->values['appli_id1'],
                                        'type'  => $this->values['appli_type1']);
        unset($this->values['appli_id1']);
        unset($this->values['appli_type1']);
        $this->values['appli2'] = array('id'    => $this->values['appli_id2'],
                                        'type'  => $this->values['appli_type2']);
        unset($this->values['appli_id2']);
        unset($this->values['appli_type2']);

        // Retreive photo informations
        $res = XDB::query("SELECT  pub
                             FROM  photo
                            WHERE  uid = {?}", S::v('uid'));
        $this->values['photo_pub'] = $res->fetchOneCell();

        $res = XDB::query("SELECT  COUNT(*)
                             FROM  requests
                            WHERE  type='photo' AND user_id = {?}",
                          S::v('uid'));
        $this->values['nouvellephoto'] = $res->fetchOneCell();
        
        // Retreive search names info
        $this->values['search_names'] = XDB::iterator("
                              SELECT sn.search_name, sn.name_type, sn.pub, sn.sn_id
                                FROM profile_names_search AS sn
                               WHERE sn.user_id = {?}
                            ORDER BY sn.name_type, search_score, search_name",
                          S::v('uid'));
    }

    protected function _saveData()
    {
        if ($this->changed['nationalite'] || $this->changed['nom'] || $this->changed['prenom']
            || $this->changed['naissance']) {
           XDB::execute("UPDATE  auth_user_md5
                            SET  nationalite = {?}, nom={?}, prenom={?}, naissance={?}
                          WHERE  user_id = {?}",
                         $this->values['nationalite'], $this->values['nom'], $this->values['prenom'],
                         preg_replace('@(\d{2})/(\d{2})/(\d{4})@', '\3-\2-\1', $this->values['naissance']),
                         S::v('uid'));
        }
        if ($this->changed['nick'] || $this->changed['mobile'] || $this->changed['mobile_pub']
            || $this->changed['freetext']
            || $this->changed['freetext_pub'] || $this->changed['synchro_ax']) {
            XDB::execute("UPDATE  auth_user_quick
                             SET  profile_nick= {?}, profile_mobile={?}, profile_mobile_pub={?}, 
                                  profile_freetext={?},
                                  profile_freetext_pub={?}, profile_from_ax = {?} 
                           WHERE  user_id = {?}", 
                         $this->values['nick'], $this->values['mobile'], $this->values['mobile_pub'],
                         $this->values['freetext'], $this->values['freetext_pub'],
                         $this->values['synchro_ax'], S::v('uid'));
        }
        if ($this->changed['nick']) {
            require_once('user.func.inc.php');
            user_reindex(S::v('uid'));
        }
        if ($this->changed['photo_pub']) {
            XDB::execute("UPDATE  photo
                             SET  pub = {?}
                           WHERE  uid = {?}",
                         $this->values['photo_pub'], S::v('uid'));
        }
        if ($this->changed['yourself'] || $this->changed['sort_name'] ||
            $this-> changed['display_name'] || $this->changed['tooltip_name']) {
          XDB::execute("UPDATE profile_names_display AS n
                           SET n.yourself = {?},
                               n.sort = {?}, ". // SET
                              "n.display = {?}, ". // SET
                              "n.tooltip = {?} ". // SET
                        "WHERE n.user_id = {?}",
                       $this->values['yourself'],
                       $this->values['sort_name'],
                       $this->values['display_name'],
                       $this->values['tooltip_name'],
                        S::v('uid'));
        }
    }

    public function _prepare(PlatalPage &$page, $id)
    {
        require_once "applis.func.inc.php";

        $res = XDB::iterator("SELECT nw.network_type AS type, nw.name
                                FROM profile_networking_enum AS nw
                            ORDER BY name");
        $page->assign('network_list', $res->fetchAllAssoc());
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
