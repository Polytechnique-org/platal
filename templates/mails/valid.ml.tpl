{* $Id: valid.ml.tpl,v 1.1 2004-02-07 18:04:56 x2000habouzit Exp $ *}
{config_load file="mails.conf" section="valid_ml"}
{subject text="[Polytechnique.org/LISTES] Demande de la liste $alias par $username"}
{from full=#from#}
{to addr="$username@polytechnique.org"}
{cc full=#cc#}
{if $answer eq "yes"}
Cher(e) camarade,

  La mailing list {$alias} que tu avais demandée vient d'être créée.
{if $motif}
Informations complémentaires:
{$motif}
{/if}

Cordialement,
L'équipe X.org
{elseif $answer eq 'no'}
Cher(e) camarade,

  La demande que tu avais faite pour la mailing list {$alias} a été refusée.
La raison de ce refus est :
{$motif}

Cordialement,
L'équipe X.org
{/if}
{* vim:set nocindent noautoindent textwidth=0: *}
