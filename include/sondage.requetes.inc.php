<?php
require_once("sondage.utils.inc.php");

/**
 * @package Requetes
 * Requêtes MySQL pour les sondages
 */


/** dit si un tableau est vide ou non
 * @param $donnees tableau à tester
 * @return booléen
 */
function est_vide($donnees) {
	if (!isset($donnees) || count($donnees)==0)
		return true;
	else
		return false;
}

/** exécute une requête MySQL et si celle-ci est invalide termine le script
 * permet de rattraper les erreurs MySQL proprement
 * @param $requete la requête MySQL à exécuter
 * @return $resultat le résultat de la requête
 */
function mysql_query_p($requete) {
    global $globals;
    $resultat = $globals->db->query($requete);
    if ($resultat == false) {
		erreur("Erreur dans une requête.");
		require("footer.inc.php");
		exit();
	}
	return $resultat;
}

/** exécute une requête MySQL select et termine le script si aucun enregistrement n'est renvoyé
 * @param $requete la requête MySQL
 * @return $resultat le résultat de la requête
 */
function mysql_query_p_non_vide($requete) {
    global $globals;
	$resultat = mysql_query_p($requete);
	if (mysql_num_rows($resultat) <= 0) {
		erreur("Erreur : pas d'enregistrement correspondant à la requête.");
		require("footer.inc.php");
		exit();
	}
	else
		return $resultat;
}

/** renvoie la liste des noms d'utilisateurs d'une liste d'enregistrements
 * @param $liste tableau d'enregistrements (...,username)
 * @return $res tableau des username correspondants
 */
function usernames($liste) {
    for ($i=0;$i<count($liste);$i++)
        $res[$i]=$liste[$i]->username;
    return $res;
}

/** renvoie la liste des modérateurs d'un sondage
 * @param $SID l'id du sondage
 * @return $res un tableau d'enregistrements (promo,nom,prenom)
 */
function moderateurs($SID) {
    global $globals;
	$resultat = mysql_query_p("select user_id,prenom,nom,promo,username ".
	"from x4dat.auth_user_md5 as u,sondage.moderateurs as m ".
	"where u.user_id=m.idu and m.ids=$SID order by promo,nom,prenom");
	$res = null;
	for ($i=0;$i<mysql_num_rows($resultat);$i++) {
		$donnee=mysql_fetch_array($resultat);
		$nouveau->prenom=$donnee["prenom"];
		$nouveau->nom=$donnee["nom"];
		$nouveau->promo=$donnee["promo"];
        $nouveau->username=$donnee["username"];
        $nouveau->uid=$donnee["user_id"];
		$res[]=$nouveau;
	}
	return $res;
}

/** renvoie le mail des modérateurs (tous) d'un sondage
 * @param $SID l'id du sondage
 * @return $res une adresse mail (chaîne)
 */
function mail_moderateurs($SID) {
	$resultat=mysql_query_p("select username ".
	"from x4dat.auth_user_md5 as u, sondage.moderateurs as m ".
	"where m.ids=$SID and m.idu=u.user_id");
	$res = "";
	for ($i=0;$i<mysql_num_rows($resultat);$i++) {
		$donnee=mysql_fetch_array($resultat);
		if ($i!=0)
			$res.=",";
		$res.=$donnee["username"]."@m4x.org";
	}
	return $res;
}

/** renvoie la liste des inscrits d'un sondage
 * @param $SID l'id du sondage
 * @return $res un tableau d'enregistrements (promo,nom,prenom)
 */
function inscrits($SID) {
	$resultat = mysql_query_p("select user_id,prenom,nom,promo,username ".
	"from x4dat.auth_user_md5 as u,sondage.inscrits as i ".
	"where u.user_id=i.idu and i.ids=$SID order by promo,nom,prenom");
	$res = null;
	for ($i=0;$i<mysql_num_rows($resultat);$i++) {
		$donnee=mysql_fetch_array($resultat);
		$nouveau->prenom=$donnee["prenom"];
		$nouveau->nom=$donnee["nom"];
		$nouveau->promo=$donnee["promo"];
        $nouveau->username=$donnee["username"];
        $nouveau->uid=$donnee["user_id"];
		$res[]=$nouveau;
	}
	return $res;
}

/** renvoie l'id d'un utilisateur dont on a l'username
 * @param $adresse le nom d'utilisateur
 * @return l'uid ou NULL s'il n'y a pas d'utilisateur avec cet username
 */
function recupere_uid($adresse) {
	$resultat = mysql_query_p("select user_id from x4dat.auth_user_md5 ".
	"where username='$adresse'");
	if (mysql_num_rows($resultat)<=0) {
		erreur("Il n'y a pas d'X inscrit d'identifiant : $adresse.");
		return NULL;
	}
	else {
		$donnee=mysql_fetch_array($resultat);
		return $donnee["user_id"];
	}
}

