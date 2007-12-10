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

// {{{ class ListeReq

class ListeReq extends Validate
{
    // {{{ properties

    public $liste;
    public $desc;

    public $advertise;
    public $modlevel;
    public $inslevel;

    public $owners;
    public $members;

    public $rules = "Refuser les listes de binets si elles ne sont pas datées (oui : apv2002@, non : apv@).
        Refuser également des listes qui pourraient nous servir (admin, postmaster,...)";
    // }}}
    // {{{ constructor

    public function __construct($_uid, $_liste, $_desc, $_advertise, $_modlevel,
                                $_inslevel, $_owners, $_members, $_stamp=0)
    {
        parent::__construct($_uid, false, 'liste', $_stamp);

        $this->liste     = $_liste;
        $this->desc      = $_desc;
        $this->advertise = $_advertise;
        $this->modlevel  = $_modlevel;
        $this->inslevel  = $_inslevel;
        $this->owners    = $_owners;
        $this->members   = $_members;
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.listes.tpl';
    }

    // }}}
    // {{{ function editor()

    public function editor()
    {
        return 'include/form.valid.edit-listes.tpl';
    }

    // }}}
    // {{{ function handle_editor()

    protected function handle_editor()
    {
        if (Env::has('listname')) {
            $this->liste = trim(Env::v('listname'));
        }
        return true;
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return "[Polytechnique.org/LISTES] Demande de la liste {$this->liste}";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return "  La mailing list {$this->liste} que tu avais demandée vient d'être créée.";
        } else {
            return "  La demande que tu avais faite pour la mailing list {$this->liste} a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        $list = new MMList(S::v('uid'), S::v('password'));
        $ret = $list->create_list($this->liste, utf8_decode($this->desc), $this->advertise,
                                  $this->modlevel, $this->inslevel,
                                  $this->owners, $this->members);
        $liste = strtolower($this->liste);
        if ($ret) {
            foreach(Array($liste, $liste."-owner", $liste."-admin", $liste."-bounces", $liste."-unsubscribe") as $l) {
                XDB::execute("INSERT INTO aliases (alias,type) VALUES({?}, 'liste')", $l);
            }
        }
        return $ret;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
