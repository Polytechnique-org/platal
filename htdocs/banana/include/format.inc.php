<?php
/********************************************************************************
* install.d/format.inc.php : HTML output subroutines
* --------------------------
*
* This file is part of the banana distribution
* Copyright: See COPYING files that comes with this distribution
********************************************************************************/

function url($string)
{
    if(strpos($string, "http://")!==false)
	return $string;
    $chemins = Array('', '../', '../../');
    foreach ($chemins as $ch) {
	if (file_exists($ch.'../htdocs/')) {
	    return $ch.$string;
	}
    }
    return '';
}

/** produces HTML output for overview
 * @param $_header STRING name of the header
 * @param $_text STRING value of the header
 * @param $_id INTEGER MSGNUM of message
 * @param $_group TEXT name of newsgroup
 * @param $_isref BOOLEAN emphasizes message in overview tree ?
 * @param $_isread BOOLEAN displays message as read ?
 * @return STRING HTML output
 * @see disp_desc
 */

function formatSpoolHeader($_header,$_text,$_id,$_group,$_isref,$_isread=true) {
  global $locale;
  switch ($_header) {
    case "date": 
      return locale_header_date($_text);
    case "from":
#     From: mark@cbosgd.ATT.COM
#     From: mark@cbosgd.ATT.COM (Mark Horton)
#     From: Mark Horton <mark@cbosgd.ATT.COM>
      $result = htmlentities($_text);
      if (preg_match("/^([^ ]+)@([^ ]+)$/",$_text,$regs))
        $result="<a href=\"&#109;&#97;&#105;&#108;&#116;&#111;&#58;".
          "{$regs[1]}&#64;{$regs[2]}\">".htmlentities($regs[1].
          "&#64;".$regs[2])."</a>";
      if (preg_match("/^([^ ]+)@([^ ]+) \((.*)\)$/",$_text,$regs))
        $result="<a href=\"&#109;&#97;&#105;&#108;&#116;&#111;&#58;".
          "{$regs[1]}&#64;{$regs[2]}\">".htmlentities($regs[3])."</a>";
      if (preg_match("/^\"?([^<>\"]+)\"? +<(.+)@(.+)>$/",$_text,$regs))
        $result="<a href=\"&#109;&#97;&#105;&#108;&#116;&#111;&#58;".
          "{$regs[2]}&#64;{$regs[3]}\">".htmlentities($regs[1])."</a>";
      return preg_replace("/\\\(\(|\))/","\\1",$result);
    case "subject":
      if ($_isref) {
        return '<span class="isref">'.htmlentities($_text).'</span>';
      } else {
        if ($_isread) {
          return "<a href=\"article.php?group=$_group&amp;id=$_id\">"
          .htmlentities($_text)."</a>";
        }else {
          return "<a href=\"article.php?group=$_group&amp;id=$_id\"><b>"
          .htmlentities($_text)."</b></a>";
        }
      }
    default:
      return htmlentities($_text);
  }
}

/** produces HTML ouput for header section in post.php
 * @param $_header STRING name of the header
 * @param $_text STRING value of the header
 * @param $_spool OBJECT spool object for building references
 * @return STRING HTML output
 */

function formatDisplayHeader($_header,$_text,$_spool) {
  global $locale;
  switch ($_header) {
    case "date": 
      return locale_date($_text);
    case "followup":
    case "newsgroups":
      $res = "";
      $groups = preg_split("/(\t| )*,(\t| )*/",$_text);
      foreach ($groups as $g) {
        $res.='<a href="thread.php?group='.$g.'">'.$g.'</a>, ';
      }
      return substr($res,0, -2);
    case "from":
#     From: mark@cbosgd.ATT.COM
#     From: mark@cbosgd.ATT.COM (Mark Horton)
#     From: Mark Horton <mark@cbosgd.ATT.COM>
#     From: Anonymous <anonymous>
      $result = htmlentities($_text);
      if (preg_match("/^([^ ]+)@([^ ]+)$/",$_text,$regs))
        $result="<a href=\"&#109;&#97;&#105;&#108;&#116;&#111;&#58;"
          ."{$regs[1]}&#64;{$regs[2]}\">".htmlentities($regs[1])
          ."&#64;{$regs[2]}</a>";
      if (preg_match("/^([^ ]+)@([^ ]+) \((.*)\)$/",$_text,$regs))
        $result="<a href=\"&#109;&#97;&#105;&#108;&#116;&#111;&#58;"
          ."{$regs[1]}&#64;{$regs[2]}\">".htmlentities($regs[3])
          ."</a>";
      if (preg_match("/^\"?([^<>\"]+)\"? +<(.+)@(.+)>$/",$_text,$regs))
        $result="<a href=\"&#109;&#97;&#105;&#108;&#116;&#111;&#58;"
          ."{$regs[2]}&#64;{$regs[3]}\">".htmlentities($regs[1])
          ."</a>";
      return preg_replace("/\\\(\(|\))/","\\1",$result);
    case "references":
      $rsl = "";
      $ndx = 1;
      $text=str_replace("><","> <",$_text);
      $text=preg_split("/( |\t)/",strtr($text,$_spool->ids));
      $parents=preg_grep("/^\d+$/",$text);
      $p=array_pop($parents);
      while ($p) {
        $valid_parents[]=$p;
        $p = $_spool->overview[$p]->parent;
      }
      foreach (array_reverse($valid_parents) as $p) {
        $rsl .= "<a href=\"article.php?group={$_spool->group}"
          ."&amp;id=$p\">$ndx</a> ";
        $ndx++;
      }
      return $rsl;
    case "xface":
      return '<img src="xface.php?face='.base64_encode($_text)
      .'"  alt="x-face" />';
    case "xorgid":
      return "$_text".(preg_match("/[\w]+\.[\w\d]+/",$_text)?
        " [<a href=\"javascript:x()\"  onclick=\"popWin('"
        .url("fiche.php")."?user=$_text')\">fiche</a>]":"");
    default:
      return htmlentities($_text);
  }
}

