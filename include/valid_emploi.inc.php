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

    function echo_formu() {
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <input type="hidden" name="uid" value="<?php echo $this->uid; ?>" />
    <input type="hidden" name="type" value="<?php echo $this->type; ?>" />
    <input type="hidden" name="stamp" value="<?php echo $this->stamp; ?>" />
    <table class="bicol" align="center" cellpadding="4" summary="Annonce emploi">
        <thead>
            <tr>
                <th colspan="2">
                    Offre d'emploi
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Demandeur
                </td>
                <td>
                    <?php echo htmlentities($this->entreprise).
                    " (".htmlentities($this->mail).")";?>
                </td>
            </tr>
            <tr>
                <td>
                    Titre du post
                </td>
                <td>
                    <?php echo htmlentities($this->titre);?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <pre><?php echo $this->text;?></pre>
                </td>
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
<?php
    }

    function handle_formu() {
        if (isset($_POST['submit'])) {
            require("mailer.inc.php");
            $mymail = new mailer('Equipe Polytechnique.org '
                .'<validation+recrutement@polytechnique.org>', 
                $this->mail,
                "[Polytechnique.org/EMPLOI] Annonce emploi : ".$this->entreprise,
                false, "validation+recrutement@m4x.org");

            $message =
                "Bonjour,\n".
                "\n";

            if($_REQUEST['submit']=="Accepter") {
                require("nntp.inc.php");
                require("poster.inc.php");
                $post = new poster(
                  "Annonces recrutement <recrutement@polytechnique.org>", 
                  "xorg.pa.emploi", 
                  "[OFFRE PUBLIQUE] {$this->entreprise} : {$this->titre}");
# Ca c'est pour faire les tests (xorg.test)
#                $post = new poster(
#                  "Tests annonces recrutement <support@polytechnique.org>", 
#                  "xorg.test", 
#                  "[TEST PUBLIC] {$this->entreprise} : {$this->titre}");
                $post->setbody($this->text
                 ."\n\n\n"
                 ."#############################################################################\n"
                 ." Ce forum n'est pas accessible à l'entreprise qui a proposé  cette  annonce.\n"
                 ." Pour  y  répondre,  utilise  les  coordonnées  mentionnées  dans  l'annonce\n"
                 ." elle-même.\n"
                 ."#############################################################################\n"
                 );
                $post->post();
                $message .= 
                "  L'annonce << {$this->titre} >> ".
                "a été acceptée par les modérateurs. Elle apparaîtra ".
                "dans le forum emploi du site\n\n".
                "Nous vous remercions d'avoir proposé cette annonce.";
            } else {
                $message .=
                "  L'annonce << {$this->titre} >> ".
                "a été refusée par les modérateurs.\n\n";
            }

            $message .=
                "\n".
                "Cordialement,\n".
                "L'équipe X.org";
            $message = wordwrap($message,78);  
            $mymail->setBody($message);
            $mymail->send();
            $this->clean();
        }
    }

    function commit() {
    }
}

?>
