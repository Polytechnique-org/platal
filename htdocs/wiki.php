<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/

require_once("xorg.inc.php");

// this page is to create a smarty template page from a wiki file
// the wiki engine used is pmwiki.
// the templates created are stored in wiki.d/cache_wikiword.tpl

// some page can be seen by everybody (public), but to validate a password
// if we arrive here before setting new access we need to try an auth
new_skinned_page('wiki.tpl', Env::has('response') ? AUTH_MDP : AUTH_PUBLIC);

function assign_auth()
{
    global $page;
    $page->assign('logged', logged());
    $page->assign('identified', identified());
    $page->assign('has_perms', has_perms());
    $page->assign('public', true);
    $page->assign('wiki_admin', has_perms() && identified());
}

if ($globals->wiki->wikidir) {
	// the wiki keword is given in the n var
	if ($n = Env::get('n', false))
	{
	  // Get the correcti wiki keywords
	  $n = str_replace('/', '.', $n);
	  $keywords = explode('.', $n);
	  $count = count($keywords);
	  if ($count == 1)
	    $n = $keywords[0].".".$keywords[0];
	  else
	    $n = $keywords[$count - 2].".".$keywords[$count - 1];
	  if (($urln = str_replace('.', '/', $n)) != Env::get('n') &&
	  	$n != Env::get('n'))
	  {
	    header("Location: ".$globals->baseurl.'/'.$urln);
	    die();
	  }
	  $_REQUEST['n'] = $n;
	    
	  $dir_wiki_tmp = '../spool/wiki.d/';
	  $tpl_name = 'cache_'.$n.'.tpl';
	  $short_tpl = $dir_wiki_tmp.$tpl_name;
	  $dir_tpl = $globals->spoolroot.'templates/'.$dir_wiki_tmp;
	  $tpl = $globals->spoolroot.'templates/'.$short_tpl;
	  $tmpfile_exists = file_exists($tpl);

	  // don't recreate the tpl if it already exists
	  if (Env::get('action') || !$tmpfile_exists)
	  {
	    if ($tmpfile_exists) {
	      unlink($tpl);
	      $templates_cache_dir = '../spool/templates_c/';
          $dh  = opendir($templates_cache_dir);
          while (false !== ($filename = readdir($dh))) if (strpos($filename, $tpl_name) !== false)
            unlink($templates_cache_dir.$filename);
	    }

	    // we leave pmwiki do whatever it wants and store everything
	    ob_start();
	    require_once(dirname(dirname(__FILE__)).'/'.$globals->wiki->wikidir.'/pmwiki.php');

	    $wikiAll = ob_get_clean();
	    // the pmwiki skin we are using (almost empty) has these keywords:
	    $i = strpos($wikiAll, "<!--/HeaderText-->");
	    $j = strpos($wikiAll, "<!--/PageLeftFmt-->", $i);
	    
	  }
	  if (Env::get('action'))
	  {
	    // clean old tmp files (more than one hour)
	    $dh = opendir($dir_wiki_tmp);
	    $time = time();
	    while (($file = readdir($dh)) !== false)
	    {
	      if (strpos($file, 'temp_') === 0)
	      {
		$created = filectime($dir_wiki_tmp.$file);
		if ($time-$created > 60 * 60)
	          unlink($dir_wiki_tmp.$file);
	      }
	    }
	  
	    $page->assign('xorg_extra_header', substr($wikiAll, 0, $i));
	    $tmp_tpl = tempnam($dir_tpl, "temp_");
	    $f = fopen($tmp_tpl, 'w');
	    fputs($f, substr($wikiAll, $j));
	    fclose($f);
	    new_skinned_page($tmp_tpl, AUTH_PUBLIC);
	  } else {
	    if (!$tmpfile_exists)
	    {
	    	$f = fopen($tpl, 'w');
        	fputs($f, substr($wikiAll, $j));
        	fclose($f);
	    }
	    new_skinned_page($short_tpl, AUTH_PUBLIC);
	  } 
	}
}

$page->assign('xorg_extra_header', "<script type='text/JavaScript'>\n<!--\nNix={map:null,convert:function(a){Nix.init();var s='';for(i=0;i<a.length;i++){var b=a.charAt(i);s+=((b>='A'&&b<='Z')||(b>='a'&&b<='z')?Nix.map[b]:b);}return s;},init:function(){if(Nix.map!=null)return;var map=new Array();var s='abcdefghijklmnopqrstuvwxyz';for(i=0;i<s.length;i++)map[s.charAt(i)]=s.charAt((i+13)%26);for(i=0;i<s.length;i++)map[s.charAt(i).toUpperCase()]=s.charAt((i+13)%26).toUpperCase();Nix.map=map;},decode:function(a){document.write(Nix.convert(a));}}\n//-->\n</script>\n");
assign_auth();
$page->addCssLink('css/wiki.css');

$page->run();
?>
