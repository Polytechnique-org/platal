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

    function formu() {
        $ent = htmlentities($this->entreprise);
        $mail = htmlentities($this->mail);
        $titre = htmlentities($this->titre);
        $texte = wordwrap($this->text, 80);
        return <<<________EOF
        <form action="{$_SERVER['PHP_SELF']}" method="POST">
        <input type="hidden" name="uid" value="{$this->uid}" />
        <input type="hidden" name="type" value="{$this->type}" />
        <input type="hidden" name="stamp" value="{$this->stamp}" />
        <table class="bicol" cellpadding="4" summary="Annonce emploi">
          <thead>
          <tr>
            <th colspan="2">Offre d'emploi</th>
          </tr>
          </thead>
          <tbody>
            <tr>
              <td>Demandeur</td>
              <td>$ent ($mail)</td>
            </tr>
            <tr>
              <td>Titre du post</td>
              <td>$titre</td>
            </tr>
            <tr>
              <td colspan="2"><pre>{$texte}</pre></td>
            </tr>
            <tr>
              <td class="bouton" colspan="2">
                <input type="submit" name="submit" value="Accepter" />
                <input type="submit" name="submit" value="Refuser" />
              </td>
            </tr>
          </tbody>
        </table>
        </form>
________EOF;
    }

    function handle_formu() {
        if (isset($_POST['submit'])) {
            require("diogenes.mailer.inc.php");
            $mymail = new DiogenesMailer(
                from_mail_valid_emploi(),
                $this->mail,
                subject_mail_valid_emploi($this->entreprise),
                false,
                cc_mail_valid_emploi());

            if($_REQUEST['submit']=="Accepter") {
                require("nntp.inc.php");
                require("poster.inc.php");
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
                $message = msg_valid_emploi_OK( $this->titre ) ;
            } else {
                $message = msg_valid_emploi_NON( $this->titre ) ;
            }

            $message = wordwrap($message,78);  
            $mymail->setBody($message);
            $mymail->send();
            $this->clean();
            return "Mail envoyé";
        }
    }

    function commit() {
    }
}

?>
