{* $Id: marketing.relance.tpl,v 1.1 2004-07-17 14:16:47 x2000habouzit Exp $ *}
{config_load file="mails.conf" section="valid_alias"}
{subject text="$subj"}
{from full=#from#}
{to addr="$lemail"}
Bonjour,

Il y a quelques temps, le {$fdate}, tu as commencé ton inscription à Polytechnique.org ! Tu n'as toutefois pas tout à fait terminé cette inscription, aussi nous nous permettons de te renvoyer cet email pour te rappeler tes paramètres de connexion, au cas où tu souhaiterais terminer cette inscription, et accéder à l'ensemble des services que nous offrons aux {$nbdix} Polytechniciens déjà inscrits (email à vie, annuaire en ligne, etc...).

UN SIMPLE CLIC sur le lien ci-dessous et ton compte sera activé !

Après activation, tes paramètres seront :

login        : {$lusername}
mot de passe : {$nveau_pass}

(ceci annule les paramètres envoyés par le mail initial)

Rends-toi sur la page web suivante afin d'activer ta pré-inscription, et de changer ton mot de passe en quelque chose de plus facile à mémoriser :

{$baseurl}/step4.php?ref={$lins_id}

Si en cliquant dessus tu n'y arrives pas, copie intégralement l'adresse dans la barre de ton navigateur.

En cas de difficulté, nous sommes bien entendu à ton entière disposition !

Bien cordialement,
Polytechnique.org
"Le portail des élèves & anciens élèves de l'Ecole polytechnique"

{* vim:set nocindent noautoindent textwidth=0: *}
