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
 ***************************************************************************
        $Id: sondage.utils.inc.php,v 1.2 2004-08-31 11:16:48 x2000habouzit Exp $
 ***************************************************************************/

require_once("sondage.requetes.inc.php");
require_once("misc.inc.php");

/**
 * @package Utils
 * Fonctions communes à l'interface sondage
 */

/** récupérer une variable en REQUEST
 * et la purifier de ses balises ou autres
 * @param $nom_variable nom de la variable à récupérer
 * @return variable purifiée ou NULL si non définie en REQUEST
 */
function recupere($nom_variable) {
	if (isset($_REQUEST[$nom_variable]))
		return str_replace("'","&#039;",stripslashes(strip_tags($_REQUEST[$nom_variable],"<a><b><i><u>")));
	else
		return NULL;
}

/** récupérer une variable en REQUEST obligatoirement
 * et la purifier de ses balises ou autres
 * si elle n'est pas définie, le script s'arrête là en ajoutant header et footer
 * @param $nom_variable nom de la variable à récupérer
 * @return variable purifiée ou arrêt du script si non définie en REQUEST
 */
function recupere_non_vide_avec_header($nom_variable) {
    $res = recupere($nom_variable);
    if (!isset($res)) {
        require_once("header1.inc.php");
        require_once("header2.inc.php");
        erreur("Il y a eu un problème lors de la transmission des données du formulaire.");
        require("footer.inc.php");
        exit;
    }
    else
        return $res;
}

/** récupérer une variable en REQUEST obligatoirement
 * et la purifier de ses balises ou autres
 * si elle n'est pas définie, le script s'arrête là en ajoutant footer (les header doivent être
 * inclus avant)
 * @param $nom_variable nom de la variable à récupérer
 * @return variable purifiée ou arrêt du script si non définie en REQUEST
 */
function recupere_non_vide_sans_header($nom_variable) {
    $res = recupere($nom_variable);
    if (!isset($res)) {
        erreur("Il y a eu un problème lors de la transmission des données du formulaire.");
        require("footer.inc.php");
        exit;
    }
    else
        return $res;
}                                                            

/** teste si l'utilisateur a le droit de modifier un sondage
 * sinon termine le script
 * @param $SID l'id du sondage
 * @return les infos du sondage
 */
function permission_modifications($SID) {
    global $sondage;
    global $moderos;
    
	if (isset($SID)) {
        if (!isset($sondage))
		    $sondage = infos_sondage($SID);
		if ($sondage->en_prod == 1)//le sondage est en prod, seuls les admins peuvent le modifier
			check_perms();
		else {
            if (!isset($moderos))
                $moderos = moderateurs($SID);
			check_perms(usernames($moderos));
        }
        return $sondage;
	}
    else
        return NULL;
}

/** teste si l'utilisateur est dans une liste d'"ayants-droits"
 * sinon termine le script avec le footer spécial sondage
 * (vote sans les menus de x.org)
 * @param $auth_array tableau de chaînes (username)
 * @return rien
 */
function check_perms_sondage($auth_array) {
	if (!has_perms($auth_array)) {
		$_SESSION['log']->log("noperms",$_SERVER['PHP_SELF']);
		echo "<div class=\"erreur\">";
		echo "Tu n'as pas les permissions n&eacute;cessaires pour acc&eacute;der &agrave; cette page.";
		echo "</div>";
		include("sondage.footer.inc.php");
		exit;
	}
}
												
/** afficher un titre
 * @param $intitule le texte du titre
 * @return rien
 */
function titre($intitule) {
	echo "<div class=\"rubrique\">\n";
	echo $intitule."\n";
	echo "</div>\n";
}

/** commencer un paragraphe
 * @return rien
 */
function debut_paragraphe() {
	echo "<p class=\"normal\">\n";
}

/** terminer un paragraphe
 * @return rien
 */
function fin_paragraphe() {
	echo "</p>\n";
}

/** donner l'attribut correspondant à une ligne de tableau paire ou impaire
 * @param $pair paire ou impaire ?
 * @return rien
 */
function nom($pair) {
	if ($pair == 0)
		return " class =\"pair\"";
	else
		return " class=\"impair\"";
}

/** changer la valeur courante de la ligne (paire ou impaire)
 * @param $pair pair ou impair ?
 * @return la nouvelle valeur de pair
 */
function change($pair) {
        if ($pair == 0)
             	return 1;
	else
		return 0;
}

