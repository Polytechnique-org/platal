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


class MarkReq extends Validate
{
    // {{{ properties

    var $perso;

    var $m_id;
    var $m_email;
    var $m_nom;
    var $m_prenom;
    var $m_promo;
    var $m_relance;

    var $rules = "Accepter si l'adresse mail parait correcte, et pas absurde (ou si le marketeur est de confiance). Si le 
    demandeur marque sa propre adresse mail, refuser dans tous les cas.
    Ne pas marqueter au nom de Polytechnique.org plus d'une 
    fois par an.";
    // }}}
    // {{{ constructor

    function MarkReq($sender, $mark_id, $email, $perso = false) {
        $this->Validate($sender, false, 'marketing');
        $this->m_id    = $mark_id;
        $this->m_email = $email;
        $this->perso   = $perso;

        $res = XDB::query('SELECT  u.nom, u.prenom, u.promo,
                                   IF(MAX(m.last)>p.relance, MAX(m.last), p.relance)
                             FROM  auth_user_md5      AS u
                        LEFT JOIN  register_pending   AS p ON p.uid = u.user_id
                        LEFT JOIN  register_marketing AS m ON m.uid = u.user_id
                            WHERE  user_id = {?}
                         GROUP BY  u.user_id', $mark_id);
        list ($this->m_nom, $this->m_prenom, $this->m_promo, $this->m_relance) = $res->fetchOneRow(); 
    }

    // }}}
    // {{{ function formu()

    function formu()
    { return 'include/form.valid.mark.tpl'; }

    // }}}
    // {{{ function _mail_subj
    
    function _mail_subj()
    {
        return "[Polytechnique.org] Marketing de {$this->m_prenom} {$this->m_nom} ({$this->m_promo})";
    }

    // }}}
    // {{{ function _mail_body

    function _mail_body($isok)
    {
        if ($isok) {
            return "  Un mail de marketing vient d'être envoyé "
                .($this->perso ? 'en ton nom' : 'en notre nom')
                ." à {$this->m_prenom} {$this->m_nom} ({$this->m_promo}) pour l'encourager à s'inscrire !\n\n"
                ."Merci de ta participation !\n";
        } else {
            return "  Nous n'avons pas jugé bon d'envoyer de mail de marketing à {$this->m_prenom} {$this->m_nom} ({$this->m_promo}).";
        }
    }

    // }}}
    // {{{ function commit()

    function commit()
    {
        require_once('marketing.inc.php');
        mark_send_mail($this->m_id, $this->m_email,(!$this->perso)?"staff":"user");
        return true;
    }

    // }}}
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