/** ajoute un modérateur à un sondage
 * @param $SID l'id du sondage
 * @param $adresse l'username à ajouter
 * @return rien
 */
function ajouter_moderateur($SID,$adresse) {
	$UID = recupere_uid($adresse);
	if (isset($UID))
		mysql_query_p("insert into sondage.moderateurs (ids,idu) values($SID,$UID)");
}

/** ajoute un inscrit à un sondage
 * @param $SID l'id du sondage
 * @param $adresse l'username à ajouter
 * @return rien
 */
function ajouter_inscrit($SID,$adresse) {
    $UID = recupere_uid($adresse);
	if (isset($UID))
		mysql_query_p("insert into sondage.inscrits (ids,idu) values($SID,$UID)");
}

/** supprime un modérateur à un sondage
 * @param $SID l'id du sondage
 * @param $adresse l'username à supprimer
 * @return rien
 */
function supprimer_moderateur($SID,$adresse) {
	$resultat = mysql_query_p("select idu from sondage.moderateurs where ids=$SID");
	if (mysql_num_rows($resultat) <= 1)
		erreur("Il doit y avoir au moins un modérateur pour un sondage.");
	else {
        $UID = recupere_uid($adresse);
		if (isset($UID))
			mysql_query_p("delete from sondage.moderateurs where ids=$SID and idu=$UID");
	}
}

/** supprime un inscrit à un sondage
 * @param $SID l'id du sondage
 * @param $adresse l'username à supprimer
 * @return rien
 */
function supprimer_inscrit($SID,$adresse) {
    $UID = recupere_uid($adresse);
	if (isset($UID))                   
		mysql_query_p("delete from sondage.inscrits where ids=$SID and idu=$UID");
} 

/** renvoie les titres des sondages qui sont/ne sont pas en production pour un certain utilisateur
 * @param $uid l'id de l'utilisateur
 * @param $prod en production ou non
 * @return $res un tableau d'enregistrements (ids,titre)
 */
function sondages($prod,$uid) {
    if ($prod == 1)
        $not = "";
    else
        $not = "!";
	$resultat=mysql_query_p("select d.ids,titre ".
	"from sondage.description_generale as d,sondage.moderateurs as m ".
	"where $not FIND_IN_SET('prod',d.flags) and d.ids=m.ids and m.idu=$uid");
	$res = null;
	for ($i=0;$i<mysql_num_rows($resultat);$i++) {
		$donnee=mysql_fetch_array($resultat);
		$nouveau->id=$donnee["ids"];
		$nouveau->titre=$donnee["titre"];
		$res[]=$nouveau;
	}
	return $res;
}

/** sondages qui sont en production pour un certain utilisateur
 * @param $uid l'id de l'utilisateur
 * @return un tableau d'enregistrements (ids,titre)
 */
function sondages_en_prod($uid) {
	return sondages(1,$uid);
}

/** sondages qui ne sont pas en production pour un certain utilisateur
 * @param $uid l'id de l'utilisateur
 * @return un tableau d'enregistrements (ids,titre)
 */
function sondages_non_en_prod($uid) {
	return sondages(0,$uid);
}
/** nombre de sondages en production et en modification
 * @param $uid l'id de l'utilisateur
 * @return $res un enregistrement (en_prod,en_modif)
 */
function nb_sondages_prod($uid) {
    $resultat = mysql_query_p("select sum(FIND_IN_SET('prod',d.flags)),count(d.flags)".
    "from sondage.description_generale as d,sondage.moderateurs as m ".
    "where d.ids=m.ids and m.idu=$uid");
    list($res->en_prod,$res->en_modif) = mysql_fetch_row($resultat);
    $res->en_modif=$res->en_modif-$res->en_prod;
    return $res;
}

/** passer un sondage en production
 * @param $SID l'id du sondage
 * @return rien
 */
function passer_en_prod($SID,$alias) {
	mysql_query_p("update sondage.description_generale "
    . "set flags=CONCAT_WS(',',flags,'prod'), alias='$alias' where ids=$SID");
}

/** dit si un utilisateur a déjà voté à un sondage
 * @param $SID l'id du sondage
 * @param $user_id l'id de l'utilisateur
 * @param $sondage informations sur le sondage (renvoyées par la fonction infos_sondage)
 * @return un booléen
 */
function deja_vote($SID,$user_id,$sondage) {
	$resultat = mysql_query_p("select idu from sondage.sondes where ids=$SID and idu=$user_id");
	if (mysql_num_rows($resultat) > 0 && $sondage->en_prod==1)
		return 1;
	else
		return 0;
}

/** renvoyer les informations générales sur un sondage
 * @param $SID l'id du sondage
 * @return $res un enregistrement (titre,en_tete,pied,parties,prod,tous,mail)
 */