/** commencer un formulaire POST vers un fichier php
 * @param $formulaire nom du fichier php à appeler
 * @return rien
 */
function debut_formulaire($formulaire) {
	echo "<form method='POST' action='$formulaire'>\n";
}

/** commencer un formulaire POST vers le fichier php appelant
 * @return rien
 */
function debut_formulaire_self() {
	debut_formulaire($_SERVER['PHP_SELF']);
}

/** terminer un formulaire
 * @return rien
 */
function fin_formulaire() {
	echo "</form>\n";
}

/** afficher un texte en gras sur une ligne
 * @param $intitule texte à afficher
 * @return rien
 */
function affiche_intitule($intitule) {
	if ($intitule!="")
		echo "<b>".$intitule."</b><br>\n";
}

/** afficher une erreur
 * @param $intitule texte de l'erreur
 * @return rien
 */
function erreur($intitule) {
	if ($intitule!="")
		echo "<div class=\"erreur\">".$intitule."</div>\n";
}

/** afficher un mail avec lien hypertexte
 * @param $mail adresse à afficher
 * @return rien
 */
function affiche_mail($mail) {
	if ($mail!="")
        echo mailto($mail);
}

/** afficher un topo
 * @param $TOPO code du topo à afficher
 * @return rien
 */
function topo($TOPO) {
	debut_paragraphe();
	echo topo_req($TOPO);
	fin_paragraphe();
}

/** afficher une liste d'utilisateurs
 * @param $liste tableau d'enregistrements (promo,nom,prenom)
 * @param $type_utilisateur moderateur ou inscrit
 * @param $SID id du sondage
 * @return rien
 */
function affiche_liste_utilisateurs($liste,$type_utilisateur,$SID) {
	debut_paragraphe();
	$promo_courante = 0;
	echo "<b>";
	if (count($liste) == 0)
		echo "Aucun X n'est abonné.";
	else {
		echo count($liste)." X ";
		if (count($liste) ==1)
			echo "est abonné :";
		else
			echo "sont abonnés :";
	}
	echo "</b><br>";
	for ($i=0;$i<count($liste);$i++) {
		$virgule = true;
		if ($liste[$i]->promo != $promo_courante) {
			if ($promo_courante!=0)
				echo "<br>";
			$promo_courante = $liste[$i]->promo;
			echo "<b>[$promo_courante]</b> ";
			$virgule = false;
		}
		if ($virgule)
			echo ", ";
		echo $liste[$i]->prenom.' ';
        echo "<a href=\"javascript:x()\" onclick=\"popWin('../x.php?x=".$liste[$i]->username."')\">";
        echo $liste[$i]->nom.'</a> ';
        echo '<a href="droits.php?SID='.$SID.'&amp;retirer=1&amp;';
        echo $type_utilisateur.'='.urlencode($liste[$i]->username);
        echo '">[Supprimer]</a>';
	}
	fin_paragraphe();
}

/** afficher un choix de sondages
 * @param $sond tableau d'enregistrements (id,titre) représentants des sondages
 * @param $formulaire fichier php vers lequel renvoyer le choix d'un de ces sondages
 * @return rien
 */
function affiche_choix($sond,$formulaire) {
	for($i=0;$i<count($sond);$i++) {
		debut_formulaire($formulaire);
		caches(array("SID"=>$sond[$i]->id));
		soumettre(array($sond[$i]->titre=>"modifier"));
		fin_formulaire();
	}
}

/** afficher un champ texte
 * @param $intitule invite texte affichée avant le champ texte
 * @param $nom_variable nom de la variable associée au champ
 * @param $defaut valeur par défaut du champ
 * @param $type ligne ou paragraphe (4 lignes)
 * @return rien
 */
function champ_texte($intitule,$nom_variable,$defaut,$type) {
	global $LIGNE,$PARAGRAPHE;

	debut_paragraphe();
	affiche_intitule($intitule);
	if ($type==$LIGNE)
		echo "<input type='text' name='$nom_variable' value='$defaut' size=75>\n";
	else if ($type==$PARAGRAPHE)
		echo "<textarea name='$nom_variable' cols=65 rows=4>$defaut</textarea>\n";
	fin_paragraphe();
}

