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
 ***************************************************************************
        $Id: valid_emploi.inc.php,v 1.10 2004-08-31 11:16:48 x2000habouzit Exp $
 ***************************************************************************/


class EmploiReq extends Validate {
    var $entreprise;
    var $titre;
    var $mail;
    var $text;

    function EmploiReq($_entreprise,$_titre,$_mail,$_text,$_stamp=0) {
        $this->Validate(0, false, 'emploi', $_stamp);
        $this->entreprise = $_entreprise;
        $this->titre = $_titre;
        $this->mail = $_mail;
        $this->text = wordwrap($_text,78);
    }

    function get_unique_request($uid) {
        return false; // non unique
    }

    function handle_formu() {
        if (isset($_POST['submit'])) {
            require("tpl.mailer.inc.php");
            $mymail = new TplMailer('valid.emploi.tpl');
            $mymail->assign('entreprise', $this->entreprise);
            $mymail->assign('titre', $this->titre);
            $mymail->_to = $this->mail;

            if($_REQUEST['submit']=="Accepter") {
                require("nntp.inc.php");   # FIXME
                require("poster.inc.php"); # FIXME : old includes
                $post = new poster(
                    from_post_emploi(),
                    to_post_emploi(),
                    subject_post_emploi($this)) ;
                    
# Ca c'est pour faire les tests (xorg.test)
#                $post = new poster(
#                   from_post_emploi_test(),
#                   to_post_emploi_test(),
#                   subject_post_emploi_test($this)) ;

                $post->setbody( msg_post_emploi($this) ) ;
                $post->post();
                $mymail->assign('answer','yes');
            } else {
                $mymail->assign('answer','no');
            }
            $mymail->send();
            $this->clean();
            return "Mail envoyé";
        }
    }

    function formu() { return 'include/form.valid.emploi.tpl'; }

    function commit() {
    }
}

?>