/** produces HTML output for message body
 * @param $_text STRING message body
 * @return STRING HTML output
 */
function formatbody($_text) {
  global $news;
  $res ="\n\n";
  $res .= htmlentities(wrap($_text,"",$news['wrap']))."\n";
  $res = preg_replace("/(&lt;|&gt;|&quot;)/"," \\1 ",$res);
  $res = preg_replace('/(["\[])?((https?|ftp|news):\/\/[a-z@0-9.~%$£µ&i#\-+=_\/\?]*)(["\]])?/i',
    "\\1<a href=\"\\2\">\\2</a>\\4", $res);
  $res = preg_replace("/ (&lt;|&gt;|&quot;) /","\\1",$res);
  return $res."\n";
}

/** contextual links 
 * @return STRING HTML output
 */
function displayshortcuts() {
  global $news,$locale,$first,$spool,$group,$post,$id,$profile,$css;
  $sname = $_SERVER['SCRIPT_NAME'];
  $array = explode('/',$sname);
  $sname = array_pop($array);

  echo "<div class=\"{$css['bananashortcuts']}\">";

  switch ($sname) {
    case 'subscribe.php' :
      echo '[<a href="index.php">Liste des forums</a>] ';
      echo '[<a href="'.url("confbanana.php").'">Profil</a>] ';
      break;
    case 'index.php' :
      if (!$profile['autoup']) { 
        echo '[<a href="index.php?banana=updateall">Mettre à jour</a>] ';
      }
      echo '[<a href="'.url("confbanana.php").'">Profil</a>] ';
      echo '[<a href="subscribe.php">Abonnements</a>] ';
      break;
    case 'thread.php' :
      if (!$profile['autoup']) { 
        echo '[<a href="index.php?banana=updateall">Mettre à jour</a>] ';
      }
      echo '[<a href="'.url("confbanana.php").'">Profil</a>] ';
      echo '[<a href="index.php">'.$locale['format']['grouplist']
        .'</a>] ';
      echo "[<a href=\"post.php?group=$group\">"
        .$locale['format']['newpost']."</a>] ";
      if (sizeof($spool->overview)>$news['max']) {
        for ($ndx=1; $ndx<=sizeof($spool->overview); $ndx += $news['max']) {
          if ($first==$ndx) {
            echo "[$ndx-".min($ndx+$news['max']-1,sizeof($spool->overview))."] ";
          } else {
            echo "[<a href=\"".$_SERVER['PHP_SELF']."?group=$group&amp;first="
           ."$ndx\">$ndx-".min($ndx+$news['max']-1,sizeof($spool->overview))
            ."</a>] ";
          }
        }
      }
      break;
    case 'article.php' :
      if (!$profile['autoup']) { 
        echo '[<a href="index.php?banana=updateall">Mettre à jour</a>] ';
      }
      echo '[<a href="'.url("confbanana.php").'">Profil</a>] ';
      echo '[<a href="index.php">'.$locale['format']['grouplist']
        .'</a>] ';
      echo "[<a href=\"thread.php?group=$group\">"
        .$locale['format']['group_b'].$group
        .$locale['format']['group_a']."</a>] ";
      echo "[<a href=\"post.php?group=$group&amp;id=$id&amp;type=followup\">"
        .$locale['format']['followup']."</a>] ";
      if (checkcancel($post->headers)) {
        echo "[<a href=\"article.php?group=$group&amp;id=$id&amp;type=cancel\">"
        .$locale['format']['cancel']."</a>] ";
      }
      break;
    case 'post.php' :
      if (!$profile['autoup']) { 
        echo '[<a href="index.php?banana=updateall">Mettre à jour</a>] ';
      }
      echo '[<a href="'.url("confbanana.php").'">Profil</a>] ';
      echo '[<a href="index.php">'.$locale['format']['grouplist']
        .'</a>] ';
      echo "[<a href=\"thread.php?group=$group\">"
        .$locale['format']['group_b'].$group
        .$locale['format']['group_a']."</a>] ";
      break;
  }
  echo '</div>';
}

?>

