{* $Id: valid.epouses.tpl,v 1.1 2004-02-07 15:47:27 x2000habouzit Exp $ *}
{config_load file="mails.conf" section="valid_epouses"}
{subject text="[Polytechnique.org/EPOUSE] Changement de nom de mariage de $username"}
{from full=#from#}
{to addr="$username@polytechnique.org"}
{cc full=#cc#}
{if $answer eq "yes"}
Chère camarade,

  La demande de changement de nom de mariage que tu as demandée vient d'être effectuée.

{if $oldepouse}  Les alias {$oldepouse}@polytechnique.org et {$oldepouse}@m4x.org ont été supprimés.
{/if}
  De plus, les alias {$alias}@polytechnique.org et {$alias}@m4x.org ont été créés.

Cordialement,
L'équipe X.org
{elseif $answer eq 'no'}
Chère camarade,

  La demande de changement de nom de mariage que tu avais faite a été refusée.
{if $smarty.request.motif}
La raison de ce refus est :
{$smarty.request.motif}
{/if}

Cordialement,
L'équipe X.org
{/if}
{* vim:set nocindent noautoindent textwidth=0: *}