function infos_sondage($SID) {
	$resultat=mysql_query_p("select titre,en_tete,pied,mail".
        ", FIND_IN_SET('prod',flags), !FIND_IN_SET('parties',flags), !FIND_IN_SET('tous',flags)".
	    " from sondage.description_generale where ids=$SID");
	$res = null;
	if (mysql_num_rows($resultat)>0) {
		list($res->titre, $res->en_tete, $res->pied, $res->mail, $res->en_prod, $res->parties, $res->tous) = mysql_fetch_row($resultat);
	}
	else {
        require_once("header1.inc.php");
        require_once("header2.inc.php");
		erreur("Ce sondage n'existe pas.");
		require("footer.inc.php");
		exit;
	}
	return $res;
}

/** renvoyer l'id d'un sondage et récupérer les informations générales
 * @param $alias l'alias du sondage
 * @return $SID l'id du sondage
 */
function obtenir_sid($alias) {
    global $sondage;
    $resultat=mysql_query_p("select ids,titre,en_tete,pied,mail".
    ", FIND_IN_SET('prod',flags), !FIND_IN_SET('parties',flags), !FIND_IN_SET('tous',flags)".
    " from sondage.description_generale where alias='$alias'");
    if (mysql_num_rows($resultat)>0) {
        list($SID,$res->titre, $res->en_tete, $res->pied, $res->mail, $res->en_prod, $res->parties, $res->tous) = mysql_fetch_row($resultat);
        $sondage = $res;
    }
    else {
        require_once("header1.inc.php");
        require_once("header2.inc.php");
        erreur("Ce sondage n'existe pas.");
        require("footer.inc.php");
        exit;
    }
    return $SID;
}

/** renvoyer les informations générales sur une partie
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @return $res un enregistrement (idp,titre,en_tete,pied,ordre)
 */
function infos_partie($SID,$PID) {
	$resultat=mysql_query_p("select idp,sous_titre,en_tete,pied,ordre ".
	"from sondage.parties where ids=$SID and idp=$PID");
	$res = null;
	if (mysql_num_rows($resultat)>0) {
		$donnees=mysql_fetch_array($resultat);
		$res->idp = $donnees["idp"];
		$res->titre=$donnees["sous_titre"];
		$res->en_tete=$donnees["en_tete"];
		$res->pied=$donnees["pied"];
		$res->ordre=$donnees["ordre"];
	}
	return $res;
}

/** renvoyer les informations générales sur une question
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @return $res un enregistrement (idq,texte,type_reponse,ordre)
 */
function infos_question($SID,$PID,$QID) {
	$resultat=mysql_query_p("select idq,texte,type_reponse,ordre ".
	"from sondage.questions where ids=$SID and idp=$PID and idq=$QID");
	$res = null;
	if (mysql_num_rows($resultat)>0) {
		$donnees=mysql_fetch_array($resultat);
		$res->idq=$donnees["idq"];
		$res->texte = $donnees["texte"];
		/** champ texte, choix multiple... */
		$res->type_question = $donnees["type_reponse"];
		$res->ordre=$donnees["ordre"];
	}
	return $res;
}

/** renvoyer les informations générales sur un choix de réponse
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @param $RID l'id du choix
 * @return $res un enregistrement (idr,texte,coche,ordre)
 */
function infos_reponse($SID,$PID,$QID,$RID) {
	$resultat=mysql_query_p("select idr,reponse,coche,ordre ".
	"from sondage.choix where ids=$SID and idp=$PID and idq=$QID and idr=$RID");
	$res = null;
	if (mysql_num_rows($resultat)>0) {
		$donnees=mysql_fetch_array($resultat);
		$res->idr=$donnees["idr"];
		$res->texte = $donnees["reponse"];
		/** coché par défaut */
		$res->coche = $donnees["coche"];
		$res->ordre=$donnees["ordre"];
	}
	return $res;
}

/** renvoyer les informations sur les parties d'un sondage
 * @param $SID l'id du sondage
 * @return $res un tableau d'enregistrements (idp,titre,en_tete,pied,ordre)
 */
function infos_parties($SID) {
	$resultat=mysql_query_p("select idp from sondage.parties where ids=$SID order by ordre");
	$res = null;
	for ($i=0;$i<mysql_num_rows($resultat);$i++) {
		$ligne = mysql_fetch_array($resultat);
		$res[] = infos_partie($SID,$ligne["idp"]);
	}
	return $res;
}

/** renvoyer les informations sur les parties d'un sondage avec questions et réponses en une seule
 * requête
 * @param $SID l'id du sondage
 * @param $en_prod permet de dire s'il faut aussi récolter les votes (en production)
 * @return $res un tableau d'enregistrements (idp,titre,en_tete,pied,ordre,questions)
 */