/** afficher une liste de choix multiples
 * @param $intitule invite texte affichée avant la liste
 * @param $nom_variable nom de la variable associée au choix (base des noms de variables pour les cases à cocher)
 * @param $defaut valeur par défaut du choix (binaire pour les cases à cocher)
 * @param $choix liste des choix (tableau de chaînes)
 * @param $type unique (boutons radio) ou multiple (cases à cocher)
 * @return rien
 */
function choix_multiple($intitule,$nom_variable,$defaut,$choix,$type) {
	global $UNIQUE,$MULTIPLE;

	debut_paragraphe();
	affiche_intitule($intitule);
	if ($type==$UNIQUE) //boutons radio
		$type_choix="radio";
	else if ($type==$MULTIPLE) //cases à cocher
		$type_choix="checkbox";
	for ($i=0;$i<count($choix);$i++) {
		if ($type==$UNIQUE) { //boutons radio -> defaut = numéro de la case cochée
			$nom_var=$nom_variable;
			$val=$i;
			if ($defaut==$i)
				${$nom_var}=1;
			else
				${$nom_var}=0;
		}
		else if ($type==$MULTIPLE) { //cases à cocher -> defaut = représentation binaire des cases cochées
			$nom_var=$nom_variable."_".$i;
			$val=1;
			if ($defaut%2)
				${$nom_var}=1;
			else
				${$nom_var}=0;
			$defaut/=2;
		}
		echo "<input type='$type_choix' name='$nom_var' value='$val'";
		if (${$nom_var} == 1)
			echo " CHECKED";
		echo ">";
		echo $choix[$i];
		echo "<br>\n";
	}
	fin_paragraphe();		
}

/** placer des champs cachés dans un formulaire
 * @param $variables tableau associatif (nom_de_la_variable => valeur)
 * @return rien
 */
function caches($variables) {
	foreach ($variables as $nom_variable=>$valeur) {
		echo "<input type='hidden' name='";
		echo $nom_variable;
		echo "' value='";
		echo $valeur;
		echo "'>\n";
	}
}

/** placer des boutons de validation dans un formulaire
 * @param $boutons tableau associatif (texte_du_bouton => nom_de_la_variable_associée)
 * @return rien
 */
function soumettre($boutons) {
	debut_paragraphe();
	foreach ($boutons as $intitule=>$nom_variable) {
		echo "<input type='submit' name='";
		echo $nom_variable;
		echo "' value='";
		echo $intitule;
		echo "'>&nbsp;&nbsp;\n";
	}
	fin_paragraphe();
}

/** placer un bouton éditer dans un formulaire avec variables cachées
 * @param $variables tableau associatif (nom_de_la_variable => valeur) de variables cachées
 * @param $formulaire nom du fichier php cible du formulaire
 * @return rien
 */
function editer($variables,$formulaire) {
	echo "<td>"; 
        debut_formulaire($formulaire);
	caches($variables);                 
	soumettre(array("Editer"=>"editer"));                     
	fin_formulaire();
	echo "</td>";
}

/** placer des boutons supprimer,descendre,monter avec variables cachées 
 * dans un formulaire de cible modifie.php
 * @param $variables tableau associatif (nom_de_la_variable => valeur) de variables cachées
 * @return rien
 */
function suppr_des_mont($variables) {
	echo "<td>";
	debut_formulaire("modifie.php");
	caches($variables);         
	soumettre(array("Suppr."=>"supprimer","->"=>"descendre","<-"=>"monter"));         
	fin_formulaire();
	echo "</td>";
}

/** afficher un choix de réponse dans le tableau de modification principal
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @param $RID l'id du choix
 * @param $texte chaîne correspondant au choix de réponse
 * @param $pair ligne paire ou impaire ?
 * @return nouvelle valeur de ligne paire ou impaire
 */
function afficher_reponse($SID,$PID,$QID,$RID,$texte,$pair) {
	echo "<tr".nom($pair)."><td colspan=\"2\"></td>";
	echo "<td>".$texte."</td>";
	echo "<td></td>";
	suppr_des_mont(array("SID"=>$SID,"PID"=>$PID,"QID"=>$QID,"RID"=>$RID));
	echo "</tr>";
	return change($pair);
}

/** afficher une question dans le tableau de modification principal
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @param $texte intitulé de la question
 * @param $pair ligne paire ou impaire ?
 * @return $pair nouvelle valeur de ligne paire ou impaire
 */
