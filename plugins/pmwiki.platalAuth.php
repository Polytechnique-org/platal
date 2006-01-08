<?php

$AuthFunction = 'AuthPlatal';

$Conditions['logged']      = logged();
$Conditions['identified']  = identified();
$Conditions['has_perms']   = has_perms();
$Conditions['public']      = 'true';
$Conditions['only_public'] = !identified();

$HandleAuth['diff'] = 'edit';
$HandleAuth['source'] = 'edit';

$InputTags['e_textarea'][':html'] =
 "<textarea \$InputFormArgs 
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
        $cond = $parts[0];
        $param = $parts[1];
        if ($cond == 'identified' && $could) {
            $cond = 'logged';
        }
	if ($smarty)
	   $iauth = '$'.$cond.($param?(' eq "'.$param.'"'):'');
	else
	{
	   if (strpos($cond, "smarty.") === 0)
	   {
	      $vars = explode('.', $cond);
	      $iauth = false;
	      switch ($vars[1])
	      {
	        case 'session':$iauth = Session::get($vars[2]) == $param; break;
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
    if ($smarty)
    	$auth = "($auth) or \$wiki_admin";
    return $auth;
}

// try to find the best permission for a given page and a given level of auth
// in order: page > group > site
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

function auth_pmwiki_to_smarty($text, $pass)
{
    $ifc = authPerms("", $pass, false, true);
    if (!$ifc) return "";
    return "{if $ifc}\n".$text."{/if}";
}

// for read pages: will come only once so we have to be careful
// and translate any auth from the wiki to smarty auth
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

    $canedit = authPerms($pagename, $passwds['edit'], true, true);
    $canattr = authPerms($pagename, $passwds['attr'], true, true);
    $panel  = "{if ($canedit) or ($canattr)}\n";
    $panel .= ">>frame<<\n";
    $panel .= "[[{\$FullName} |Voir la page]]\\\\\n";
    $panel .= "{if ($canedit)}\n";
    $panel .= "[[{\$FullName}?action=edit |Editer]]\\\\\n";
    $panel .= "[[{\$FullName}?action=diff |Historique]]\\\\\n";
    $panel .= "[[{\$FullName}?action=upload |Upload]]\\\\\n";
    $panel .= "{/if}{if ($canattr)}\n";
    $panel .= "[[{\$FullName}?action=attr |Droits]]\\\\\n";
    $panel .= "[[{\$Group}/GroupAttributes?action=attr|Droits du groupe]]\\\\\n";
    $panel .= "{/if}\n";
    $panel .= ">><<\n";
    $panel .= "{/if}\n";
  
    if ((identified() && has_perms()) || authPerms($pagename, $passwds[$level]))
    {
        $page_read['=passwd'] = $passwds;
        $page_read['=pwsource'] = $pwsources;
	// if try to read, add the permission limitation as a smarty if tag
	if ($level == 'read')
	{
	  $page_read['text'] = auth_pmwiki_to_smarty($page_read['text'], $passwds['read']);
	  $page_read['text'] = $panel.$page_read['text'];
	}
//	print_r($page_read); die();
        return $page_read;
    }

    // if we arrive here, the user doesn't have enough permission to access page

    // maybe it is because he is not identified
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
    $page->run();
    // don't return false or pmwiki will send an exit breaking smarty page
    return 1;
}

?>