function infos_parties_une_seule_requete($SID,$en_prod) {
    global $UNIQUE,$MULTIPLE,$QUESTION_TEXTE,$QUESTION_MULT;
    $parties=mysql_query_p("select idp,sous_titre,en_tete,pied,ordre ".
    "from sondage.parties where ids=$SID order by idp");
    $questions=mysql_query_p("select idp,idq,texte,type_reponse,ordre ".
    "from sondage.questions where ids=$SID order by idp,idq");
    $reponses=mysql_query_p("select idp,idq,idr,reponse,coche,ordre ".
    "from sondage.choix where ids=$SID order by idp,idq,ordre");
    if ($en_prod == 1) {
        $reponses_texte=mysql_query_p("select idp,idq,reponse from sondage.reponses_texte ".
        "where ids=$SID order by idp,idq");
        $reponses_choix=mysql_query_p("select idp,idq,code from sondage.reponses_choix_multiple ".
        "where ids=$SID order by idp,idq");
        if (mysql_num_rows($reponses_texte)>0)
            $reponse_texte = mysql_fetch_array($reponses_texte);
        if (mysql_num_rows($reponses_choix)>0)
            $reponse_choix = mysql_fetch_array($reponses_choix);
        $t = 0;
        $c = 0;
    }   
    $res = null;
    if (mysql_num_rows($questions)>0)
        $question= mysql_fetch_array($questions);
    if (mysql_num_rows($reponses)>0)
        $reponse= mysql_fetch_array($reponses);
    $j=0;
    $k=0;
    for ($i=0;$i<mysql_num_rows($parties);$i++) {
        $partie = mysql_fetch_array($parties);
        $res[$i]->idp = $partie["idp"];
        $res[$i]->titre=$partie["sous_titre"];
        $res[$i]->en_tete=$partie["en_tete"];
        $res[$i]->pied=$partie["pied"];
        $res[$i]->ordre=$partie["ordre"];
        $resq = null;
        $l = 0;
        if (mysql_num_rows($questions)>0)
        while($question["idp"] == $partie["idp"] && $j<=mysql_num_rows($questions)) {
            $resq[$l]->idq=$question["idq"];
            $resq[$l]->texte = $question["texte"];
            /** champ texte, choix multiple... */
            $resq[$l]->type_question = $question["type_reponse"];
            $resq[$l]->ordre=$question["ordre"];
            $resr = null;
            $m = 0;
            if (mysql_num_rows($reponses)>0)
            while($reponse["idp"] == $partie["idp"] && $reponse["idq"] == $question["idq"] 
                && $k<=mysql_num_rows($reponses)) {
                $resr[$m]->idr=$reponse["idr"];
                $resr[$m]->texte = $reponse["reponse"];
                /** coché par défaut */
                $resr[$m]->coche = $reponse["coche"];
                $resr[$m]->ordre=$reponse["ordre"];
                if (mysql_num_rows($reponses)>0)
                    $reponse= mysql_fetch_array($reponses);
                $k++;
                $m++;
            }
            $resq[$l]->reponses=$resr;
            if ($en_prod == 1) {
                if ($question["type_reponse"]==$QUESTION_TEXTE) {
                    if (mysql_num_rows($reponses_texte)>0)
                    while($reponse_texte["idp"] == $partie["idp"] 
                    && $reponse_texte["idq"] == $question["idq"]
                    && $t<=mysql_num_rows($reponses_texte)) {
                        $resv[]=$reponse_texte["reponse"];
                        if (mysql_num_rows($reponses_texte)>0)
                            $reponse_texte= mysql_fetch_array($reponses_texte);
                        $t++;
                    }
                    $resq[$l]->propositions = $resv;
                }
                else {
                    $nb_reponses = count($resr);
                    if ($resq[$l]->type_question==$QUESTION_MULT)
                        $codage = $MULTIPLE;
                    else
                        $codage = $UNIQUE;
                    $counter = 0;
                    for ($cj=0;$cj<$nb_reponses;$cj++)
                        $votes[$cj] = 0;
                    if (mysql_num_rows($reponses_choix)>0)
                    while($c<=mysql_num_rows($reponses_choix)
                    && $reponse_choix["idp"] == $partie["idp"]
                    && $reponse_choix["idq"] == $question["idq"]) {
                        if ($codage == $UNIQUE) { //la valeur stockée correspond au numéro du choix
                            if ($reponse_choix["code"]!=0)
                                $votes[$reponse_choix["code"]-1]++;
                        }
                        else { //la représentation binaire de la valeur stockée correspond aux choix cochés
                            $code = $reponse_choix["code"];
                            for ($cj=0;$cj<$nb_reponses;$cj++) {
                                $votes[$cj]+=$code%2;
                                $code=$code/2;
                            }
                        }
                        if (mysql_num_rows($reponses_choix)>0)
                            $reponse_choix=mysql_fetch_array($reponses_choix);
                        $c++;
                        $counter++;
                    }
                    $resc = null;
                    for ($cj=0;$cj<$nb_reponses;$cj++) {
                        $resc[$cj]->votes=$votes[$cj];
                        if ($counter>0)
                            $resc[$cj]->pourcentage=$votes[$cj]/$counter*100;
                        else
                            $resc[$cj]->pourcentage=0;
                    }
                    $resq[$l]->resultats = $resc;
                }
            }
            if ($j<mysql_num_rows($questions))
                $question= mysql_fetch_array($questions);
            $j++;
            $l++;
        }
        $res[$i]->questions=$resq;
    }
    return $res;
}

