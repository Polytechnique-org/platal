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

// {{{ class NLReq

require_once("newsletter.inc.php");

class NLReq extends Validate
{
    // {{{ properties

    public $art;
    public $rules = "Laisser valider par le NL-MASTER";

    // }}}
    // {{{ constructor

    public function __construct($uid, $title, $body, $append)
    {
        parent::__construct($uid, false, 'nl');
        $this->art = new NLArticle($title, $body, $append);
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.nl.tpl';
    }

    // }}}
    // {{{ function editor()

    public function editor()
    {
        return 'include/form.valid.edit-nl.tpl';
    }

    // }}}
    // {{{ function handle_editor()

    protected function handle_editor()
    {
        $this->art->_body   = Env::v('nl_body');
        $this->art->_title  = Env::v('nl_title');
        $this->art->_append = Env::v('nl_append');
        return true;
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return "[Polytechnique.org/NL] Proposition d'article dans la NL";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return '  L\'article que tu avais proposé ('.$this->art->title().') vient d\'être validé.';
        } else {
            return '  L\'article que tu avais proposé ('.$this->art->title().') a été refusé.';
        }
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        $nl  = new Newsletter();
        $nl->saveArticle($this->art);
        return true;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
