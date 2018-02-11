<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

// {{{ class MedalReq

class MedalReq extends ProfileValidate
{
    // {{{ properties

    public $mid;
    public $gid;
    public $level;
    public $has_levels;

    // }}}
    // {{{ constructor

    public function __construct(User $_user, Profile $_profile, $_idmedal, $_subidmedal, $_level, $has_levels, $_stamp = 0)
    {
        parent::__construct($_user, $_profile, false, 'medal', $_stamp);
        $this->mid = $_idmedal;
        $this->gid = $_subidmedal;
        $this->level = $_level;
        $this->has_levels = $has_levels;
        if (is_null($this->gid)) {
            $this->gid = 0;
        }
        if (!$this->has_levels) {
            $this->level = '';
        }
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.medals.tpl';
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return '[Polytechnique.org/Décoration] Demande de décoration : ' . $this->medal_name();
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return '  La décoration ' . $this->medal_name() . ' vient d\'être ajoutée à ta fiche.';
        } else {
            return '  La demande que tu avais faite pour la décoration ' . $this->medal_name() . ' a été refusée.';
        }
    }

    // }}}
    // {{{ function medal_name

    public function medal_name()
    {
        $name = XDB::fetchOneCell('SELECT  text
                                     FROM  profile_medal_enum
                                    WHERE  id = {?}', $this->mid);
        $grade = XDB::fetchOneCell('SELECT  text
                                      FROM  profile_medal_grade_enum
                                     WHERE  mid = {?} AND gid = {?}',
                                   $this->mid, $this->gid);
        if (is_null($grade)) {
            return $name;
        }
        return $name . ' (' . $grade . ')';
    }

    // }}}
    // {{{ function submit()

    public function submit()
    {
        $res = XDB::query("SELECT  FIND_IN_SET('validation', flags)
                             FROM  profile_medal_enum
                            WHERE  id = {?}", $this->mid);
        if ($res->fetchOneCell()) {
            parent::submit();
        } else {
            $this->commit();
        }
    }

    // }}}
    // {{{ function commit()

    public function commit ()
    {
        return XDB::execute('INSERT INTO  profile_medals (pid, mid, gid, level)
                                  VALUES  ({?}, {?}, {?}, {?})
                 ON DUPLICATE KEY UPDATE  gid = VALUES(gid)',
                            $this->profile->id(), $this->mid,
                            is_null($this->gid) ? 0 : $this->gid, $this->level);
    }

    // }}}
    // {{{ function get_request($medal)

    static public function get_request($pid, $type, $grade, $level)
    {
        $reqs = parent::get_typed_requests($pid, 'medal');
        foreach ($reqs as &$req) {
            if ($req->mid == $type && $req->gid == $grade && $req->level == $level) {
                return $req;
            }
        }
        return null;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
