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

/** produces HTML ouput for header section in post.php
 * @param $_header STRING name of the header
 * @param $_text STRING value of the header
 * @param $_spool OBJECT spool object for building references
 * @return STRING HTML output
 */

function formatDisplayHeader($_header,$_text,$_spool) {
    switch ($_header) {
        case "date": 
            return formatDate($_text);

        case "followup":
        case "newsgroups":
            $res = "";
            $groups = preg_split("/(\t| )*,(\t| )*/",$_text);
            foreach ($groups as $g) {
                $res.='<a href="thread.php?group='.$g.'">'.$g.'</a>, ';
            }
            return substr($res,0, -2);

        case "from":
            return formatFrom($_text);

        case "references":
            $rsl = "";
            $ndx = 1;
            $text=str_replace("><","> <",$_text);
            $text=preg_split("/( |\t)/",strtr($text,$_spool->ids));
            $parents=preg_grep("/^\d+$/",$text);
            $p=array_pop($parents);
            $valid_parents = Array();
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
            return '<img src="xface.php?face='.base64_encode($_text).'"  alt="x-face" />';

        case "xorgid":
            return "$_text".(preg_match("/[\w]+\.[\w\d]+/",$_text)?" [<a href=\"".url("fiche.php")."?user=$_text\" class='popup2'>fiche</a>]":"");

        default:
            return htmlentities($_text);
    }
}

/** contextual links 
 * @return STRING HTML output
 */
function displayshortcuts() {
    global $news,$first,$spool,$group,$post,$id,$profile,$css;
    $sname = basename($_SERVER['SCRIPT_NAME']);

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
            echo '[<a href="index.php">'._('Liste des forums').'</a>] ';
            echo "[<a href=\"post.php?group=$group\">"._('Nouveau message')."</a>] ";
            if (sizeof($spool->overview)>$news['max']) {
                for ($ndx=1; $ndx<=sizeof($spool->overview); $ndx += $news['max']) {
                    if ($first==$ndx) {
                        echo "[$ndx-".min($ndx+$news['max']-1,sizeof($spool->overview))."] ";
                    } else {
                        echo "[<a href=\"?group=$group&amp;first=$ndx\">$ndx-".min($ndx+$news['max']-1,sizeof($spool->overview))."</a>] ";
                    }
                }
            }
            break;
        case 'article.php' :
            if (!$profile['autoup']) { 
                echo '[<a href="index.php?banana=updateall">Mettre à jour</a>] ';
            }
            echo '[<a href="'.url("confbanana.php").'">Profil</a>] ';
            echo '[<a href="index.php">'._('Liste des forums').'</a>] ';
            echo "[<a href=\"thread.php?group=$group\">$group</a>]";
            echo "[<a href=\"post.php?group=$group&amp;id=$id&amp;type=followup\">"._('Répondre')."</a>] ";
            if (checkcancel($post->headers)) {
                echo "[<a href=\"article.php?group=$group&amp;id=$id&amp;type=cancel\">"._('Annuler ce message')."</a>] ";
            }
            break;
        case 'post.php' :
            if (!$profile['autoup']) { 
                echo '[<a href="index.php?banana=updateall">Mettre à jour</a>] ';
            }
            echo '[<a href="'.url("confbanana.php").'">Profil</a>] ';
            echo '[<a href="index.php">'._('Liste des forums').'</a>] ';
            echo "[<a href=\"thread.php?group=$group\">$group</a>] ";
            break;
    }
    echo '</div>';
}

?>

