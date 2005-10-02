<?php

$AuthFunction = "AuthPlatal";

function authPerms($pagename,$key,$could=false)
{
 $words = explode(' ', $key);
 $auth = false;
 $and = false;
 foreach ($words as $word) {
  $iauth = false;
  if ($word == 'and:') { $and = true; continue; }
  $parts = explode(':', $word);
  $cond = $parts[0];
  $param = $parts[1];
  if ($cond == "identified" && $could)
   $cond = "logged";
  $iauth = CondText($pagename, "if ".$cond." ".$param, true);
  if ($and) $auth &= $iauth;
  else $auth |= $iauth;
  $and = false;
 }
 return $auth;
}

function TryAllAuths($pagename, $level, $page_read, $group_read, $could = false)
{
 global $DefaultPasswords;
 if (isset($page_read['passwd'.$level]) && $page_read['passwd'.$level] != '*')
  return authPerms($pagename,$page_read['passwd'.$level], $could);
 if (isset($group_read['passwd'.$level]) && $group_read['passwd'.$level] != '*')
  return authPerms($pagename,$group_read['passwd'.$level], $could);
 if (isset($DefaultPasswords[$level]))
  return authPerms($pagename,$DefaultPasswords[$level], $could);
 return false;
}

function AuthPlatal($pagename, $level, $authprompt, $since)
{
 global $Conditions;
 $authUser = false;
 $authPage = false;

 $page_read = ReadPage($pagename, $since);
 $groupattr = FmtPageName('$Group/GroupAttributes', $pagename);
 $group_read = ReadPage($groupattr, $since);

 if (!isset($Conditions['canedit']))
 $Conditions['canedit'] = TryAllAuths($pagename, 'edit', $page_read, $group_read, true);
 if (!isset($Conditions['canattr']))
 $Conditions['canattr'] = TryAllAuths($pagename, 'attr', $page_read, $group_read, true);

 if (TryAllAuths($pagename, $level, $page_read, $group_read))
 {
   return $page_read;
 }
   
 if ($authprompt && !identified())
 {
  new_skinned_page('wiki.tpl', AUTH_MDP); 
 }

 global $page;
 new_skinned_page('', AUTH_MDP); 
 if (has_perms())
  $page->trig("Erreur : page Wiki inutilisable sur plat/al");
 else
  $page->trig("Tu n'as pas le droit d'accéder à ce service");
 // don't return false or pmwiki will send an exit breaking smarty page
 return 1;
}

 $Conditions['logged'] = 'logged()';
 $Conditions['identified'] = 'identified()';
 $Conditions['has_perms'] = 'has_perms()';
 $Conditions['public'] = 'true';
 $Conditions['only_public'] = '!identified()';

