<?
/*** Validation des alias **/

// Adresse d'alias telle qu'elle apparaîtra dans la base de données
function addr_alias($nomAlias) {
    global $globals ;
    return $nomAlias.'@'.$globals->domaine_mail_alias[0] ;
}

function from_mail_valid_alias() {
    global $globals ;
    return "Equipe Polytechnique.org <".$globals->addr_mail_valid_alias.">" ; 
}

function to_mail_valid_alias ($nomUser) {
    global $globals ;
    return $nomUser.'@'.$globals->domaine_mail ;
}

function subject_mail_valid_alias ($nomUser,$nomAlias) {
    global $globals ;
    return  "[Polytechnique.org/MELIX] Demande de l'alias ".
            addr_alias($nomAlias)." par $nomUser" ;
}

function cc_mail_valid_alias() {
    global $globals ;
    return $globals->addr_mail_valid_alias ;
}

function msg_valid_alias_OK ($nomAlias) {
    global $globals ;
    $msg =  "Cher(e) camarade,\n".
            "\n".
            "  Les adresses e-mail $nomAlias@melix.net et $nomAlias@melix.org que ".
            "tu avais demandées viennent d'être créées, tu peux désormais les ".
            "utiliser à ta convenance.\n".
            "\n".
            "Cordialement,\n".
            "L'équipe X.org" ;
    return $msg ;
}

function msg_valid_alias_NON ($nomAlias,$motif) {
    global $globals ;
    $msg =  "Cher(e) camarade,\n".
            "\n".
            "  La demande que tu avais faite pour les alias $nomAlias@melix.net et $nomAlias@melix.org ".
            "a été refusée pour la raison suivante :\n".
            $motif.
            "\n".
            "Cordialement,\n".
            "L'équipe X.org" ;
    return $msg ;
}

?>
