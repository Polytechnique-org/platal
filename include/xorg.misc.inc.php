<?php
/** vérifie si une adresse email (sans @) correspond à un username ou alias ou une liste...
 * @param $email l'adresse email a verifier
 * @return BOOL
 */
function isvalid_email_local($email) {
  // ATTENTION, les requêtes sur le username et l'alias ne doivent pas être faites
  // dans la même requête MySQL, car elles portent sur 2 index de la table avec un OR
  // et MySQL ne sait pas utiliser les index dans ce cas
  // (ce n'est plus vrai dans MySQL 4.x donc quand on y passera, on pourra
  // combiner les 2 requêtes suivantes en une seule)
  $req = $globals->db->query("select count(*) from auth_user_md5 where username='$email'");
  list($nb)=mysql_fetch_row($req);
  mysql_free_result($req);
  if ($nb>0) return true;

  $req = $globals->db->query("select count(*) from auth_user_md5 where alias='$email'");
  list($nb)=mysql_fetch_row($req);
  mysql_free_result($req);
  if ($nb>0) return true;

  // vérification des adresses types $liste et $liste-request
  // ATTENTION, il ne faut pas accepter les adresses types owner-$liste et sm-$liste
  $req = $globals->db->query("select count(*) from aliases where (alias='$email' and type='liste') or (alias='$email-request' and type='liste-request')");
  list($nb)=mysql_fetch_row($req);
  mysql_free_result($req);
  if ($nb>0) return true;

  // reste à vérifier si c'est pas une adresse dans /etc/aliases
  // surement possible en utilisant postmap -q $email hash:/etc/aliases

  return false;
}

/** vérifie si une adresse email convient comme adresse de redirection 
 * @param $email l'adresse email a verifier
 * @return BOOL
 */
function isvalid_email_redirection($email) {
  return isvalid_email($email) && 
         !preg_match("/@(polytechnique\.(org|edu)|melix\.(org|net)|m4x\.org)$/", $email);
}

/* Un soundex en français posté par Frédéric Bouchery
Voici une adaptation en PHP de la fonction soundex2 francisée de Frédéric BROUARD (http://sqlpro.developpez.com/Soundex/).
C'est une bonne démonstration de la force des expressions régulières compatible Perl.
trouvé sur http://expreg.com/voirsource.php?id=40&type=Chaines%20de%20caract%E8res */
function soundex_fr($sIn)
{ 
    // Si il n'y a pas de mot, on sort immédiatement 
    if ( $sIn === '' ) return '    '; 
    // On met tout en minuscule 
    $sIn = strtoupper( $sIn ); 
    // On supprime les accents 
    $sIn = strtr( $sIn, 'ÂÄÀÇÈÉÊË¼ÎÏÔÖÙÛÜ', 'AAASEEEEEIIOOUUU' ); 
    // On supprime tout ce qui n'est pas une lettre 
    $sIn = preg_replace( '`[^A-Z]`', '', $sIn ); 
    // Si la chaîne ne fait qu'un seul caractère, on sort avec. 
    if ( strlen( $sIn ) === 1 ) return $sIn . '   '; 
    // on remplace les consonnances primaires 
    $convIn = array( 'GUI', 'GUE', 'GA', 'GO', 'GU', 'CA', 'CO', 'CU', 'Q', 'CC', 'CK' ); 
    $convOut = array( 'KI', 'KE', 'KA', 'KO', 'K', 'KA', 'KO', 'KU', 'K', 'K', 'K' ); 
    $sIn = str_replace( $convIn, $convOut, $sIn ); 
    // on remplace les voyelles sauf le Y et sauf la première par A 
    $sIn = preg_replace( '`(?<!^)[EIOU]`', 'A', $sIn ); 
    // on remplace les préfixes puis on conserve la première lettre 
    // et on fait les remplacements complémentaires 
    $convIn = array( '`^KN`', '`^(PH|PF)`', '`^MAC`', '`^SCH`', '`^ASA`', '`(?<!^)KN`', '`(?<!^)(PH|PF)`', '`(?<!^)MAC`', '`(?<!^)SCH`', '`(?<!^)ASA`' ); 
    $convOut = array( 'NN', 'FF', 'MCC', 'SSS', 'AZA', 'NN', 'FF', 'MCC', 'SSS', 'AZA' ); 
    $sIn = preg_replace( $convIn, $convOut, $sIn ); 
    // suppression des H sauf CH ou SH 
    $sIn = preg_replace( '`(?<![CS])H`', '', $sIn ); 
    // suppression des Y sauf précédés d'un A 
    $sIn = preg_replace( '`(?<!A)Y`', '', $sIn ); 
    // on supprime les terminaisons A, T, D, S 
    $sIn = preg_replace( '`[ATDS]$`', '', $sIn ); 
    // suppression de tous les A sauf en tête 
    $sIn = preg_replace( '`(?!^)A`', '', $sIn ); 
    // on supprime les lettres répétitives 
    $sIn = preg_replace( '`(.)\1`', '$1', $sIn ); 
    // on ne retient que 4 caractères ou on complète avec des blancs 
    return substr( $sIn . '    ', 0, 4); 
} 
?>
