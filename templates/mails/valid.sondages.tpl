{* $Id: valid.sondages.tpl,v 1.1 2004-02-08 12:38:27 x2000habouzit Exp $ *}
{config_load file="mails.conf" section="valid_sondages"}
{subject text="[Polytechnique.org/SONDAGE] Demande de validation du sondage $titre par $username"}
{from full=#from#}
{to addr="$username@polytechnique.org"}
{cc full=#cc#}
{if $answer eq "yes"}
Cher(e) camarade,

  Le sondage {$titre} que tu as composé vient d'être validé.
  Il ne te reste plus qu'à transmettre aux sondés l'adresse où ils pourront voter. Cette adresse est : https://www.polytechnique.org/sondages/questionnaire.php?alias={$alias|escape:'url'}

Cordialement,
L'équipe X.org
{elseif $answer eq 'no'}

Cher(e) camarade,

  Le sondage $titre que tu avais proposé a été refusé.
La raison de ce refus est :
{$smarty.request.motif}

Cordialement,
L'équipe X.org
{/if}
{* vim:set nocindent noautoindent textwidth=0: *}
