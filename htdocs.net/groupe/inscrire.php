<?php
require 'xnet.inc.php';

new_page('xnet/groupe/inscrire.tpl', AUTH_MDP);
$page->useMenu();
$page->setType($globals->asso('cat'));
$page->assign('asso', $globals->asso());
$page->assign('admin', may_update());

if (!$globals->asso('inscriptible'))
	$page->kill("Il n'est pas possible de s'inscire en ligne à ce groupe. Essaie de joindre le contact indiqué sur la page de présentation.");

if (Env::has('u') && may_update()) {
    $u   = Env::get('u');
    $res = $globals->xdb->query("SELECT nom, prenom, promo, user_id FROM auth_user_md5 AS u INNER JOIN aliases AS al ON (al.id = u.user_id AND al.type != 'liste') WHERE al.alias = {?}", $u);

    if (list($nom, $prenom, $promo, $uid) = $res->fetchOneRow())
    {
        $res = $globals->xdb->query("SELECT count(*) FROM  groupex.membres AS m
                                     INNER JOIN  aliases  AS a ON ( m.uid = a.id AND a.type != 'homonyme' ) WHERE a.alias = {?} AND m.asso_id = {?}", $u, $globals->asso('id'));
        $n   = $res->fetchOneCell();
        if ($n)
        {
            $page->trig_run("$prenom $nom est déjà membre du groupe !");
        }
        elseif (Env::has('accept'))
        {
            $globals->xdb->execute("INSERT INTO groupex.membres VALUES ({?}, {?}, 'membre', 'X', NULL, NULL, NULL, NULL)", $globals->asso('id'), $uid);
            require_once 'diogenes/diogenes.hermes.inc.php';
            $mailer = new HermesMailer();
            $mailer->addTo("$u@polytechnique.org");
            $mailer->setFrom('"'.Session::get('prenom').' '.Session::get('nom').'" <'.Session::get('forlife').'@polytechnique.org>');
            $mailer->setSubject('['.$globals->asso('nom').'] Demande d\'inscription');
            $message = "Cher Camarade,\n"
                     . "\n"
                     . "  Suite à ta demande d'adhésion à ".$globals->asso('nom').",\n"
                     . "j'ai le plaisir de t'annoncer que ton inscription a bien été validée !\n"
                     . "\n"
                     . "Bien cordialement,\n"
                     . "{$_SESSION["prenom"]} {$_SESSION["nom"]}.";
            $mailer->setTxtBody($message);
            $mailer->send();
            $page->kill("$prenom $nom a bien été inscrit");
        }
        elseif (Env::has('refuse'))
        {
            require_once 'diogenes/diogenes.hermes.inc.php';
            $mailer = new HermesMailer();
            $mailer->addTo("$u@polytechnique.org");
            $mailer->setFrom('"'.Session::get('prenom').' '.Session::get('nom').'" <'.Session::get('forlife').'@polytechnique.org>');
            $mailer->setSubject('['.$globals->asso('nom').'] Demande d\'inscription annulée');
            $mailer->setTxtBody(Env::get('motif'));
            $mailer->send();
            $page->kill("la demande $prenom $nom a bien été refusée");
        }
        else
        {
            $page->assign('show_form', true);
            $page->gassign('prenom');
            $page->gassign('nom');
            $page->gassign('promo');
            $page->gassign('uid');
        }
    }
    else
    {
        $page->kill("utilisateur invalide");
    }
} elseif (is_member()) {
    $page->kill("tu es déjà membre !");
} elseif (Post::has('inscrire')) {

    $res = $globals->xdb->query('SELECT  IF(m.email IS NULL, CONCAT(al.alias,"@polytechnique.org"), m.email)
                                   FROM  groupex.membres AS m
                             INNER JOIN  aliases         AS al ON (al.type = "a_vie" AND al.id = m.uid)
                                  WHERE  perms="admin" AND m.asso_id = {?}', $globals->asso('id'));
    $emails = $res->fetchColumn();
    $to     = implode(',', $emails);

    $append = "\n"
            . "-- \n"
            . "Ce message a été envoyé suite à la demande d'inscription de\n"
            . Session::get('prenom').' '.Session::get('nom').' (X'.Session::get('promo').")\n"
            . "Via le site www.polytechnique.net. Tu peux choisir de valider ou\n"
            . "de refuser sa demande d'inscription depuis la page :\n"
            . "http://www.polytechnique.net/".$globals->asso("diminutif")."/inscrire.php?u=".Session::get('forlife')."\n"
            . "\n"
            . "En cas de problème, contacter l'équipe de Polytechnique.org\n"
            . "à l'adresse : support@polytechnique.org\n";
    
    if (!$to) {
    	$to = $globals->asso("mail").", support@polytechnique.org";
    	$append = "\n-- \nLe groupe ".$globals->asso("nom")." n'a pas d'administrateur, l'équipe de Polytechnique.org a été prévenue et va rapidement résoudre ce problème.\n";
    }
    
    require_once 'diogenes/diogenes.hermes.inc.php';
    $mailer = new HermesMailer();
    $mailer->addTo($to);
    $mailer->setFrom('"'.Session::get('prenom').' '.Session::get('nom').'" <'.Session::get('forlife').'@polytechnique.org>');
    $mailer->setSubject('['.$globals->asso('nom').'] Demande d\'inscription');
    $mailer->setTxtBody(Post::get('message').$append);
    $mailer->send();
}


$page->run();

?>