/** renvoyer les informations sur les questions d'une partie
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @return $res un tableau d'enregistrements (idq,texte,type_reponse,ordre)
 */
function infos_questions($SID,$PID) {
        $resultat=mysql_query_p("select idq from sondage.questions where ids=$SID and idp=$PID order by ordre");
	$res = null;			        
	for ($i=0;$i<mysql_num_rows($resultat);$i++) {
		$ligne = mysql_fetch_array($resultat);
		$res[] = infos_question($SID,$PID,$ligne["idq"]);
	}
	return $res;
}

/** renvoyer les informations sur les choix de réponse d'une question
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @return $res un tableau d'enregistrements (idr,texte,coche,ordre)
 */
function infos_reponses($SID,$PID,$QID) {
	$resultat=mysql_query_p("select idr from sondage.choix where ids=$SID and idp=$PID and idq=$QID order by ordre");
	$res = null;
	for ($i=0;$i<mysql_num_rows($resultat);$i++) {
		$ligne = mysql_fetch_array($resultat);
		$res[] = infos_reponse($SID,$PID,$QID,$ligne["idr"]);
	}
	return $res;
}

/** renvoyer les réponses des sondés à une question de type texte
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @return $res un tableau de chaînes
 */
function reponses_texte($SID,$PID,$QID) {
	$resultat = mysql_query_p("select reponse from sondage.reponses_texte where ids=$SID and idp=$PID and idq=$QID");
	$res = null;
	for ($i=0;$i<mysql_num_rows($resultat);$i++) {
		$ligne = mysql_fetch_array($resultat);
		$res[] = $ligne["reponse"];
	}
	return $res;
}

/** renvoyer les réponses des sondés à une question de type choix multiple
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @param $nb_reponses le nombre de choix
 * @param $codage plusieurs réponses possibles ou choix unique
 * @return $res un tableau d'enregistrements (votes,pourcentage)
 */
function reponses_choix($SID,$PID,$QID,$nb_reponses,$codage) {
	global $UNIQUE;

	$resultat = mysql_query_p("select code from sondage.reponses_choix_multiple where ids=$SID and idp=$PID and idq=$QID");
	for ($j=0;$j<$nb_reponses;$j++)
		$votes[]=0;
	for ($i=0;$i<mysql_num_rows($resultat);$i++) {
		$ligne = mysql_fetch_array($resultat);
		if ($codage == $UNIQUE) { //la valeur stockée correspond au numéro du choix
			if ($ligne["code"]!=0)
                $votes[$ligne["code"]-1]++;
        }
		else { //la représentation binaire de la valeur stockée correspond aux choix cochés
			$code = $ligne["code"];
			for ($j=0;$j<$nb_reponses;$j++) {
				$votes[$j]+=$code%2;
				$code=$code/2;
			}
		}
	}
	for ($j=0;$j<$nb_reponses;$j++) {
		$res[$j]->votes = $votes[$j];
		if (mysql_num_rows($resultat)>0)
			$res[$j]->pourcentage = $votes[$j]/mysql_num_rows($resultat)*100;
		else
			$res[$j]->pourcentage = 0;
	}
	return $res;
}

/** renvoyer le texte d'un topo explicatif de l'interface
 * @param $TOPO l'id du topo
 * @return une chaîne
 */
function topo_req($TOPO) {
	$resultat = mysql_query_p("select texte from sondage.topo where ref = $TOPO");
	$ligne = mysql_fetch_array($resultat);
	return $ligne["texte"];
}

/** ajouter un sondage dont le premier modérateur sera un certain utilisateur
 * @param $user_id l'id de l'utilisateur
 * @return $SID l'id du sondage créé
 */
function ajouter_sondage($user_id) {
    //on vérifie que l'utilisateur n'a pas trop de sondages
    $resultat = mysql_query_p("select count(ids) from sondage.moderateurs where idu=$user_id");
    list($nb_sondages) = mysql_fetch_row($resultat);
    if ($nb_sondages > 5) {
        erreur("Tu as atteint le quota maximal de sondages autorisés. Tu ne peux plus en créer.");
        require("footer.inc.php");
        exit;
    }
	mysql_query_p("insert into sondage.description_generale (titre) values('')");
    $globals->db->query("lock sondage.description_generale");//lock nécessaire pour le retour de SID
	$resultat = mysql_query_p("select max(ids) from sondage.description_generale");
    $ligne = mysql_fetch_row($resultat);
	$SID = $ligne[0];
	mysql_query_p("insert into sondage.moderateurs (ids,idu) values($SID,$user_id)");
    $globals->db->query("unlock sondage.description_generale");
	return $SID;
}

