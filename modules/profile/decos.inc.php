<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

class ProfileSettingDeco implements ProfileSetting
{
    public function value(ProfilePage &$page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value)) {
            // Fetch already attributed medals
            $res = XDB::iterRow("SELECT  m.id AS id, s.gid AS grade
                                   FROM  profile_medals    AS s
                             INNER JOIN  profile_medal_enum        AS m ON ( s.mid = m.id )
                                  WHERE  s.pid = {?}",
                                $page->pid());
            $value = array();
            while (list($id, $grade) = $res->next()) {
                $value[$id] = array('grade' => $grade,
                                    'valid' => '1');
            }

            // Fetch not yet validated medals
            require_once('validations.inc.php');
            $medals = Validate::get_typed_requests(S::i('uid'), 'medal');
            foreach ($medals as &$medal) {
                $value[$medal->mid] = array('grade' => $medal->gid,
                                            'valid' => '0');
            }
        } else if (!is_array($value)) {
            $value = array();
        }
        ksort($value);
        return $value;
    }

    public function save(ProfilePage &$page, $field, $value)
    {
        require_once('validations.inc.php');

        $orig =& $page->orig[$field];

        // Remove old ones
        foreach ($orig as $id=>&$val) {
            if (!isset($value[$id]) || $val['grade'] != $value[$id]['grade']) {
                if ($val['valid']) {
                    XDB::execute("DELETE FROM  profile_medals
                                        WHERE  pid = {?} AND mid = {?}",
                                 $page->pid(), $id);
                } else {
                    $req = MedalReq::get_request(S::i('uid'), $id);
                    if ($req) {
                        $req->clean();
                    }
                }
            }
        }

        // Add new ones
        foreach ($value as $id=>&$val) {
            if (!isset($orig[$id]) || $orig[$id]['grade'] != $val['grade']) {
                $req = new MedalReq(S::user(), $id, $val['grade']);
                $req->submit();
                sleep(1);
            }
        }
    }
}

class ProfileSettingDecos extends ProfilePage
{
    protected $pg_template = 'profile/deco.tpl';

    public function __construct(PlWizard &$wiz)
    {
        parent::__construct($wiz);
        $this->settings['medals'] = new ProfileSettingDeco();
        $this->settings['medals_pub'] = new ProfileSettingPub();
        $this->watched['medals'] = true;
    }

    protected function _fetchData()
    {
        $res = XDB::query("SELECT  medals_pub
                             FROM  profiles
                            WHERE  pid = {?}",
                          $this->pid());
        $this->values['medals_pub'] = $res->fetchOneCell();
    }

    protected function _saveData()
    {
        if ($this->changed['medals_pub']) {
            XDB::execute("UPDATE  profiles
                             SET  medals_pub = {?}
                           WHERE  pid = {?}",
                         $this->values['medals_pub'], $this->pid());
        }
    }

    public function _prepare(PlPage &$page, $id)
    {
        $res    = XDB::iterator("SELECT  *, FIND_IN_SET('validation', flags) AS validate
                                   FROM  profile_medal_enum
                               ORDER BY  type, text");
        $mlist  = array();
        while ($tmp = $res->next()) {
            $mlist[$tmp['type']][] = $tmp;
        }
        $page->assign('medal_list', $mlist);
        $trad = Array('ordre'      => 'Ordres',
                      'croix'      => 'Croix',
                      'militaire'  => 'Médailles militaires',
                      'honneur'    => 'Médailles d\'honneur',
                      'resistance' => 'Médailles de la résistance',
                      'prix'       => 'Prix');
        $page->assign('trad', $trad);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
