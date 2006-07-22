<?php

$AuthFunction = 'AuthPlatal';

$Conditions['logged']      = S::logged();
$Conditions['identified']  = S::identified();
$Conditions['has_perms']   = S::has_perms();
$Conditions['public']      = 'true';

$HandleAuth['diff']        = 'edit';
$HandleAuth['source']      = 'edit';

// impossible to see the diff without the source because of the smarty tags
$DiffShow['source'] = 'y';
$DiffSourceFmt = '';

// for read pages: will come only once so we have to be careful
// and translate any auth from the wiki to smarty auth
function AuthPlatal($pagename, $level, $authprompt)
{
    global $Conditions, $page;

    $page_read = ReadPage($pagename);

    $levels = array('read', 'attr', 'edit', 'upload');

    if (S::identified() && S::has_perms())
    {
        $page_read['=passwd']   = $passwds;
        $page_read['=pwsource'] = $pwsources;

        return $page_read;
    }

    // if we arrive here, the user doesn't have enough permission to access page

    // maybe it is because he is not identified
    if ($authprompt && !S::identified()) {
        require_once dirname(__FILE__).'/../classes/Platal.php';
        require_once dirname(__FILE__).'/../classes/PLModule.php';
        $platal = new Platal();
        $platal->force_login($page);
    }

    if (S::has_perms()) {
        $page->trig('Erreur : page Wiki inutilisable sur plat/al');
    }
    $page->run();
}

?>
