<?php

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
