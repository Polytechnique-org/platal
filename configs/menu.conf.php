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
            'Envoyer un mail' => 'sendmail.php' ,
            'Forums & PA' => 'banana/' ,
            'Listes de diffusion' => '###url###' ,
            'Micropaiements' => 'paiement/' ,
            'Patte cassée' => 'pattecassee.php' ,
        ),
        'Communauté X' => Array (
            'Annuaire' => 'search.php',
            'Trombi promo' => 'trombipromo.php',
            'Groupes X' => 'http://www.polytechnique.net/plan.php',
            'Sites Polytechniciens' => 'http://www.polytechnique.net/'
        ),
        'Informations' => Array (
            'Lettres mensuelles' => 'newsletter.php',
            'Documentations' => 'docs/',
            'Nous contacter ' => "docs/contacts.php",
            'Emploi' => 'http://www.manageurs.com/'
        )
    );

    if(identified()) {
        $menu[0] = array_merge( Array('Déconnexion' => 'deconnexion.php'),  $menu[0] );
    }

    if(has_perms()) {
        $menu['***'] = Array (
            'Marketing' => 'marketing/',
            'Administration' => 'admin/',
            'Clear cache' => 'clear_all_cache.php'
        );
    }
} else {
    $menu = Array(
        'Polytechniciens' => Array(
            'Me connecter !' => 'login.php',
            'M\'inscrire' => 'inscription/',
            'Pourquoi m\'inscrire ?' => 'docs/services.php'
        ),
        'Visiteurs' => Array(
            'Annuaire de l\'X' => 'search.php',
            'Associations X' => 'http://www.polytechnique.net/',
            'Recrutement' => 'http://www.manageurs.com/'
        ),
        'Informations' => Array(
            'A propos du site' => 'docs/apropos.php',
            'Nous contacter ' => 'docs/contacts.php',
            'FAQ' => 'docs/faq.php'
        )
    );
}
$this->assign_by_ref('menu', $menu);
?>
