<?php

if(logged()) {
    $menu = Array(
        0 => Array( 'Page d\'accueil' => '###url###' ),
        'Personnaliser' => Array(
            'Mes emails' => '###url###' ,
            'Mon profil' => '###url###' ,
            'Mes contacts' => '###url###' ,
            'Mon mot de passe' => '###url###' ,
            'Mes préférences' => '###url###' 
        ),
        'Services' => Array (
            'Envoyer un mail' => '###url###' ,
            'Forums & PA' => '###url###' ,
            'Listes de diffusion' => '###url###' ,
            'Micropaiements' => '###url###' ,
            'Patte cassée' => '###url###' ,
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
            'Documentation' => '###url###',
            'Nous contacter' => '###url###',
            'Emploi' => '###url###'
        )
    );

    if(identified()) {
        $array = array_merge( Array('Déconnexion' => '###url###'),  $menu[0] );
    }

    if(has_perms()) {
        $menu['***'] = Array (
            'Marketing' => '###url###',
            'Administration' => '###url###',
            'Trackers' => '###url###',
            'Documentations' => '###url###'
        );
    } elseif(has_perms($marketing_admin)) {
        $menu['***'] = Array (
            'Marketing' => '###url###'
        );
    }
} else {
    $menu = Array(
        'Polytechniciens' => Array(
            'Me connecter !' => "###url###",
            'M\'inscrire' => "###url###"
        ),
        'Visiteurs' => Array(
            'Annuaire de l\'X' => "###url###",
            'Associations X' => "###url###",
            'Recrutement' => "###url###"
        ),
        'Informations' => Array(
            'A propos du site' => "###url###",
            'Nous contacter ' => "###url###",
            'FAQ' => "###url###"
        )
    );
}
$this->assign_by_ref('menu', $menu);
?>
