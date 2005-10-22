<?php

$AuthFunction = 'AuthPlatal';

$Conditions['logged']      = logged();
$Conditions['identified']  = identified();
$Conditions['has_perms']   = has_perms();
$Conditions['public']      = 'true';
$Conditions['only_public'] = !identified();

function authPerms($pagename, $key, $could=false)
{
    $words = explode(' ', $key);
    $auth  = false;
    $and   = false;
    foreach ($words as $word) {
        $iauth = false;
        if ($word == 'and:') {
            $and = true;
            continue;
        }
        $parts = explode(':', $word);
        $cond = $parts[0];
        $param = $parts[1];
        if ($cond == 'identified' && $could) {
            $cond = 'logged';
        }
        $iauth = CondText($pagename, 'if '.$cond.' '.$param, true);
        if ($and) {
            $auth &= $iauth;
        } else {
            $auth |= $iauth;
        }
        $and = false;
    }
    return $auth;
}

function TryAllAuths($pagename, $level, $page_read, $group_read)
{
    global $DefaultPasswords;
    if (isset($page_read['passwd'.$level]) && $page_read['passwd'.$level] != '*') {
        return array('page', $page_read['passwd'.$level]);
    }
    if (isset($group_read['passwd'.$level]) && $group_read['passwd'.$level] != '*') {
        return array('group', $group_read['passwd'.$level]);
    }
    if (isset($DefaultPasswords[$level])) {
        return array('site', $DefaultPasswords[$level]);
    }
    return array('none', '');
}

function AuthPlatal($pagename, $level, $authprompt, $since)
{
    global $Conditions;
    $authUser = false;
    $authPage = false;

    $page_read  = ReadPage($pagename, $since);
    $groupattr  = FmtPageName('$Group/GroupAttributes', $pagename);
    $group_read = ReadPage($groupattr, $since);

    $levels = array('read', 'attr', 'edit', 'upload');

    foreach ($levels as $l)
    {
        list($from, $pass) = TryAllAuths($pagename, $l, $page_read, $group_read);
        $passwds[$l] = $pass;
        $pwsources[$l] = $from;
    }

    if (!isset($Conditions['canedit'])) {
        $Conditions['canedit'] = authPerms($pagename, $passwds['edit'], true);
    }
    if (!isset($Conditions['canattr'])) {
        $Conditions['canattr'] = authPerms($pagename, $passwds['attr'], true);
    }

    if (authPerms($pagename, $passwds[$level]))
    {
        $page_read['=passwd'] = $passwds;
        $page_read['=pwsource'] = $pwsources;
        return $page_read;
    }

    if ($authprompt && !identified())
    {
        new_skinned_page('wiki.tpl', AUTH_MDP); 
    }

    global $page;
    new_skinned_page('', AUTH_MDP); 
    if (has_perms()) {
        $page->trig('Erreur : page Wiki inutilisable sur plat/al');
    } else {
        $page->trig("Tu n'as pas le droit d'accéder à ce service");
    }
    // don't return false or pmwiki will send an exit breaking smarty page
    return 1;
}

?>
