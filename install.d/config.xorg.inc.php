<?php
/* $Id: config.xorg.inc.php,v 1.1 2004-01-25 17:25:36 x2000habouzit Exp $ */

/* URL de la racine pour les mails contenant des URL (pas de slash final!) */
if (!isset($baseurl)) $baseurl="http://dev.m4x.org";

/* pour empêcher les mises à jour de la base de donnée depuis le site */
$no_update_bd = false;
/* les parametres pour se connecter à la BDD */
$globals->dbhost='localhost';
$globals->dbdb = 'x4dat';
$globals->dbuser =  $no_update_bd ? 'webro' : 'web';
$globals->dbpwd="*******";
$globals->root="...";
$globals->libroot="...";

/* les parametres pour se connecter au serveur NNTP */
if (!isset($news_server)) $news_server="localhost";
if (!isset($news_port)) $news_port=119;
if (!isset($news_auth_pass)) $news_auth_pass="***";

/* acces a la page marketing */
$marketing_admin = array();

/* définir sur le site de dev */
if (!isset($site_dev)) $site_dev=true;

$globals->spoolroot="***";

// legacy
$dbhost = $globals->dbhost;  // recherche.php : 303
$dbuser = $globals->dbuser;
$dbpwd = $globals->dbpwd;
$xdat = $globals->dbdb;
?>
