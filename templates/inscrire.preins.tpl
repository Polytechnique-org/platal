{* $Id: inscrire.preins.tpl,v 1.2 2004-08-26 14:44:43 x2000habouzit Exp $ *}

<div class="rubrique">
  Pré-inscription réussie
</div>

<p>
La pré-inscription que tu viens de soumettre a été enregistrée.
</p>
{dynamic}
<p>
Les instructions te permettant notamment d'activer ton e-mail
<strong>{$mailorg}@polytechnique.org</strong>, ainsi que ton mot de passe pour
acc&eacute;der au site viennent de t'être envoyés à l'adresse
<strong>{$smarty.request.email}</strong>.
</p>
<p>
Tu n'as que quelques jours pour suivre ces instructions après quoi la pré-inscription
est effacée automatiquement de nos bases et il faut tout recommencer. Si tu as soumis
plusieurs pré-inscriptions, seul le dernier e-mail reçu est valable, les précédents
ne servant plus.
</p>
<p>
Si tu ne reçois rien, vérifie bien l'adresse <strong>{$smarty.request.email}</strong>.
</p>
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
