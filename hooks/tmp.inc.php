<?php

function tmp_menu()
{
    global $globals;
    $globals->menu->addPrivateEntry(XOM_NO,       10, 'Page d\'accueil',       'login.php');

    $globals->menu->addPrivateEntry(XOM_CUSTOM,   10, 'Mon profil',            'profil.php');
    $globals->menu->addPrivateEntry(XOM_CUSTOM,   20, 'Mes contacts',          'carnet/mescontacts.php');
    $globals->menu->addPrivateEntry(XOM_CUSTOM,   30, 'Mon carnet',            'carnet/');
    $globals->menu->addPrivateEntry(XOM_CUSTOM,   40, 'Mon mot de passe',      'motdepassemd5.php');
    $globals->menu->addPrivateEntry(XOM_CUSTOM,   50, 'Mes préférences',       'preferences.php');

    $globals->menu->addPrivateEntry(XOM_GROUPS,   10, 'Trombi promo',          'trombipromo.php');
    $globals->menu->addPrivateEntry(XOM_GROUPS,   20, 'Conseil Pro.',          'referent.php');
    $globals->menu->addPrivateEntry(XOM_GROUPS,   30, 'Groupes X',             'http://www.polytechnique.net/plan.php');
    $globals->menu->addPrivateEntry(XOM_GROUPS,   40, 'Web Polytechnicien',    'http://www.polytechnique.net/');

    $globals->menu->addPrivateEntry(XOM_INFOS,    10, 'Documentations',        'docs/');
    $globals->menu->addPrivateEntry(XOM_INFOS,    20, 'Nous contacter',        'docs/contacts.php');
    $globals->menu->addPrivateEntry(XOM_INFOS,    30, 'Emploi',                'http://www.manageurs.com/');

    $globals->menu->addPrivateEntry(XOM_ADMIN,    00, 'Marketing',           'marketing/');
    $globals->menu->addPrivateEntry(XOM_ADMIN,    10, 'Administration',      'admin/');
    $globals->menu->addPrivateEntry(XOM_ADMIN,    20, 'Clear cache',         'clear_all_cache.php');

    $globals->menu->addPublicEntry(XOM_US,    00, 'Me connecter !',         'login.php');
    $globals->menu->addPublicEntry(XOM_US,    10, 'M\'inscrire',            'register/');
    $globals->menu->addPublicEntry(XOM_US,    20, 'Pourquoi m\'inscrire ?', 'docs/services.php');

    $globals->menu->addPublicEntry(XOM_EXT,   10, 'Associations X',         'http://www.polytechnique.net/');
    $globals->menu->addPublicEntry(XOM_EXT,   20, 'Recrutement',            'http://www.manageurs.com/');

    $globals->menu->addPublicEntry(XOM_INFOS, 00, 'A propos du site',       'docs/apropos.php');
    $globals->menu->addPublicEntry(XOM_INFOS, 10, 'Nous contacter',         'docs/contacts.php');
    $globals->menu->addPublicEntry(XOM_INFOS, 20, 'FAQ',                    'docs/faq.php');
}

// {{{ subscribe HOOK

function tmp_subscribe($forlife, $uid, $promo, $password)
{

    require_once('notifs.inc.php');
    register_watch_op($uid, WATCH_INSCR);
    inscription_notifs_base($uid);
}

// }}}
// {{{ prfs hook

function tmp_prefs()
{
    $fmt  = Session::get('mail_fmt', 'html') == 'html' ? 'texte' : 'html';
    $fmt2 = Session::get('mail_fmt', 'html') == 'html' ? 'texte' : 'HTML';
    return Array(
            Array(
                'url'    => 'preferences.php?mail_fmt='.$fmt,
                'title'  => 'Recevoir les mails en format '.$fmt2,
                'text'   => 'Tu recois les mails envoyés par le site (lettre mensuelle, carnet, ...) de préférence <strong>sous forme de '
                            .Session::get('mail_fmt', 'html').'</strong>',
                'weight' => 30
            ),
            Array(
                'url'    => 'preferences.php?rss='.(intval(Session::get('core_rss_hash')=='')),
                'title'  => (Session::get('core_rss_hash') ? 'désactiver' : 'activer').' les fils rss',
                'text'   => 'Ceci te permet d\'utiliser les fils rss du site. Attention, désactiver puis réactiver les fils en change les URL !',
                'weight' => 40
            )
        );
}

// }}}
?>
