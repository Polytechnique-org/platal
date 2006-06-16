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

// {{{ class NLReq

require_once("newsletter.inc.php");

class NLReq extends Validate
{
    // {{{ properties

    var $art;
    var $rules = "Laisser valider par le NL-MASTER";
    
    // }}}
    // {{{ constructor

    function NlReq($uid, $title, $body, $append) {
        $this->Validate($uid, false, 'nl');
        $this->art = new NLArticle($title, $body, $append);
    }

    // }}}
    // {{{ function formu()

    function formu()
    {
        return 'include/form.valid.nl.tpl';
    }

    // }}}
    // {{{ function _mail_subj
    
    function _mail_subj()
    {
        return "[Polytechnique.org/NL] Proposition d'article dans la NL";
    }

    // }}}
    // {{{ function _mail_body

    function _mail_body($isok)
    {
        if ($isok) {
            return '  L\'article que tu avais proposé ('.$this->art->title().') vient d\'être validé.';
        } else {
            return '  L\'article que tu avais proposé ('.$this->art->title().') a été refusé.';
        }
    }

    // }}}
    // {{{ function commit()

    function commit()
    {
        $nl  = new Newsletter();
        $nl->saveArticle($this->art);
        return true;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
