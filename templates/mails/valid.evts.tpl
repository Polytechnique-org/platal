{* $Id: valid.evts.tpl,v 1.1 2004-02-08 12:21:33 x2000habouzit Exp $ *}
{config_load file="mails.conf" section="valid_evts"}
{subject text="[Polytechnique.org/EVENEMENTS] Proposition d'événement"}
{from full=#from#}
{to addr="$username@polytechnique.org"}
{cc full=#cc#}
{if $answer eq "yes"}
Cher(e) camarade,

  L'annonce que tu avais proposée ({$titre|strip_tags}) vient d'être validée.

Cordialement,
L'équipe X.org
{elseif $answer eq 'no'}

Cher(e) camarade,

  L'annonce que tu avais proposée ({$titre|strip_tags}) a été refusée.

Cordialement,
L'équipe X.org
{/if}
{* vim:set nocindent noautoindent textwidth=0: *}