/** renvoyer un numéro d'ordre disponible pour une partie d'un sondage
 * @param $SID l'id du sondage
 * @return l'ordre de la partie
 */
function pid_transitoire($SID) {
	$resultat = mysql_query_p("select max(ordre) from sondage.parties where ids=$SID");
	$ligne = mysql_fetch_array($resultat);
	if ($ligne[0]==null)
		return 1;
	else
		return $ligne[0]+1;
}

/** renvoyer un numéro d'ordre disponible pour une question d'une partie
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @return l'ordre de la question
 */
function qid_transitoire($SID,$PID) {
    $resultat = mysql_query_p("select max(ordre) from sondage.questions where ids=$SID and idp=$PID");
	$ligne = mysql_fetch_array($resultat);
	if ($ligne[0]==null)
		return 1;
	else
		return $ligne[0]+1;
}

/** renvoyer un numéro d'ordre disponible pour un choix d'une question
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @return l'ordre du choix
 */
function rid_transitoire($SID,$PID,$QID) {
    $resultat = mysql_query_p("select max(ordre) from sondage.choix where ids=$SID and idp=$PID and idq=$QID");
	$ligne = mysql_fetch_array($resultat);
	if ($ligne[0]==null)
		return 1;
	else
		return $ligne[0]+1;
}

/** ajouter une partie à un sondage
 * @param $SID l'id du sondage
 * @return $PID l'id de la partie créée
 */
function ajouter_partie($SID) {
	$globals->db->query("lock sondage.parties");//lock nécessaire pour le retour de PID
	$resultat = mysql_query_p("select max(idp),max(ordre) from sondage.parties where ids = $SID");
	$ligne = mysql_fetch_array($resultat);
	if ($ligne[0]==null)
		$PID = 1;
	else
		$PID = $ligne[0]+1;
	if ($ligne[1]==null)
        $ordre = 1;
    else
        $ordre = $ligne[1]+1;                    
	mysql_query_p("insert into sondage.parties (ids,idp,ordre,sous_titre,en_tete,pied) values($SID,$PID,$ordre,'','','')");
	$globals->db->query("unlock sondage.parties");
	return $PID;
}

/** ajouter une question à une partie
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @return $QID l'id de la question créée
 */
function ajouter_question($SID,$PID) {
	$globals->db->query("lock sondage.questions");//lock nécessaire pour le retour de QID
        $resultat = mysql_query_p("select max(idq),max(ordre) from sondage.questions where ids = $SID and idp=$PID");
	$ligne = mysql_fetch_array($resultat);
	if ($ligne[0]==null)
		$QID = 1;
	else
		$QID = $ligne[0]+1;
    if ($ligne[1]==null)
        $ordre = 1;
    else
        $ordre = $ligne[1]+1;
	mysql_query_p("insert into sondage.questions (ids,idp,idq,ordre,texte,type_reponse) values($SID,$PID,$QID,$ordre,'',0)");
	$globals->db->query("unlock sondage.questions");
	return $QID;
}

/** ajouter un choix de réponse à une question
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @return $RID l'id du choix créé
 */
function ajouter_reponse($SID,$PID,$QID) {
	$globals->db->query("lock sondage.choix");//lock nécessaire pour le retour de RID
    $resultat = mysql_query_p("select max(idr),max(ordre) from sondage.choix where ids = $SID and idp=$PID and idq=$QID");
	$ligne = mysql_fetch_array($resultat);
	if ($ligne[0]==null)
		$RID = 1;
	else
		$RID = $ligne[0]+1;
    if ($ligne[1]==null)
        $ordre = 1;
    else
        $ordre = $ligne[1]+1;
	mysql_query_p("insert into sondage.choix (ids,idp,idq,idr,ordre) values($SID,$PID,$QID,$RID,$ordre)");
	$globals->db->query("unlock sondage.choix");
	return $RID;
}

/** mettre à jour les informations d'un sondage
 * @param $SID l'id du sondage
 * @param $titre titre du sondage
 * @param $en_tete en-tête du sondage
 * @param $pied pied de page
 * @param $prod le sondage est-il en production ?
 * @param $parties y a-t-il plusieurs parties?
 * @param $tous tous les inscrits peuvent-ils voter ?
 * @param $mail mail de contact des sondés
 * @return rien
 */
function mettre_a_jour_sondage($SID,$titre,$en_tete,$pied,$prod,$parties,$tous,$mail) {
    // on connait l'ensemble des flags, on peut donc reconstruire le champ flag en entier
    $flags = ($prod == 1 ? 'prod,' : '');
    $flags .= ($parties != 1 ? 'parties,' : '');
    $flags .= ($tous != 1 ? 'tous,' : '');
	mysql_query_p("update sondage.description_generale "
    ."set titre='$titre',en_tete='$en_tete',pied='$pied', flags = '$flags', mail='$mail' where ids=$SID");
}

