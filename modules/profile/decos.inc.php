<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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
    private static function compareMedals(array $a, array $b)
    {
        if ($a['id'] == $b['id']) {
            if ($a['grade'] == $b['grade']) {
                return $a['level'] > $b['level'];
            }
            return $a['grade'] > $b['grade'];
        }
        return $a['id'] > $b['id'];
    }

    public function value(ProfilePage $page, $field, $value, &$success)
    {
        $success = true;
        if (is_null($value) || !S::user()->isMyProfile($profile) &&
            $page->values['medals_pub'] == 'private' && !S::user()->checkPerms(User::PERM_DIRECTORY_PRIVATE)) {
            // Fetch already attributed medals
            $value = XDB::fetchAllAssoc("SELECT  m.mid AS id, m.gid AS grade, 1 AS valid, FIND_IN_SET('has_levels', e.flags) AS has_levels, m.level
                                           FROM  profile_medals     AS m
                                     INNER JOIN  profile_medal_enum AS e ON (m.mid = e.id)
                                          WHERE  m.pid = {?}",
                                        $page->pid());

            // Fetch not yet validated medals
            $medals = ProfileValidate::get_typed_requests($page->pid(), 'medal');
            foreach ($medals as &$medal) {
                $value[] = array(
                    'id'         => $medal->mid,
                    'grade'      => $medal->gid,
                    'level'      => $medal->level,
                    'has_levels' => $medal->has_levels,
                    'valid'      => '0'
                );
            }
        } elseif (!is_array($value)) {
            $value = array();
        }
        usort($value, 'self::compareMedals');
        return $value;
    }

    public function save(ProfilePage $page, $field, $value)
    {
        $original =& $page->orig[$field];

        $i = $j = 0;
        $total_original = count($original);
        $total_value = count($value);

        if ($total_original && !S::user()->isMyProfile($profile) &&
            $page->values['medals_pub'] == 'private' && !S::user()->checkPerms(User::PERM_DIRECTORY_PRIVATE)) {
            return;
        }

        while ($i < $total_original || $j < $total_value) {
            if (isset($value[$j]) && (!isset($original[$i]) || self::compareMedals($original[$i], $value[$j]))) {
                $req = new MedalReq(S::user(), $page->profile, $value[$j]['id'], $value[$j]['grade'], $value[$j]['level'], $value[$j]['has_levels']);
                $req->submit();
                sleep(1);
                ++$j;
            } elseif (isset($original[$i]) && (!isset($value[$j]) || self::compareMedals($value[$j], $original[$i]))) {
                if ($original[$i]['valid']) {
                    XDB::execute('DELETE FROM  profile_medals
                                        WHERE  pid = {?} AND mid = {?} AND gid = {?}',
                                 $page->pid(), $original[$i]['id'], $original[$i]['grade']);
                } else {
                    $req = MedalReq::get_request($page->pid(), $original[$i]['id'], $original[$i]['grade'], $value[$j]['level']);
                    if ($req) {
                        $req->clean();
                    }
                }
                ++$i;
            } else {
                ++$i;
                ++$j;
            }
        }
    }

    public function getText($value) {
        $medalsList = DirEnum::getOptions(DirEnum::MEDALS);
        $medals = array();
        foreach ($value as $id => $medal) {
            $medals[] = $medalsList[$id];
        }
        return implode(', ', $medals);
    }
}

class ProfilePageDecos extends ProfilePage
{
    protected $pg_template = 'profile/deco.tpl';

    public function __construct(PlWizard $wiz)
    {
        parent::__construct($wiz);
        $this->settings['medals_pub'] = new ProfileSettingPub();
        $this->settings['medals'] = new ProfileSettingDeco();
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
        if ($this->changed['medals_pub'] && (S::user()->isMyProfile($profile) || S::user()->checkPerms(User::PERM_DIRECTORY_PRIVATE))) {
            XDB::execute("UPDATE  profiles
                             SET  medals_pub = {?}
                           WHERE  pid = {?}",
                         $this->values['medals_pub'], $this->pid());
        }
    }

    public function _prepare(PlPage $page, $id)
    {
        $res = XDB::iterator('SELECT  *, FIND_IN_SET(\'validation\', flags) AS validate
                                FROM  profile_medal_enum
                            ORDER BY  type, text');
        $mlist = array();
        while ($tmp = $res->next()) {
            $mlist[$tmp['type']][] = $tmp;
        }
        $page->assign('medal_list', $mlist);
        $fullType = array(
            'ordre'      => 'Ordres',
            'croix'      => 'Croix',
            'militaire'  => 'Médailles militaires',
            'honneur'    => 'Médailles d\'honneur',
            'resistance' => 'Médailles de la résistance',
            'prix'       => 'Prix',
            'sport'      => 'Médailles sportives'
        );
        $page->assign('fullType', $fullType);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