function afficher_question($SID,$PID,$QID,$texte,$pair,$infos) {
    global $QUESTION_TEXTE;

	echo "<tr".nom($pair)."><td></td>";
	echo "<td colspan=\"2\"><b>".$texte."</b></td>";
	editer(array("SID"=>$SID,"PID"=>$PID,"QID"=>$QID),"question.php");
	suppr_des_mont(array("SID"=>$SID,"PID"=>$PID,"QID"=>$QID));
	echo "</tr>";
	$pair=change($pair);
    if ($infos->type_question!=$QUESTION_TEXTE) {
	    $reponses = $infos->reponses;
        $ordre_courant = 0;
        if (count($reponses)>0)
            $min_courant = $reponses[0]->ordre;
        $min = 0;
        for ($i=0;$i<count($reponses);$i++) {
            for ($j=0;$j<count($reponses);$j++)
                if ($reponses[$j]->ordre>$ordre_courant && $reponses[$j]->ordre<$min_courant) {
                    $min = $j;
                    $min_courant = $reponses[$j]->ordre;
                }
            $ordre_courant = $min_courant;
            $min_courant = 255;//nb max de réponses
		    $pair = afficher_reponse($SID,$PID,$QID,$reponses[$min]->idr,$reponses[$min]->texte,$pair);
        }
    }
	return $pair;
}

/** afficher une partie dans le tableau de modification principal
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $titre titre de la partie
 * @param $pair ligne paire ou impaire ?
 * @param $parties le sondage comporte plusieurs parties ?
 * @return $pair nouvelle valeur de ligne paire ou impaire
 */
function afficher_partie($SID,$PID,$titre,$pair,$parties,$infos) {
	if ($parties == 0) {
		echo "<tr".nom($pair).">";
		echo "<td colspan=\"3\"><b><u>".$titre."</u></b></td>";
		editer(array("SID"=>$SID,"PID"=>$PID,"partie"=>0),"titre.php");
		suppr_des_mont(array("SID"=>$SID,"PID"=>$PID));
		echo "</tr>";
		$pair = change($pair);
	}
	$questions = $infos->questions;
    $ordre_courant = 0;
    if (count($questions)>0)
        $min_courant = $questions[0]->ordre;
    $min = 0;
    for ($i=0;$i<count($questions);$i++) {
        for ($j=0;$j<count($questions);$j++)
            if ($questions[$j]->ordre>$ordre_courant && $questions[$j]->ordre<$min_courant) {
                $min = $j;
                $min_courant = $questions[$j]->ordre;
            }
            $ordre_courant = $min_courant;
            $min_courant = 255;//nb max de questions
		$pair =
        afficher_question($SID,$PID,$questions[$min]->idq,($i+1).'. '.$questions[$min]->texte,$pair,$questions[$min]);
    }
	echo "<tr".nom($pair)."><td></td><td colspan = \"4\">";
	debut_formulaire("modifie.php");
	caches(array("SID"=>$SID,"PID"=>$PID));
	soumettre(array("Ajouter une question"=>"ajouter_question"));
	fin_formulaire();
	echo "</td></tr>";
	$pair = change($pair);
	return $pair;
}

/** afficher les réponses d'une question texte données par les sondés
 * @param $reponses les propositions faites par les sondés
 */
function afficher_reponses_texte($reponses) {
	echo '<table class="bicol" width="95%">';
	echo '<tr><th>Réponses</th></tr>';
	$pair = 0;
	for ($i=0;$i<count($reponses);$i++) {
        if ($reponses[$i]!="") {
		    echo "<tr".nom($pair)."><td>";
		    echo $reponses[$i];
		    echo "</td></tr>";
		    $pair = change($pair);
        }
	}
	echo '</table>';
}

/** afficher les réponses d'une question à choix multiples données par les sondés
 * @param $infos les infos du choix dont son intitulé
 * @param $reponses les résultats en nombre de votes et pourcentage
 */
function afficher_reponses_choix($infos,$reponses) {
	echo '<table class="bicol" width="95%">';
	echo '<tr><th>Réponse</th><th>Votes</th><th>%</th></tr>';
	$pair = 0;
	for ($i=0;$i<count($infos);$i++) {
		echo '<tr'.nom($pair).'><td>';
		echo $infos[$i]->texte;
		echo '</td><td>';
		echo $reponses[$i]->votes;
		echo '</td><td>';
		echo number_format($reponses[$i]->pourcentage,2);
		echo '</td></tr>';
		$pair = change($pair);
	}
	echo '</table>';
}

?>
