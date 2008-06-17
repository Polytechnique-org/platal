{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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
{iterate from=$users item=vcard}
BEGIN:VCARD
VERSION:3.0
{if $vcard.nom_usage}
FN:{$vcard.prenom|vcard_enc} {$vcard.nom_usage|vcard_enc} ({$vcard.nom|vcard_enc})
{else}
FN:{$vcard.prenom|vcard_enc} {$vcard.nom|vcard_enc}
{/if}
N:{$vcard.nom|vcard_enc};{$vcard.prenom|vcard_enc};{$vcard.nom_usage|vcard_enc};;
{if $vcard.nickname}
NICKNAME:{$vcard.nickname|vcard_enc}
{/if}
EMAIL;TYPE=internet,pref:{$vcard.bestalias}@{#globals.mail.domain#}
EMAIL;TYPE=internet:{$vcard.bestalias}@{#globals.mail.domain2#}
{if $vcard.bestalias neq $vcard.forlife}
EMAIL;TYPE=internet:{$vcard.forlife}@{#globals.mail.domain#}
EMAIL;TYPE=internet:{$vcard.forlife}@{#globals.mail.domain2#}
{/if}
{if $vcard.virtualalias}
EMAIL;TYPE=internet:{$vcard.virtualalias}
{/if}
{if $vcard.mobile}
TEL;TYPE=cell:{$vcard.mobile|vcard_enc}
{/if}
{if $vcard.adr_pro}
{if $vcard.adr_pro[0].entreprise}
ORG:{$vcard.adr_pro[0].entreprise|vcard_enc}
{/if}
{if $vcard.adr_pro[0].poste}
TITLE:{$vcard.adr_pro[0].poste|vcard_enc}
{/if}
{if $vcard.adr_pro[0].fonction}
ROLE:{$vcard.adr_pro[0].fonction|vcard_enc}
{/if}
{if $vcard.adr_pro[0].tel}
TEL;TYPE=work:{$vcard.adr_pro[0].tel|vcard_enc}
{/if}
{if $vcard.adr_pro[0].fax}
FAX;TYPE=work:{$vcard.adr_pro[0].fax|vcard_enc}
{/if}
ADR;TYPE=work:{format_adr adr=$vcard.adr_pro[0]}
{/if}
{foreach item=adr from=$vcard.adr}
ADR;TYPE=home{if $adr.courier},postal{/if}:{format_adr adr=$adr}
{foreach item=tel from=$adr.tels}
{if $tel.tel}
{if $tel.tel_type neq 'fax'}TEL{else}FAX{/if};TYPE=home:{$tel.tel}
{/if}
{/foreach}
{/foreach}
{foreach from=$vcard.networking item=nw}
{if $nw.filter eq 'web'}
URL:{$nw.address}
{/if}
{/foreach}
{if strlen(trim($vcard.freetext)) == 0}
NOTE:(X{$vcard.promo})
{else}
NOTE:(X{$vcard.promo})\n{$vcard.freetext|vcard_enc}
{/if}
{if $vcard.section}
X-SECTION:{$vcard.section}
{/if}
{if $vcard.binets_vcardjoin}
X-BINETS:{$vcard.binets_vcardjoin}
{/if}
{if $vcard.gpxs_vcardjoin}
X-GROUPS:{$vcard.gpxs_vcardjoin}
{/if}
{if $vcard.photo}
PHOTO;ENCODING=b;TYPE={$vcard.photo.attachmime}:{$vcard.photo.attach|base64_encode|vcard_enc}
{/if}
SORT-STRING:{$vcard.nom|vcard_enc}
REV:{$vcard.date|date_format:"%Y%m%dT000000Z"}
END:VCARD{"\n"}
{/iterate}
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
