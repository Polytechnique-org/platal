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

// {{{ class MedalReq

class MedalReq extends Validate
{
    // {{{ properties

    public $mid;
    public $gid;

    // }}}
    // {{{ constructor

    public function __construct($_uid, $_idmedal, $_subidmedal, $_stamp=0)
    {
        parent::__construct($_uid, false, 'medal', $_stamp);
        $this->mid  = $_idmedal;
        $this->gid = $_subidmedal;
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
        return "[Polytechnique.org/Décoration] Demande de décoration : ".$this->medal_name();
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return "  La décoration ".$this->medal_name()." vient d'être ajoutée à ta fiche.";
        } else {
            return "  La demande que tu avais faite pour la décoration ".$this->medal_name()." a été refusée.";
        }
    }

    // }}}
    // {{{ function medal_name

    public function medal_name()
    {
        //var_dump($this);
        $r = XDB::query("
            SELECT IF (g.text IS NOT NULL, CONCAT(m.text,' - ', g.text), m.text)
              FROM profile_medals AS m
         LEFT JOIN profile_medals_grades AS g ON(g.mid = m.id AND g.gid = {?})
             WHERE m.id = {?}", $this->gid, $this->mid);
        return $r->fetchOneCell();
    }

    // }}}
    // {{{ function submit()

    public function submit()
    {
        $res = XDB::query("SELECT  FIND_IN_SET('validation', flags)
                             FROM  profile_medals
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
        return XDB::execute('REPLACE INTO profile_medals_sub VALUES({?}, {?}, {?})', $this->uid, $this->mid, $this->gid);
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
