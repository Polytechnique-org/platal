<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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
                || $this->matchWord($ini, $new, $newLen);
        if (!$success) {
            global $page;
            $page->trig("Le $field que tu as choisi ($value) est trop loin de ton $field initial ($init)"
                       . (($init == $current)? "" : " et de ton prénom précédent ($current)"));
        }
        return $success ? $value : $current;
    }

    public function save(ProfilePage &$page, $field, $new_value)
    {
        $_SESSION[$field] = $new_value;
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

class ProfileGeneral extends ProfilePage
{
    protected $pg_template = 'profile/general.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['nom'] = $this->settings['prenom']
                               = new ProfileNom();
        $this->settings['mobile_pub']
                                  = $this->settings['web_pub']
                                  = $this->settings['freetext_pub']
                                  = $this->settings['photo_pub']
                                  = new ProfilePub();
        $this->settings['freetext']
                                  = $this->settings['nationalite']
                                  = $this->settings['nick']
                                  = null;
        $this->settings['synchro_ax']
                                  = new ProfileBool();
        $this->settings['mobile'] = new ProfileTel();
        $this->settings['web'] = new ProfileWeb();
        $this->settings['appli1']
                                  = $this->settings['appli2']
                                  = new ProfileAppli();
    }

    protected function fetchData()
    {
        if (count($this->orig) > 0) {
            $this->values = $this->orig;
            return;
        }

        // Checkout all data...
        $res = XDB::query("SELECT  u.promo, u.promo_sortie, u.nom_usage, u.nationalite,
                                   q.profile_mobile as mobile, q.profile_mobile_pub as mobile_pub,
                                   q.profile_web as web, q.profile_web_pub as web_pub,
                                   q.profile_freetext as freetext, q.profile_freetext_pub as freetext_pub,
                                   q.profile_nick as nick, q.profile_from_ax as synchro_ax, u.matricule_ax,
                                   IF(a1.aid IS NULL, -1, a1.aid) as appli_id1, a1.type as appli_type1,
                                   IF(a2.aid IS NULL, -1, a2.aid) as appli_id2, a2.type as appli_type2
                             FROM  auth_user_md5   AS u
                       INNER JOIN  auth_user_quick AS q  USING(user_id)
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
        parent::fetchData();
    }

    protected function saveData()
    {
        parent::saveData();
        if ($this->changed['nationalite'] || $this->changed['nom'] || $this->changed['prenom']) {
           XDB::execute("UPDATE  auth_user_md5
                            SET  nationalite = {?}, nom={?}, prenom={?}
                          WHERE  user_id = {?}",
                         $this->values['nationalite'], $this->values['nom'], $this->values['prenom'], S::v('uid'));
        }
        if ($this->changed['nick'] || $this->changed['mobile'] || $this->changed['mobile_pub']
            || $this->changed['web'] || $this->changed['web_pub'] || $this->changed['freetext']
            || $this->changed['freetext_pub'] || $this->changed['synchro_ax']) {
            XDB::execute("UPDATE  auth_user_quick
                             SET  profile_nick= {?}, profile_mobile={?}, profile_mobile_pub={?}, 
                                  profile_web={?}, profile_web_pub={?}, profile_freetext={?}, 
                                  profile_freetext_pub={?}, profile_from_ax = {?} 
                           WHERE  user_id = {?}", 
                         $this->values['nick'], $this->values['mobile'], $this->values['mobile_pub'],
                         $this->values['web'], $this->values['web_pub'],
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
    }

    public function prepare(PlatalPage &$page)
    {
        parent::prepare($page);
        require_once "applis.func.inc.php";
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