/** mettre à jour les informations d'une partie
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $titre titre du sondage
 * @param $en_tete en-tête du sondage
 * @param $pied pied de page
 * @return rien
 */
function mettre_a_jour_partie($SID,$PID,$titre,$en_tete,$pied) {
    mysql_query_p("update sondage.parties set sous_titre='$titre',en_tete='$en_tete',pied='$pied' ".
    "where ids=$SID and idp=$PID");
}

/** mettre à jour les informations d'une question
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @param $texte intitulé de la question
 * @param $type_question type de la question (texte, choix multiple...)
 * @return rien
 */
function mettre_a_jour_question($SID,$PID,$QID,$texte,$type_question) {
    mysql_query_p("update sondage.questions set texte='$texte',type_reponse='$type_question' ".
    "where ids=$SID and idp=$PID and idq=$QID");
}

/** mettre à jour les informations d'un choix de réponse
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @param $RID l'id du choix
 * @param $texte intitulé du choix
 * @param $coche coché par défaut ?
 * @return rien
 */
function mettre_a_jour_reponse($SID,$PID,$QID,$RID,$texte,$coche) {
	if ($texte!="") {
		mysql_query_p("update sondage.choix set reponse='$texte',coche='$coche' ".
        "where ids=$SID and idp=$PID and idq=$QID and idr=$RID");
	}
}

/** supprimer une partie
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @return rien
 */
function supprimer_partie($SID,$PID) {
	mysql_query_p("delete from sondage.parties where ids=$SID and idp=$PID");
	mysql_query_p("delete from sondage.questions where ids=$SID and idp=$PID");
	mysql_query_p("delete from sondage.choix where ids=$SID and idp=$PID");
}

/** supprimer une question
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @return rien
 */
function supprimer_question($SID,$PID,$QID) {
	mysql_query_p("delete from sondage.questions where ids=$SID and idp=$PID and idq=$QID");
	mysql_query_p("delete from sondage.choix where ids=$SID and idp=$PID and idq=$QID");
}

/** supprimer un choix de réponse
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @param $RID l'id du choix
 * @return rien
 */
function supprimer_reponse($SID,$PID,$QID,$RID) {
	mysql_query_p("delete from sondage.choix where ids=$SID and idp=$PID and idq=$QID and idr=$RID");
}

/** échanger la position de deux parties
 * @param $SID l'id du sondage
 * @param $PID1 l'ordre de la partie 1
 * @param $PID2 l'ordre de la partie 2
 * @return rien
 */
function echanger_partie($SID,$PID1,$PID2,$temp) {
	//$temp = pid_transitoire($SID);
	mysql_query_p("update sondage.parties set ordre=$temp where ids=$SID and ordre=$PID1");
	mysql_query_p("update sondage.parties set ordre=$PID1 where ids=$SID and ordre=$PID2");
	mysql_query_p("update sondage.parties set ordre=$PID2 where ids=$SID and ordre=$temp");
}

/** échanger la position de deux questions
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID1 l'ordre de la question 1
 * @param $QID2 l'ordre de la question 2
 * @return rien
 */
function echanger_question($SID,$PID,$QID1,$QID2,$temp) {
	//$temp = qid_transitoire($SID,$PID);
	mysql_query_p("update sondage.questions set ordre=$temp where ids=$SID and idp=$PID and ordre=$QID1");
	mysql_query_p("update sondage.questions set ordre=$QID1 where ids=$SID and idp=$PID and ordre=$QID2");
	mysql_query_p("update sondage.questions set ordre=$QID2 where ids=$SID and idp=$PID and ordre=$temp");
}

/** échanger la position de deux choix de réponse
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @param $RID1 l'ordre du choix 1
 * @param $RID2 l'ordre du choix 2
 * @return rien
 */
function echanger_reponse($SID,$PID,$QID,$RID1,$RID2,$temp) {
	//$temp = rid_transitoire($SID,$PID,$QID);
	mysql_query_p("update sondage.choix set ordre=$temp where ids=$SID and idp=$PID and idq=$QID and ordre=$RID1");
	mysql_query_p("update sondage.choix set ordre=$RID1 where ids=$SID and idp=$PID and idq=$QID and ordre=$RID2");
	mysql_query_p("update sondage.choix set ordre=$RID2 where ids=$SID and idp=$PID and idq=$QID and ordre=$temp");
}

/** descendre une partie d'un niveau
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @return rien
 */
function descendre_partie($SID,$PID) {
	$globals->db->query("lock sondage.parties");//lock nécessaire pour récupérer max(ordre)
	$resultat = mysql_query_p("select min(s1.ordre),s2.ordre ".
    "from sondage.parties as s1,sondage.parties as s2 ".
    "where s2.ids=$SID and s2.idp=$PID and s1.ids=$SID and s1.ordre>s2.ordre group by s2.ordre");
    if (mysql_num_rows($resultat)>0) {
        $ligne = mysql_fetch_array($resultat);
	    if ($ligne[0]!=null)
		    echanger_partie($SID,$ligne[1],$ligne[0],0);
    }
	$globals->db->query("unlock sondage.parties");
}

