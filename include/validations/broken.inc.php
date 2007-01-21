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


class BrokenReq extends Validate
{
    // {{{ properties

    var $m_forlife;
    var $m_bestalias;
    var $m_prenom;
    var $m_nom;
    var $m_promo;
    var $m_sexe;
    var $m_email;
    var $old_email;
    var $m_comment;

    var $rules = "Accepter si l'adresse mail parait correcte, et pas absurde (ou si le marketeur est de confiance).
    Si le demandeur marque sa propre adresse mail, refuser dans tous les cas.
    Si l'adresse proposée est surveillée, refuser.
    Si le compte associé est désactivé, étudier le cas en fonction de la raison de la désactivation";
    // }}}
    // {{{ constructor

    function BrokenReq($sender, $user, $email, $comment = null)
    {
        $this->Validate($sender, false, 'broken');
        $this->m_email     = $email;
        $this->m_comment   = trim($comment);
        $this->m_forlife   = $user['forlife'];
        $this->m_bestalias = $user['bestalias'];
        $this->m_prenom    = $user['prenom'];
        $this->m_nom       = $user['nom'];
        $this->m_promo     = $user['promo'];
        $this->m_sexe      = $user['sexe'];
        $this->old_email   = $user['email'];
    }

    // }}}
    // {{{ function formu()

    function formu()
    {
        return 'include/form.valid.broken.tpl';
    }

    // }}}
    // {{{ function _mail_subj
    
    function _mail_subj()
    {
        return "[Polytechnique.org] Récupération de {$this->m_prenom} {$this->m_nom} ({$this->m_promo})";
    }

    // }}}
    // {{{ function _mail_body

    function _mail_body($isok)
    {
        if ($isok) {
            return "  Un mail de contact vient d'être envoyé "
                ." à {$this->m_prenom} {$this->m_nom} ({$this->m_promo}) pour confirmer sa volonté de"
                ." mettre à jour sa redirection Polytechnique.org!\n\n"
                ."Merci de ta participation !\n";
        } else {
            return "  Nous n'avons pas jugé bon d'envoyer de mail de contact à {$this->m_prenom} {$this->m_nom} ({$this->m_promo}).";
        }
    }

    // }}}
    // {{{ function commit()

    function commit()
    {
        global $globals;
        $email =  $this->m_bestalias . '@' . $globals->mail->domain;
        if ($this->old_email) {
            $subject = "Ton adresse $email semble ne plus fonctionner";
            $reason  = "Nous avons été informés que ton adresse $email ne fonctionne plus correctement par un camarade";
        } else {
            $res = XDB::iterRow("SELECT  email
                                   FROM  emails AS e
                             INNER JOIN  aliases AS a ON (a.id = e.uid)
                                  WHERE  a.alias = {?} AND e.flags = 'panne'", $this->m_forlife);
            $redirect = array();
            while (list($red) = $res->next()) {
                list(, $redirect[]) = explode('@', $red);
            }
            $subject = "Ton adresse $email ne fonctionne plus";
            $reason  = "Ton adresse $email ne fonctionne plus ";
            if (!count($redirect)) {
                $reason .= '.';
            } elseif (count($redirect) == 1) {
                $reason .= ' car sa redirection vers ' . $redirect[0] . ' est hors-service depuis plusiers mois.';
            } else {
                $reason .= ' cas ses redirections vers ' . implode(', ', $redirect) 
                        . ' sont hors-services depuis plusieurs mois.';
            }
        }
        $body = ($this->m_sexe ? 'Chère ' : 'Cher ') . $this->m_prenom . ",\n\n"
              . $reason . "\n\n"
              . "L'adresse {$this->m_email} nous a été communiquée, veux-tu que cette adresse devienne ta nouvelle "
              . "adresse devienne ta nouvelle adresse de redirection ? Si oui, envoie nous des informations qui "
              . "nous permettrons de nous assurer de ton identité (par exemple ta date de naissance et ta promotion)\n"
              . "-- \nTrès Cordialement,\nL'Equipe de Polytechnique.org\n";
        $body = wordwrap($body, 78);
        $mailer = new PlMailer();
        $mailer->setFrom('"Association Polytechnique.org" <register@polytechnique.org>');
        $mailer->addTo($this->m_email);
        $mailer->setSubject($subject);
        $mailer->setTxtBody($body);
        return $mailer->send();
    }

    // }}}
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
