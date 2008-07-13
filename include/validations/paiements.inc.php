<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

    public $titre;
    public $site;

    public $montant;
    public $montant_min;
    public $montant_max;

    public $msg_reponse;
    public $asso_id;
    public $asso;
    public $evt;
    public $evt_intitule;

    public $rules = "Vérifier que les balises &lt;salutation&gt;, &lt;prenom&gt;, &lt;nom&gt; et &lt;montant&gt; n'ont pas été modifiées.
Vérifier que le demandeur n'a pas laissé les crochets [].
Si le télépaiement n'est pas lié à un groupe ou supérieur à 51 euros, laisser la validation à un trésorier";
    // }}}
    // {{{ constructor

    public function __construct($_uid, $_intitule, $_site, $_montant, $_msg,
                                $_montantmin=0, $_montantmax=999, $_asso_id = 0,
                                $_evt = 0, $_stamp=0)
    {
        parent::__construct($_uid, false, 'paiements', $_stamp);

        $this->titre        = $_intitule;
        $this->site         = $_site;
        $this->msg_reponse  = $_msg;
        $this->asso_id      = (string)$_asso_id;
        $this->evt          = (string)$_evt;
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

    static public function same_event($evt, $asso_id)
    {
        $wevt = 's:3:"evt";s:'.strlen($evt+"").':"'.$evt.'"';
        $wassoid = 's:7:"asso_id";s:'.strlen($asso_id + "").':"'.$asso_id.'"';
        $where = "%".$wassoid."%".$wevt."%";
        return $where;
    }

    // }}}
    // {{{ function accept()

    // check the message
    public function accept()
    {
        // no text [AI JMIAJM IJA MIJ]
        if (preg_match('/\[[-\'"A-Z ]+\]/', $this->msg_reponse)) {
            $this->trigError("La demande de paiement n'est pas valide. Merci de compléter le texte avant de la soumettre");
            return false;
        }
        if (!preg_match('/<montant>/', $this->msg_reponse)) {
            $this->trigError("Le demande de paiement ne contient pas la balise obligatoire &lt;montant&gt;");
            return false;
        }
        return true;
    }

    // }}}
    // {{{ function submit()

    // supprime les demandes de paiments pour le meme evenement
    public function submit()
    {
        if ($this->evt)
        {
            XDB::execute('DELETE FROM requests WHERE type={?} AND data LIKE {?}', 'paiements', PayReq::same_event($this->evt, $this->asso_id));
        }
        parent::submit();
    }
    // }}}
      // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.paiements.tpl';
    }

    // }}}
    // {{{ function editor()

    public function editor()
    {
        return 'include/form.valid.edit-paiements.tpl';
    }

    // }}}
    // {{{ function handle_editor()

    protected function handle_editor()
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

    protected function _mail_subj()
    {
        return "[Polytechnique.org/Paiments] Demande de création de paiement {$this->titre}";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok) {
            return "  Le paiement que tu avais demandé pour {$this->titre} vient d'être créé.".($this->evt?" Il a bien été associé à la gestion de l'événement du groupe":"");
        } else {
            return "  La demande que tu avais faite pour le paiement de {$this->intitule} a été refusée.";
        }
    }

    // }}}
    // {{{ function commit()

    public function commit()
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
        if ($this->asso_id && $this->evt) {
            XDB::execute("UPDATE  groupex.evenements
                             SET  paiement_id = {?}
                           WHERE  asso_id = {?} AND eid = {?}",
                         $id, $this->asso_id, $this->evt);
            $res = XDB::query("SELECT  a.nom, a.diminutif, e.intitule
                                 FROM  groupex.asso AS a
                           INNER JOIN  groupex.evenements AS e ON (a.id = e.asso_id)
                                WHERE  e.eid = {?}",
                              $this->evt);
            list($nom, $diminutif, $evt) = $res->fetchOneRow();
            require_once dirname(__FILE__) . '/../../modules/xnetevents/xnetevents.inc.php';
            $participants = get_event_participants(get_event_detail($this->evt, false, $this->asso_id), null, 'nom');
            foreach ($participants as &$u) {
                if (!$u['notify_payment']) {
                    continue;
                }
                $topay = $u['montant'] - $u['paid'];
                if ($topay > 0) {
                    $mailer = new PlMailer('xnetevents/newpayment.mail.tpl');
                    $mailer->assign('asso', $nom);
                    $mailer->assign('diminutif', $diminutif);
                    $mailer->assign('evt', $evt);
                    $mailer->assign('payment', $id);
                    $mailer->assign('prenom', $u['prenom']);
                    $mailer->assign('topay', $topay);
                    $mailer->assign('to', $u['email']);
                    $mailer->send();
                }
            }
        }
        return $ret;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