/** descendre une question d'un niveau
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @return rien
 */
function descendre_question($SID,$PID,$QID) {
	$globals->db->query("lock sondage.questions");//lock nécessaire pour récupérer max(ordre)
	$resultat = mysql_query_p("select min(s1.ordre),s2.ordre ".
    "from sondage.questions as s1,sondage.questions as s2 ".
    "where s2.ids=$SID and s2.idp=$PID and s2.idq=$QID and ".
    "s1.ids=$SID and s1.idp=$PID and s1.ordre>s2.ordre group by s2.ordre");
	if (mysql_num_rows($resultat)>0) {
        $ligne = mysql_fetch_array($resultat);
	    if ($ligne[0]!=null)
		    echanger_question($SID,$PID,$ligne[1],$ligne[0],0);
	}
    $globals->db->query("unlock sondage.questions");
}

/** descendre un choix de réponse d'un niveau
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @param $RID l'id du choix
 * @return rien
 */
function descendre_reponse($SID,$PID,$QID,$RID) {
	$globals->db->query("lock sondage.choix");//lock nécessaire pour récupérer max(ordre)
    $resultat = mysql_query_p("select min(s1.ordre),s2.ordre ".
    "from sondage.choix as s1,sondage.choix as s2 ".
    "where s2.ids=$SID and s2.idp=$PID and s2.idq=$QID and s2.idr=$RID and ".
    "s1.ids=$SID and s1.idp=$PID and s1.idq=$QID and s1.ordre>s2.ordre group by s2.ordre");
    if (mysql_num_rows($resultat)>0) {
        $ligne = mysql_fetch_array($resultat);
	    if ($ligne[0]!=null)
		    echanger_reponse($SID,$PID,$QID,$ligne[1],$ligne[0],0);
	}
    $globals->db->query("unlock sondage.choix");
}

/** monter une partie d'un niveau
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @return rien
 */
function monter_partie($SID,$PID) {
	$globals->db->query("lock sondage.parties");//lock nécessaire pour récupérer max(ordre)
    $resultat = mysql_query_p("select max(s1.ordre),s2.ordre ".
    "from sondage.parties as s1,sondage.parties as s2 ".
    "where s2.ids=$SID and s2.idp=$PID and s1.ids=$SID and s1.ordre<s2.ordre group by s2.ordre");
    if (mysql_num_rows($resultat)>0) {
        $ligne = mysql_fetch_array($resultat);
	    if ($ligne[0]!=null)
		    echanger_partie($SID,$ligne[1],$ligne[0],0);
	}
    $globals->db->query("unlock sondage.parties");
}

/** monter une question d'un niveau
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @return rien
 */
function monter_question($SID,$PID,$QID) {
	$globals->db->query("lock sondage.questions");//lock nécessaire pour récupérer max(ordre)
    $resultat = mysql_query_p("select max(s1.ordre),s2.ordre ".
    "from sondage.questions as s1,sondage.questions as s2 ".
    "where s2.ids=$SID and s2.idp=$PID and s2.idq=$QID and ".
    "s1.ids=$SID and s1.idp=$PID and s1.ordre<s2.ordre group by s2.ordre");
	if (mysql_num_rows($resultat)>0) {
        $ligne = mysql_fetch_array($resultat);
	    if ($ligne[0]!=null)
		    echanger_question($SID,$PID,$ligne[1],$ligne[0],0);
	}
    $globals->db->query("unlock sondage.questions");
}

/** monter un choix de réponse d'un niveau
 * @param $SID l'id du sondage
 * @param $PID l'id de la partie
 * @param $QID l'id de la question
 * @param $RID l'id du choix
 * @return rien
 */
function monter_reponse($SID,$PID,$QID,$RID) {
	$globals->db->query("lock sondage.choix");//lock nécessaire pour récupérer max(ordre)
    $resultat = mysql_query_p("select max(s1.ordre),s2.ordre ".
    "from sondage.choix as s1,sondage.choix as s2 ".
    "where s2.ids=$SID and s2.idp=$PID and s2.idq=$QID and s2.idr=$RID and ".
    "s1.ids=$SID and s1.idp=$PID and s1.idq=$QID and s1.ordre<s2.ordre group by s2.ordre");
	if (mysql_num_rows($resultat)>0) {
        $ligne = mysql_fetch_array($resultat);                                  
	    if ($ligne[0]!=null)                                                    
		    echanger_reponse($SID,$PID,$QID,$ligne[1],$ligne[0],0);
	}
    $globals->db->query("unlock sondage.choix");
} 
								
?>
