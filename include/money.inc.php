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

// {{{ class Payment

class Payment
{
    // {{{ properties

    var $id;
    var $text;
    var $url;
    var $flags;
    var $mail;
    var $montant_min;
    var $montant_max;
    var $montant_def;
    var $asso_id;

    var $api = null;

    // }}}
    // {{{ constructor
    
    function Payment($ref=-1)
    {
        global $globals;
        $r   = $ref==-1 ? $globals->money->mpay_def_id : $ref;
        $res = XDB::query("SELECT  id, text, url, flags, mail, montant_min, montant_max, montant_def, asso_id
                                       FROM  {$globals->money->mpay_tprefix}paiements WHERE id={?}", $r);
        list($this->id, $this->text, $this->url, $flags, $this->mail,
                $this->montant_min, $this->montant_max, $this->montant_def, $this->asso_id) = $res->fetchOneRow();
        
        $this->montant_min = (float)$this->montant_min;
        $this->montant_max = (float)$this->montant_max;
        $this->flags       = new Flagset($flags);

        return $link;
    }

    // }}}
    // {{{ function check()
    
    function check($value)
    {
        $v = (float)strtr($value, ',', '.');
        if ($this->montant_min > $v) {
            return "Montant inférieur au minimum autorisé ({$this->montant_min}).";
        } elseif ($v > $this->montant_max) {
            return "Montant supérieur au maximum autorisé ({$this->montant_max}).";
        } else {
            return true;
        }
    }

    // }}}
    // {{{ function init()

    function init($val, &$meth)
    {
        require_once('money/'.$meth->inc);
        $this->api = new $api($val);
    }

    // }}}
    // {{{ function prepareform()

    function prepareform()
    {
        return $this->api->prepareform($this);
    }

    function event()
    {
        global $globals;
        if ($this->asso_id)
        {
            $res = XDB::query("SELECT eid, a.diminutif FROM groupex.evenements AS e, groupex.asso AS a WHERE e.asso_id = {?} AND a.id = {?}", $this->asso_id, $this->asso_id);
            return $res->fetchOneAssoc();
        }
        return null;
    }
}

// }}}
// {{{ class PayMethod

class PayMethod
{
    // {{{ properties

    var $id;
    var $text;
    var $inc;

    // }}}
    // {{{ constructor

    function PayMethod($id=-1)
    {
        global $globals;
        $i   = $id==-1 ? $globals->money->mpay_def_meth : $id;
        $res = XDB::query("SELECT id,text,include FROM {$globals->money->mpay_tprefix}methodes WHERE id={?}", $i);
        list($this->id, $this->text, $this->inc) = $res->fetchOneRow();
    } 

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
