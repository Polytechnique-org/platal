{* $Id: vcard.tpl,v 1.1 2004-01-27 13:41:41 x2000habouzit Exp $ *}
BEGIN:VCARD
VERSION:3.0
{if $vcard.epouse}
FN;ENCODING=QUOTED-PRINTABLE:{"`$vcard.prenom` `$vcard.epouse` (`$vcard.nom`)"|qp_enc}
{else}
FN;ENCODING=QUOTED-PRINTABLE:{"`$vcard.prenom` `$vcard.nom`"|qp_enc}
{/if}
N;ENCODING=QUOTED-PRINTABLE:{$vcard.nom|qp_enc};{$vcard.prenom|qp_enc};{$vcard.epouse|qp_enc};;
EMAIL;TYPE=internet:{$vcard.username}@polytechnique.org
{if $vcard.alias}
EMAIL;TYPE=internet:{$vcard.alias}@polytechnique.org
{/if}
{if $vcard.mobile}
TEL;TYPE=cell;ENCODING=QUOTED-PRINTABLE:{$vcard.mobile|qp_enc}
{/if}
{if $work}
{if $work.entreprise}
ORG;ENCODING=QUOTED-PRINTABLE:{$work.entreprise|qp_enc}
{/if}
{if $work.poste}
TITLE;ENCODING=QUOTED-PRINTABLE:{$vcard.poste|qp_enc}
{/if}
{if $work.fonction}
ROLE;ENCODING=QUOTED-PRINTABLE:{$work.fonction|qp_enc}
{/if}
{if $work.tel}
TEL;TYPE=work;ENCODING=QUOTED-PRINTABLE:{$work.tel|qp_enc}
{/if}
{if $work.fax}
FAX;TYPE=work;ENCODING=QUOTED-PRINTABLE:{$work.fax|qp_enc}
{/if}
ADR;TYPE=work;ENCODING=QUOTED-PRINTABLE:{format_adr adr=$work}
{/if}
{foreach item=adr from=$home}
{if $adr.tel}
TEL;TYPE=home;ENCODING=QUOTED-PRINTABLE:{$adr.tel|qp_enc}
{/if}
{if $adr.fax}
FAX;TYPE=home;ENCODING=QUOTED-PRINTABLE:{$adr.fax|qp_enc}
{/if}
ADR;TYPE=home{if $adr.courier},postal{/if};ENCODING=QUOTED-PRINTABLE:{format_adr adr=$adr}
{/foreach}
{if $vcard.web}
URL;ENCODING=QUOTED-PRINTABLE:{$vcard.web|qp_enc}
{/if}
NOTE;ENCODING=QUOTED-PRINTABLE:{"(X`$vcard.promo`)\n`$vcard.libre`"|qp_enc}
SORT-STRING;ENCODING=QUOTED-PRINTABLE:{$vcard.nom|qp_enc}
REV:{$smarty.now|date_format:"%Y-%m-%dT%TZ"}
END:VCARD

{* vim:set et sw=2 sts=2 sws=2: *}
