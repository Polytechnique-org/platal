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

// {{{ class NLReq

require_once("newsletter.inc.php");
require_once("comletter.inc.php");
require_once("nl.inc.php");

class ComLReq extends NLReq
{
    // {{{ properties

    public $rules = "Laisser valider par le NL-MASTER";

    // }}}
    // {{{ constructor

    public function __construct(User $_user, $_title, $_body, $_append)
    {
        Validate::__construct($_user, false, 'community-letter');
        $this->art = new ComLArticle($_title, $_body, $_append);
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return "[Polytechnique.org/LettreCommunauté] Proposition d'article dans la Lettre de la communauté";
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        $nl = NewsLetter::forGroup(NewsLetter::GROUP_COMMUNITY)->getPendingIssue(true);
        $nl->saveArticle($this->art);
        return true;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
