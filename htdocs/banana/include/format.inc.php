<?php
/********************************************************************************
* install.d/format.inc.php : HTML output subroutines
* --------------------------
*
* This file is part of the banana distribution
* Copyright: See COPYING files that comes with this distribution
********************************************************************************/

/** contextual links 
 * @return STRING HTML output
 */
function displayshortcuts($first = -1) {
    global $banana,$css;
    $sname = basename($_SERVER['SCRIPT_NAME']);

    echo "<div class=\"{$css['bananashortcuts']}\">";

    switch ($sname) {
        case 'subscribe.php' :
            echo '[<a href="index.php">Liste des forums</a>] ';
            echo '[<a href="'.url("confbanana.php").'">Profil</a>] ';
            break;
        case 'index.php' :
            if (!$banana->profile['autoup']) { 
                echo '[<a href="index.php?banana=updateall">Mettre à jour</a>] ';
            }
            echo '[<a href="'.url("confbanana.php").'">Profil</a>] ';
            echo '[<a href="subscribe.php">Abonnements</a>] ';
            break;
        case 'thread.php' :
            if (!$banana->profile['autoup']) { 
                echo '[<a href="index.php?banana=updateall">Mettre à jour</a>] ';
            }
            echo '[<a href="'.url("confbanana.php").'">Profil</a>] ';
            echo '[<a href="index.php">'._('Liste des forums').'</a>] ';
            echo "[<a href=\"post.php?group={$banana->spool->group}\">"._('Nouveau message')."</a>] ";
            if (sizeof($banana->spool->overview)>$banana->tmax) {
                for ($ndx=1; $ndx<=sizeof($banana->spool->overview); $ndx += $banana->tmax) {
                    if ($first==$ndx) {
                        echo "[$ndx-".min($ndx+$banana->tmax-1,sizeof($banana->spool->overview))."] ";
                    } else {
                        echo "[<a href=\"?group={$banana->spool->group}&amp;first=$ndx\">$ndx-".min($ndx+$banan->tmax-1,sizeof($banana->spool->overview))."</a>] ";
                    }
                }
            }
            break;
        case 'article.php' :
            if (!$banana->profile['autoup']) { 
                echo '[<a href="index.php?banana=updateall">Mettre à jour</a>] ';
            }
            echo '[<a href="'.url("confbanana.php").'">Profil</a>] ';
            echo '[<a href="index.php">'._('Liste des forums').'</a>] ';
            echo "[<a href=\"thread.php?group={$banana->spool->group}\">{$banana->spool->group}</a>]";
            echo "[<a href=\"post.php?group={$banana->spool->group}&amp;id={$banana->post->id}&amp;type=followup\">"
                ._('Répondre')."</a>] ";
            if ($banana->post->checkcancel()) {
                echo "[<a href=\"article.php?group={$banana->spool->group}&amp;id={$banana->post->id}&amp;type=cancel\">"
                    ._('Annuler ce message')."</a>] ";
            }
            break;
        case 'post.php' :
            if (!$banana->profile['autoup']) { 
                echo '[<a href="index.php?banana=updateall">Mettre à jour</a>] ';
            }
            echo '[<a href="'.url("confbanana.php").'">Profil</a>] ';
            echo '[<a href="index.php">'._('Liste des forums').'</a>] ';
            echo "[<a href=\"thread.php?group={$banana->spool->group}\">{$banana->spool->group}</a>] ";
            break;
    }
    echo '</div>';
}

?>

