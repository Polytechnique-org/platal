{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
{*  http://opensource.polytechnique.org/                                  *}
{*                                                                        *}
{*  This program is free software; you can redistribute it and/or modify  *}
{*  it under the terms of the GNU General Public License as published by  *}
{*  the Free Software Foundation; either version 2 of the License, or     *}
{*  (at your option) any later version.                                   *}
{*                                                                        *}
{*  This program is distributed in the hope that it will be useful,       *}
{*  but WITHOUT ANY WARRANTY; without even the implied warranty of        *}
{*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *}
{*  GNU General Public License for more details.                          *}
{*                                                                        *}
{*  You should have received a copy of the GNU General Public License     *}
{*  along with this program; if not, write to the Free Software           *}
{*  Foundation, Inc.,                                                     *}
{*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}
BEGIN:VCARD
VERSION:3.0
{if $vcard.nom_usage}
FN;ENCODING=QUOTED-PRINTABLE:{"`$vcard.prenom` `$vcard.nom_usage` (`$vcard.nom`)"|qp_enc}
{else}
FN;ENCODING=QUOTED-PRINTABLE:{"`$vcard.prenom` `$vcard.nom`"|qp_enc}
{/if}
N;ENCODING=QUOTED-PRINTABLE:{$vcard.nom|qp_enc};{$vcard.prenom|qp_enc};{$vcard.nom_usage|qp_enc};;
{if $vcard.nickname}
NICKNAME;ENCODING=QUOTED-PRINTABLE:{$vcard.nickname}
{/if}
EMAIL;TYPE=internet:{$vcard.bestalias}@{#globals.mail.domain#}
{if $vcard.bestalias neq $vcard.forlife}
EMAIL;TYPE=internet:{$vcard.forlife}@{#globals.mail.domain#}
{/if}
{if $vcard.virtualalias}
EMAIL;TYPE=internet:{$vcard.virtualalias}
{/if}
{if $vcard.mobile}
TEL;TYPE=cell;ENCODING=QUOTED-PRINTABLE:{$vcard.mobile|qp_enc}
{/if}
{if $vcard.adr_pro}
{if $vcard.adr_pro[0].entreprise}
ORG;ENCODING=QUOTED-PRINTABLE:{$vcard.adr_pro[0].entreprise|qp_enc}
{/if}
{if $vcard.adr_pro[0].poste}
TITLE;ENCODING=QUOTED-PRINTABLE:{$vcard.adr_pro[0].poste|qp_enc}
{/if}
{if $vcard.adr_pro[0].fonction}
ROLE;ENCODING=QUOTED-PRINTABLE:{$vcard.adr_pro[0].fonction|qp_enc}
{/if}
{if $vcard.adr_pro[0].tel}
TEL;TYPE=work;ENCODING=QUOTED-PRINTABLE:{$vcard.adr_pro[0].tel|qp_enc}
{/if}
{if $vcard.adr_pro[0].fax}
FAX;TYPE=work;ENCODING=QUOTED-PRINTABLE:{$vcard.adr_pro[0].fax|qp_enc}
{/if}
ADR;TYPE=work;ENCODING=QUOTED-PRINTABLE:{format_adr adr=$vcard.adr_pro[0]}
{/if}
{foreach item=adr from=$vcard.adr}
ADR;TYPE=home{if $adr.courier},postal{/if};ENCODING=QUOTED-PRINTABLE:{format_adr adr=$adr}
{foreach item=tel from=$adr.tels}
{if $tel.tel}
{if $tel.tel_type neq 'Fax'}TEL{else}FAX{/if};TYPE=home;ENCODING=QUOTED-PRINTABLE:{$tel.tel|qp_enc}
{/if}
{/foreach}
{/foreach}
{if $vcard.web}
URL;ENCODING=QUOTED-PRINTABLE:{$vcard.web|qp_enc}
{/if}
{if strlen(trim($vcard.freetext)) == 0}
NOTE;ENCODING=QUOTED-PRINTABLE:{"(X`$vcard.promo`)"|qp_enc}
{else}
NOTE;ENCODING=QUOTED-PRINTABLE:{"(X`$vcard.promo`)\n`$vcard.freetext`"|qp_enc}
{/if}
{if $vcard.photo}
PHOTO;BASE64:{$vcard.photo|base64_encode}
{/if}
SORT-STRING;ENCODING=QUOTED-PRINTABLE:{$vcard.nom|qp_enc}
REV:{$vcard.date|date_format:"%Y%m%dT000000Z"}
END:VCARD
{* vim:set et sw=2 sts=2 sws=2: *}
