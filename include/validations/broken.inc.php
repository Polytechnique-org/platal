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


class BrokenReq extends Validate
{
    // {{{ properties

    public $m_user;
    public $m_comment;
    public $m_email;

    private $m_reactive = false;

    public $rules = "Accepter si l'adresse email parait correcte, et pas absurde (ou si le marketeur est de confiance).
    Si le demandeur marque sa propre adresse email, refuser dans tous les cas.
    Si l'adresse proposée est surveillée, refuser.
    Si le compte associé est désactivé, étudier le cas en fonction de la raison de la désactivation.";
    // }}}
    // {{{ constructor

    public function __construct(User $sender, User $user, $email, $comment = null)
    {
        parent::__construct($sender, false, 'broken');
        $this->m_user      = $user;
        $this->m_comment   = trim($comment);
        $this->m_email     = $email;
    }

    // }}}
    // {{{ function formu()

    public function formu()
    {
        return 'include/form.valid.broken.tpl';
    }

    // }}}
    // {{{ function _mail_subj

    protected function _mail_subj()
    {
        return "[Polytechnique.org] Récupération de {$this->m_user->fullName()} ({$this->m_user->promo()})";
    }

    // }}}
    // {{{ function _mail_body

    protected function _mail_body($isok)
    {
        if ($isok && !$this->m_reactive) {
            return "  Un email de contact vient d'être envoyé à {$this->m_user->fullName()}"
                . " ({$this->m_user->promo()})  pour confirmer sa volonté de"
                . " mettre à jour sa redirection Polytechnique.org !\n\n"
                . "Merci de ta participation !\n";
        } elseif ($isok) {
            return "  L'adresse de redirection {$this->m_email} de {$this->m_user->fullName()} ({$this->m_user->promo()}) "
                ."vient d'être réactivée. Un email lui a été envoyé pour l'en informer.\n\n"
                ."Merci de ta participation !\n";
        } else {
            return "  Nous n'utiliserons pas cette adresse pour contacter {$this->m_user->fullName()} ({$this->m_user->promo()}).";
        }
    }

    // }}}
    // {{{ function commit()

    public function commit()
    {
        global $globals;
        $email =  $this->m_user->bestEmail();

        XDB::execute("UPDATE  emails
                         SET  flags = 'active', panne_level = 2
                       WHERE  uid = {?} AND email = {?}", $this->m_user->id(), $this->m_email);
        if (XDB::affectedRows() > 0) {
            $this->m_reactive = true;
            $mailer = new PlMailer();
            $mailer->setFrom('"Association Polytechnique.org" <register@' . $globals->mail->domain . '>');
            $mailer->addTo($email);
            $mailer->setSubject("Mise à jour de ton adresse {$email}");
            $mailer->setTxtBody(wordwrap("Cher Camarade,\n\n"
                    . "Ton adresse {$email} étant en panne et ayant été informés que ta redirection {$this->m_email}, jusqu'à présent inactive, "
                    . "est fonctionnelle, nous venons de réactiver cette adresse.\n\n"
                    . "N'hésite pas à aller gérer toi-même tes redirections en te rendant à la page :\n"
                    . "https://www.polytechnique.org/emails/redirect\n"
                    . "Si tu as perdu ton mot de passe d'accès au site, tu peux également effectuer la procédure de récupération à l'adresse :\n"
                    . "https://www.polytechnique.org/recovery\n\n"
                    . "-- \nTrès Cordialement,\nL'Équipe de Polytechnique.org\n"));
            $mailer->send();
            return true;
        }

        if ($this->m_user->email) {
            $subject = "Ton adresse $email semble ne plus fonctionner";
            $reason  = "Nous avons été informés que ton adresse $email ne fonctionne plus correctement par un camarade";
        } else {
            $res = XDB::iterRow("SELECT email FROM emails WHERE uid = {?} AND flags = 'panne'", $this->m_user->id());
            $redirect = array();
            while (list($red) = $res->next()) {
                list(, $redirect[]) = explode('@', $red);
            }
            $subject = "Ton adresse $email ne fonctionne plus";
            $reason  = "Ton adresse $email ne fonctionne plus";
            if (!count($redirect)) {
                $reason .= '.';
            } elseif (count($redirect) == 1) {
                $reason .= ' car sa redirection vers ' . $redirect[0] . ' est hors-service depuis plusieurs mois.';
            } else {
                $reason .= ' car ses redirections vers ' . implode(', ', $redirect)
                        . ' sont hors-services depuis plusieurs mois.';
            }
        }
        $body = ($this->m_user->isFemale() ? 'Chère ' : 'Cher ') . $this->m_user->displayName() . ",\n\n"
              . $reason . "\n\n"
              . "L'adresse {$this->m_email} nous a été communiquée, veux-tu que cette adresse devienne ta nouvelle "
              . "adresse de redirection ? Si oui, envoie nous des informations qui "
              . "nous permettront de nous assurer de ton identité (par exemple ta date de naissance et ta promotion).\n\n"
              . "-- \nTrès Cordialement,\nL'Équipe de Polytechnique.org\n";
        $body = wordwrap($body, 78);
        $mailer = new PlMailer();
        $mailer->setFrom('"Association Polytechnique.org" <register@' . $globals->mail->domain . '>');
        $mailer->addTo($this->m_email);
        $mailer->setSubject($subject);
        $mailer->setTxtBody($body);
        return $mailer->send();
    }

    // }}}
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
