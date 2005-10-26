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

// {{{ class ListeReq

class ListeReq extends Validate
{
    // {{{ properties
    
    var $liste;
    var $desc;

    var $advertise;
    var $modlevel;
    var $inslevel;

    var $owners;
    var $members;

    var $rules = "Refuser les listes de binets si elles ne sont pas datées (apv20002@ et non apv@).
        Refuser également des listes qui pourraient nous servir (admin, postmaster,...)";
    // }}}
    // {{{ constructor
    
    function ListeReq($_uid, $_liste, $_desc, $_advertise, $_modlevel, $_inslevel, $_owners, $_members, $_stamp=0)
    {
        global $globals;
        $this->Validate($_uid, true, 'liste', $_stamp);
        
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

    function formu()
    { return 'include/form.valid.listes.tpl'; }

    // }}}
    // {{{ function _mail_subj

    function _mail_subj()
    {
        return "[Polytechnique.org/LISTES] Demande de la liste {$this->liste}";
    }

    // }}}
    // {{{ function _mail_body

    function _mail_body($isok)
    {
        if ($isok) {
            return "  La mailing list {$this->liste} que tu avais demandée vient d'être créée.";
        } else {
            return "  La demande que tu avais faite pour la mailing list {$this->liste} a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()
    
    function commit()
    {
        global $globals;
        require_once('platal/xmlrpc-client.inc.php');
        require_once('lists.inc.php');

        $client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'));
        $ret = $client->create_list($this->liste, $this->desc,
            $this->advertise, $this->modlevel, $this->inslevel,
            $this->owners, $this->members);
        $liste = strtolower($this->liste);
        if ($ret) {
            foreach(Array($liste, $liste."-owner", $liste."-admin", $liste."-bounces") as $l) {
                $globals->xdb->execute("INSERT INTO aliases (alias,type) VALUES({?}, 'liste')", $l);
            }
        }
        return $ret;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
