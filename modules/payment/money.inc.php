<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

class Payment
{
    public $id;
    public $text;
    public $url;
    public $flags;
    public $mvarail;
    public $amount_min;
    public $amount_max;
    public $amount_def;
    public $asso_id;

    public $api = null;

    function Payment($ref = -1)
    {
        global $globals;

        $r   = ($ref == -1) ? $globals->money->mpay_def_id : $ref;
        $res = XDB::query('SELECT  id, text, url, flags, mail, amount_min, amount_max, amount_def, asso_id
                             FROM  payments
                            WHERE  id = {?}', $r);
        list($this->id, $this->text, $this->url, $flags, $this->mail,
             $this->amount_min, $this->amount_max, $this->amount_def, $this->asso_id) = $res->fetchOneRow();

        $this->amount_min = (float)$this->amount_min;
        $this->amount_max = (float)$this->amount_max;
        $this->flags      = new PlFlagSet($flags);
    }

    function check($value)
    {
        $v = (float)strtr($value, ',', '.');
        if ($this->amount_min > $v) {
            return "Montant inférieur au minimum autorisé ({$this->amount_min}).";
        } elseif ($v > $this->amount_max) {
            return "Montant supérieur au maximum autorisé ({$this->amount_max}).";
        } else {
            return true;
        }
    }

    function init($val, $meth)
    {
        require_once dirname(__FILE__) . '/money/' . $meth->inc;
        $this->api = new $api($val);
    }

    function prepareform(User $user)
    {
        return $this->api->prepareform($this, $user);
    }

    function event()
    {
        if ($this->asso_id) {
            $res = XDB::query("SELECT  e.eid, a.diminutif
                                 FROM  group_events AS e
                           INNER JOIN  groups AS a ON (e.asso_id = a.id)
                            LEFT JOIN  group_event_participants AS p ON (p.eid = e.eid AND p.uid = {?})
                                WHERE  e.paiement_id = {?} AND p.uid IS NULL", S::i('uid'), $this->id);
            if ($res->numRows()) {
                return $res->fetchOneAssoc();
            }
        }
        return null;
    }
}

class PayMethod
{
    public $id;
    public $text;
    public $inc;

    function PayMethod($id = -1)
    {
        global $globals;

        $i   = ($id == -1) ? $globals->money->mpay_def_meth : $id;
        $res = XDB::query('SELECT  id, text, include
                             FROM  payment_methods
                            WHERE  id = {?}', $i);
        list($this->id, $this->text, $this->inc) = $res->fetchOneRow();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
