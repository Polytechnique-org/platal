<?
/*** Validation des offres d'emploi ***/

function from_mail_valid_emploi() {
    global $globals ;
    return "Equipe Polytechnique.org <".$globals->addr_mail_valid_emploi.">" ; 
}

function subject_mail_valid_emploi ($nomEntreprise) {
    global $globals ;
    return "[Polytechnique.org/EMPLOI] Annonce emploi : ".$nomEntreprise ;
}

function cc_mail_valid_emploi() {
    global $globals ;
    return $globals->addr_mail_valid_emploi ;
}

function msg_valid_emploi_OK ($titre) {
    $msg =  "Bonjour,\n".
            "\n".
            "L'annonce << {$titre} >> ".
            "a été acceptée par les modérateurs. Elle apparaîtra ".
            "dans le forum emploi du site\n\n".
            "Nous vous remercions d'avoir proposé cette annonce.\n";
            "\n".
            "Cordialement,\n".
            "L'équipe X.org" ;
    return $msg ;
}

function msg_valid_emploi_NON ($titre) {
    $msg =  "Bonjour,\n".
            "\n".
            "L'annonce << {$titre} >> ".
            "a été refusée par les modérateurs.\n".
            "\n".
            "Cordialement,\n".
            "L'équipe X.org" ;
    return $msg ;
}

function from_post_emploi() {
    global $globals ;
    return "Annonce recrutement <".$globals->addr_mail_recrutement.">" ;
}

function to_post_emploi() {
    return "xorg.pa.emploi" ;
}

function subject_post_emploi( $annonceEmploi ) {
    return "[OFFRE PUBLIQUE] ".$annonceEmploi->entreprise." : ".$annonceEmploi->titre ;
}

function msg_post_emploi( $annonceEmploi ) {
    return  $annonceEmploi->text.
            "\n\n\n".
            "#############################################################################\n".
            " Ce forum n'est pas accessible à l'entreprise qui a proposé  cette  annonce.\n".
            " Pour  y  répondre,  utilise  les  coordonnées  mentionnées  dans  l'annonce\n".
            " elle-même.\n".
            "#############################################################################\n" ;
}

function from_post_emploi_test() {
    global $globals ;
    return "Tests annonces recrutement <".$globals->addr_mail_supprt.">" ;
}

function to_post_emploi_test() {
    return "xorg.test" ;
}

function subject_post_emploi_test( $annonceEmploi) {
    return "[TEST PUBLIC] ".$annonceEmploi->entreprise." : ".$annonceEmploi->titre ;
}


?>
