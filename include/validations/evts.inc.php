<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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

// {{{ class EvtReq

class EvtReq extends Validate
{
    // {{{ properties
    
    var $evtid;
    var $titre;
    var $texte;
    var $pmin;
    var $pmax;
    var $peremption;    
    var $comment;
    
    // }}}
    // {{{ constructor

    function EvtReq($_titre, $_texte, $_pmin, $_pmax, $_peremption, $_comment, $_uid) {
        global $globals;
        $this->Validate($_uid, false, 'evts', $_stamp);
        $this->titre      = $_titre;
        $this->texte      = $_texte;
        $this->pmin       = $_pmin;
        $this->pmax       = $_pmax;
        $this->peremption = $_peremption;
        $this->comment    = $_comment;
    }

    // }}}
    // {{{ function get_unique_request()
    
    function get_unique_request($uid)
    {
        return false;  //non unique
    }

    // }}}
    // {{{ function formu()

    function formu()
    { return 'include/form.valid.evts.tpl'; }

    // }}}
    // {{{ function _mail_subj
    
    function _mail_subj()
    {
        return "[Polytechnique.org/EVENEMENTS] Proposition d'événement";
    }

    // }}}
    // {{{ function _mail_body

    function _mail_body($isok)
    {
        if ($isok) {
            return "  L'annonce que tu avais proposée ({$this->titre}) vient d'être validée.";
        } else {
            return "  L'annonce que tu avais proposée ({$this->titre}) a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()

    function commit()
    {
        global $globals;
        $globals->xdb->execute(
                "INSERT INTO  evenements
                         SET  user_id = {?}, creation_date=NULL, titre={?}, texte={?},
                              peremption={?}, promo_min={?}, promo_max={?}, flags=CONCAT(flags,',valide')",
                $this->_uid, $this->titre, $this->texte,
                $this->peremption, $this->pmin, $this->pmax);
        return true;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
