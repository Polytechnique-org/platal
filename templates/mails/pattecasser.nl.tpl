{* $Id: pattecasser.nl.tpl,v 1.1 2004-02-11 13:57:06 x2000habouzit Exp $ *}
{config_load file="mails.conf" section="pattecassee_nl"}
{subject text="Une de tes adresses de redirection Polytechnique.org ne marche plus !!"}
{from full=#from#}
{to addr=$dest}

  Bonjour !
	
  Nous t'écrivons car lors de l'envoi de la lettre d'information mensuelle de Polytechnique.org à ton adresse polytechnicienne {$dest}@polytechnique.org, l'adresse {$email}, sur laquelle tu rediriges ton courrier, ne fonctionnait pas.
  Estimant que cette information serait susceptible de t'intéresser, nous avons préféré t'en informer. Il n'est pas impossible qu'il ne s'agisse que d'une panne temporaire.
  Si tu souhaites changer la liste des adresses sur lesquelles tu reçois le courrier qui t'es envoyé à ton adresse polytechnicienne, il te suffit de te rendre sur la page :
  https://www.polytechnique.org/emails.php
  
  A bientôt sur Polytechnique.org !
  L'équipe d'administration <support@polytechnique.org>
  
  PS : si jamais tu ne disposes plus du mot de passe te permettant d'accéder au site, rends toi sur la page https://www.polytechnique.org/recovery.php ; elle te permettra de créer un nouveau mot de passe après avoir rentré ton login ({$dest}) et ta date de naissance !";
  
{* vim:set nocindent noautoindent textwidth=0: *}
