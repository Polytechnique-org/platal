<?
$this->addPrivateEntry(XOM_NO,       10, 'Page d\'accueil',       'login.php');

$this->addPrivateEntry(XOM_CUSTOM,   00, 'Mes emails',            'emails.php');
$this->addPrivateEntry(XOM_CUSTOM,   10, 'Mon profil',            'profil.php');
$this->addPrivateEntry(XOM_CUSTOM,   20, 'Mes contacts',          'carnet/mescontacts.php');
$this->addPrivateEntry(XOM_CUSTOM,   30, 'Mon carnet',            'carnet/');
$this->addPrivateEntry(XOM_CUSTOM,   40, 'Mon mot de passe',      'motdepassemd5.php');
$this->addPrivateEntry(XOM_CUSTOM,   50, 'Mes préférences',       'preferences.php');

$this->addPrivateEntry(XOM_SERVICES, 00, 'Envoyer un mail',       'sendmail.php');
$this->addPrivateEntry(XOM_SERVICES, 10, 'Forums & PA',           'banana/');
$this->addPrivateEntry(XOM_SERVICES, 20, 'Listes de diffusion',   'listes/');
$this->addPrivateEntry(XOM_SERVICES, 30, 'Envoyer un mail',       'sendmail.php');
$this->addPrivateEntry(XOM_SERVICES, 40, 'Patte cassée',          'pattecassee.php');
       
$this->addPrivateEntry(XOM_GROUPS,   00, 'Annuaire',              'search.php');
$this->addPrivateEntry(XOM_GROUPS,   10, 'Trombi promo',          'trombipromo.php');
$this->addPrivateEntry(XOM_GROUPS,   20, 'Conseil Professionnel', 'referent.php');
$this->addPrivateEntry(XOM_GROUPS,   30, 'Groupes X',             'http://www.polytechnique.net/plan.php');
$this->addPrivateEntry(XOM_GROUPS,   40, 'Sites Polytechniciens', 'http://www.polytechnique.net/');

$this->addPrivateEntry(XOM_INFOS,    00, 'Lettres mensuelles',    'newsletter/');
$this->addPrivateEntry(XOM_INFOS,    10, 'Documentations',        'docs/');
$this->addPrivateEntry(XOM_INFOS,    20, 'Nous contacter',        'docs/contacts.php');
$this->addPrivateEntry(XOM_INFOS,    30, 'Emploi',                'http://www.manageurs.com/');

$this->addPrivateEntry(XOM_ADMIN,    00, 'Marketing',           'marketing/');
$this->addPrivateEntry(XOM_ADMIN,    10, 'Administration',      'admin/');
$this->addPrivateEntry(XOM_ADMIN,    20, 'Clear cache',         'clear_all_cache.php');

$this->addPublicEntry(XOM_US,    00, 'Me connecter !',         'login.php');
$this->addPublicEntry(XOM_US,    10, 'M\'inscrire',            'inscription/');
$this->addPublicEntry(XOM_US,    20, 'Pourquoi m\'inscrire ?', 'docs/services.php');

$this->addPublicEntry(XOM_EXT,   00, 'Annuaire de l\'X',       'search.php');
$this->addPublicEntry(XOM_EXT,   10, 'Associations X',         'http://www.polytechnique.net/');
$this->addPublicEntry(XOM_EXT,   20, 'Recrutement',            'http://www.manageurs.com/');

$this->addPublicEntry(XOM_INFOS, 00, 'A propos du site',       'docs/apropos.php');
$this->addPublicEntry(XOM_INFOS, 10, 'Nous contacter',         'docs/contacts.php');
$this->addPublicEntry(XOM_INFOS, 20, 'FAQ',                    'docs/faq.php');
?>
