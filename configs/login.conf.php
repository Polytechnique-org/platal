<?
/* Bandeau de publicité sur la page de login */
$pub_nbLig = 2 ;
$pub_nbCol = 2 ;

// Liens apparaissant toujours
$pub_tjs = array(
    "motdepassemd5.php" => "Changer mon mot de passe" ,
    "dons.php"          => "Faire un don à l'association Polytechnique.org"
    ) ;

// Liens apparaissant de façon aléatoire
$pub_rnd = array(
    "newsletter/show.php?nid=last"		    => "Afficher la dernière newsletter" ,
    "http://asso.polytechnique.org"		    => "Vers les autres sites polytechniciens" ,
    "trombipromo.php?xpromo={$_SESSION["promo"]}"   => "Voir le trombi de ma promo" ,
    "banana/"                                       => "Un petit tour du côté des forums !!"
    ) ;
?>
