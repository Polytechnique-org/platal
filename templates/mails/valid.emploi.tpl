{* $Id: valid.emploi.tpl,v 1.1 2004-02-07 14:42:07 x2000habouzit Exp $ *}
{config_load file="mails.conf" section="valid_emploi"}
{subject text="[Polytechnique.org/EMPLOI] Annonce emploi $entreprise"}
{from full=#from#}
{cc full=#cc#}
{if $answer eq "yes"}
Bonjour,

L'annonce « {$titre} » a été acceptée par les modérateurs. Elle apparaîtra dans le forum emploi du site.

Nous vous remercions d'avoir proposé cette annonce

Cordialement,
L'équipe Polytechnique.org
{elseif $answer eq 'no'}
Bonjour,

L'annonce « {$titre} » a été refusée par les modérateurs.

Cordialement,
L'équipe Polytechnique.org
{/if}
{* vim:set nocindent noautoindent textwidth=0: *}
