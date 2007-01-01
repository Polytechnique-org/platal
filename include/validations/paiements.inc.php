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

// {{{ class PayReq

class PayReq extends Validate
{
    // {{{ properties
    
    var $titre;
    var $site;

    var $montant;
    var $montant_min;
    var $montant_max;

    var $msg_reponse;
    var $asso_id;
    var $asso;
    var $evt;
    var $evt_intitule;

    var $rules = "Laisser la validation à un trésorier";
    // }}}
    // {{{ constructor

    function PayReq($_uid, $_intitule, $_site, $_montant, $_msg,
                    $_montantmin=0, $_montantmax=999, $_asso_id = 0,
                    $_evt = 0, $_stamp=0)
    {
        $this->Validate($_uid, false, 'paiements', $_stamp);

        $this->titre        = $_intitule;
        $this->site         = $_site;
        $this->msg_reponse  = $_msg;
        $this->asso_id      = $_asso_id;
        $this->evt          = $_evt;
        $this->montant      = $_montant;
        $this->montant_min  = $_montantmin;
        $this->montant_max  = $_montantmax;

        if ($_asso_id) {
            $res = XDB::query("SELECT nom FROM groupex.asso WHERE id = {?}", $_asso_id);
            $this->asso = $res->fetchOneCell();
        }
        if ($_asso_id && $_evt) {
            $res = XDB::query("SELECT intitule FROM groupex.evenements WHERE asso_id = {?} AND eid = {?}", $_asso_id, $_evt);
            $this->evt_intitule = $res->fetchOneCell();
        }
    }

    // }}}
  // {{{ function same_event()
    static function same_event($evt, $asso_id)
    {
        $wevt = 's:3:"evt";s:'.strlen($evt+"").':"'.$evt.'"';
        $wassoid = 's:7:"asso_id";s:'.strlen($asso_id + "").':"'.$asso_id.'"';
        $where = "%".$wassoid."%".$wevt."%";
        return $where;
    }
  // }}}
  // {{{ function submit() 
  // supprime les demandes de paiments pour le meme evenement
    function submit()
    {
        if ($this->evt)
        {
            XDB::execute('DELETE FROM requests WHERE type={?} AND data LIKE {?}', 'paiements', PayReq::same_event($this->evt, $this->asso_id));
        }
        Validate::submit();
    }
    // }}}
      // {{{ function formu()

    function formu()
    { return 'include/form.valid.paiements.tpl'; }

    // }}}
    // {{{ function editor()

    function editor()
    {
        return 'include/form.valid.edit-paiements.tpl';
    }

    // }}}
    // {{{ function handle_editor()

    function handle_editor()
    {
        $this->titre       = Env::v('pay_titre');
        $this->site        = Env::v('pay_site');
        $this->montant     = Env::i('pay_montant');
        $this->montant_min = Env::i('pay_montant_min');
        $this->montant_max = Env::i('pay_montant_max');
        $this->msg_reponse = Env::v('pay_msg_reponse');
        return true;
    }

    // }}}
    // {{{ function _mail_subj

    function _mail_subj()
    {
        return "[Polytechnique.org/Paiments] Demande de création de paiement {$this->titre}";
    }

    // }}}
    // {{{ function _mail_body

    function _mail_body($isok)
    {
        if ($isok) {
            return "  Le paiement que tu avais demandé pour {$this->titre} vient d'être créé.".($this->evt?" Il a bien été associé à la gestion de l'événement du groupe":"");
        } else {
            return "  La demande que tu avais faite pour le paiement de {$this->intitule} a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()
    
    function commit()
    {
        global $globals;
        $res = XDB::query("SELECT MAX(id) FROM paiement.paiements");
        $id = $res->fetchOneCell()+1;
        $ret = XDB::execute("INSERT INTO paiement.paiements VALUES
            ( {?}, {?}, {?}, '',
            {?}, {?}, {?},
            {?}, {?}, {?} )
            ",
            $id, $this->titre, $this->site,
            $this->montant, $this->montant_min, $this->montant_max,
            $this->bestalias."@".$globals->mail->domain, $this->msg_reponse, $this->asso_id);
        if ($this->asso_id && $this->evt) 
            $ret = XDB::execute("UPDATE groupex.evenements SET paiement_id = {?} WHERE asso_id = {?} AND eid = {?}", $id, $this->asso_id, $this->evt);

        return $ret;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
