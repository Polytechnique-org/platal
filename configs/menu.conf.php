<?php

if(logged()) {
    $menu = Array(
        0 => Array( 'Page d\'accueil' => 'login.php' ),
        'Personnaliser' => Array(
            'Mes emails' => 'emails.php' ,
            'Mon profil' => 'profil.php' ,
            'Mes contacts' => 'mescontacts.php' ,
            'Mon mot de passe' => 'motdepassemd5.php' ,
            'Mes préférences' => 'preferences.php' 
        ),
        'Services' => Array (
            'Envoyer un mail' => '###url###' ,
            'Forums &amp; PA' => '###url###' ,
            'Listes de diffusion' => '###url###' ,
            'Micropaiements' => '###url###' ,
            'Patte cassée' => 'pattecassee.php' ,
            'Sondages' => '###url###'
        ),
        'Communauté X' => Array (
            'Annuaire' => '###url###',
            'Trombi promo' => '###url###',
            'Groupes X' => '###url###',
            'Sites Polytechniciens' => '###url###'
        ),
        'Informations' => Array (
            'Lettres mensuelles' => '###url###',
            'Documentations' => 'docs/',
            'Nous contacter ' => "docs/contacts.php",
            'Emploi' => '###url###'
        )
    );

    if(identified()) {
        $menu[0] = array_merge( Array('Déconnexion' => 'deconnexion.php'),  $menu[0] );
    }

    if(has_perms()) {
        $menu['***'] = Array (
            'Marketing' => '###url###',
            'Administration' => '###url###',
            'Clear cache' => 'clear_all_cache.php'
        );
    }
} else {
    $menu = Array(
        'Polytechniciens' => Array(
            'Me connecter !' => "login.php",
            'M\'inscrire' => "###url###"
        ),
        'Visiteurs' => Array(
            'Annuaire de l\'X' => "###url###",
            'Associations X' => "###url###",
            'Recrutement' => "###url###"
        ),
        'Informations' => Array(
            'A propos du site' => "docs/apropos.php",
            'Nous contacter ' => "docs/contacts.php",
            'FAQ' => "###url###"
        )
    );
}
$this->assign_by_ref('menu', $menu);
?>
