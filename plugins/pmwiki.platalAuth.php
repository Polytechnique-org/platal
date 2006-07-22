<?php

$AuthFunction = 'AuthPlatal';

$Conditions['logged']      = S::logged();
$Conditions['identified']  = S::identified();
$Conditions['has_perms']   = S::has_perms();
$Conditions['public']      = 'true';

$HandleAuth['diff']        = 'edit';
$HandleAuth['source']      = 'edit';

$InputTags['e_textarea'][':html'] = "<textarea \$InputFormArgs
     onkeydown='if (event.keyCode==27) event.returnValue=false;'
    >{literal}\$EditText{/literal}</textarea>";

// impossible to see the diff without the source because of the smarty tags
$DiffShow['source'] = 'y';
$DiffSourceFmt = '';

$DiffStartFmt = "{literal}<div class='diffbox'><div class='difftime'>\$DiffTime \$[by] <span class='diffauthor' title='\$DiffHost'>\$DiffAuthor</span></div>";
$DiffEndFmt = "</div>{/literal}";

// compute permissions based on the permission string (key)
// if could is true, compute permission that could be enabled with a mdp
// if smarty is true, return a string to insert in a smarty if tag
// otherwise return true or false
function authPerms($pagename, $key, $could=false, $smarty=false)
{
    $words = explode(' ', $key);
    $auth  = $smarty?"":false;
    $and   = false;
    foreach ($words as $word) {
        if (strpos($word, '@') === 0) $word = substr($word,1);
        $iauth = false;
        if ($word == 'and:') {
            $and = true;
            continue;
        }
        $parts = explode(':', $word);
        $cond  = $parts[0];
        $param = $parts[1];
        if ($cond == 'identified' && $could) {
            $cond = 'logged';
        }
        if ($smarty) {
            $iauth = '$'.$cond.($param?(' eq "'.$param.'"'):'');
        } else {
            if (strpos($cond, "smarty.") === 0) {
                $vars = explode('.', $cond);
                $iauth = false;
                switch ($vars[1])
                {
                  case 'session':$iauth = S::v($vars[2]) == $param; break;
                  case 'request':$iauth = Env::get($vars[2]) == $param; break;
                }
            }
            else $iauth = CondText($pagename, 'if '.$cond.' '.$param, true);
        }

        if ($and) {
            if ($smarty)
                $auth = ($auth?"($auth) and ":"").$iauth;
            else
                $auth &= $iauth;
        } else {
            if ($smarty)
                $auth = ($auth?"($auth) or ":"").$iauth;
            else
                $auth |= $iauth;
        }
        $and = false;
    }
    return $auth;
}

// try to find the best permission for a given page and a given level of auth
// in order: page > site
function TryAllAuths($pagename, $level, $page_read)
{
    global $DefaultPasswords;
    if (isset($page_read['passwd'.$level]) && $page_read['passwd'.$level] != '*') {
        return array('page', $page_read['passwd'.$level]);
    }
    if (isset($DefaultPasswords[$level])) {
        return array('site', $DefaultPasswords[$level]);
    }
    return array('none', '');
}

function auth_pmwiki_to_smarty($text, $pass)
{
    $ifc = authPerms("", $pass, false, true);
    if (!$ifc)
        return "";
    return "{if $ifc}\n".$text."\n{else}(:div class='erreur':Droits insuffisants.:){/if}";
}

// for read pages: will come only once so we have to be careful
// and translate any auth from the wiki to smarty auth
function AuthPlatal($pagename, $level, $authprompt)
{
    global $Conditions, $page;

    $page_read = ReadPage($pagename);

    $levels = array('read', 'attr', 'edit', 'upload');

    foreach ($levels as $l) {
        list($from, $pass) = TryAllAuths($pagename, $l, $page_read);
        $passwds[$l]   = $pass;
        $pwsources[$l] = $from;
    }

    $canedit = authPerms($pagename, $passwds['edit'], true, true);
    $canattr = authPerms($pagename, $passwds['attr'], true, true);
    $panel  = "{if ($canedit) or ($canattr)}\n";
    $panel .= ">>frame<<\n";
    $panel .= "[[{\$FullName}|Voir la page]]";
    $panel .= "{if ($canedit)}\n";
    $panel .= "[[{\$FullName}?action=edit |Editer]]";
    $panel .= "[[{\$FullName}?action=diff |Historique]]";
    $panel .= "[[{\$FullName}?action=upload |Upload]]";
    $panel .= "{/if}{if ($canattr)}\n";
    $panel .= "[[{\$FullName}?action=attr |Droits]]";
    $panel .= "{/if}\n";
    $panel .= "\\\\\n\n";
    $panel .= ">><<\n";
    $panel .= "{/if}\n";

    if ((S::identified() && S::has_perms()) || authPerms($pagename, $passwds[$level]))
    {
        $page_read['=passwd']   = $passwds;
        $page_read['=pwsource'] = $pwsources;

        // if try to read, add the permission limitation as a smarty if tag
        if ($level == 'read') {
            $page_read['text'] = auth_pmwiki_to_smarty($page_read['text'], $passwds['read']);
            $page_read['text'] = $panel.$page_read['text'];
        }

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
