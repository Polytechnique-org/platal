{* $Id: mescontacts_ldif.tpl,v 1.2 2004-02-15 15:45:29 x2000habouzit Exp $ *}
{* http://developer.kde.org/documentation/library/cvs-api/kabc/html/ldifconverter_8cpp-source.html *}
{foreach item=c from=$contacts}
{******************************************************************************}
{* DONNEES PERSOS                                                             *}
{******************************************************************************}
{if $c.epouse}
dn: cn={"`$c.prenom` `c.epouse` (`$c.nom`)"|utf8},mail={$c.username}@polytechnique.org
cn: {"`$c.prenom` `c.epouse`"|utf8}
{else}
dn: cn={"`$c.prenom` `$c.nom`"|utf8},mail={$c.username}@polytechnique.org
cn: {"`$c.prenom` `$c.nom`"|utf8}
{/if}
sn: {$c.nom}
givenname: {$c.prenom|utf8}
uid: {$c.username}
mail: {$c.username}@polytechnique.org
{if $c.alias}
mailalternateaddress: {$c.alias}@polytechnique.org
mozillasecondemail: {$c.alias}@polytechnique.org
{/if}
{if $c.mobile}
mobile: {$c.mobile|utf8}
cellphone: {$c.mobile|utf8}
{/if}
{if $vcard.web}
homeurl:: {$vcard.web|ldif_format}
workurl:: {$vcard.web|ldif_format}
{/if}
{******************************************************************************}
{* ENTREPRISE/WORK                                                            *}
{******************************************************************************}
{if $c.entreprise}
o:: {$c.entreprise|ldif_format}
organization:: {$c.entreprise|ldif_format}
organizationname:: {$c.entreprise|ldif_format}
{if $c.fonction}
ou:: {$c.fonction|ldif_format}
{/if}
{if $c.poste}
title:: {$c.poste|ldif_format}
{/if}
{if $c.tel}
telephonenumber: {$c.tel|utf8}
{/if}
{if $c.cp}
postalcode: {$c.cp|utf8}
{/if}
{if $c.pays}
countryname:: {$c.pays|ldif_format}
c:: {$c.pays|ldif_format}
{/if}
{if $c.ville}
l:: {$c.ville|ldif_format}
{/if}
{if $c.name}
st:: {$c.name|ldif_format}
{/if}
{if $c.adr_fmt}
streetaddress:: {$c.adr_fmt|ldif_format}
{/if}
{if $c.adr0}
postaladdress:: {$c.adr0|ldif_format}
{/if}
{if $c.adr1}
mozillapostaladdress2:: {$c.adr1|ldif_format}
{/if}
{if $c.adr2}
mozillapostaladdress2:: {$c.adr2|ldif_format}
{/if}
{if $c.fax}
facsimiletelephonenumber: {$c.faxx|utf8}
{/if}
{/if}
{******************************************************************************}
{* ADDRESSE PERSO                                                             *}
{******************************************************************************}
{if $c.home.adr_fmt}
streethomeaddress:: {$c.home.adr_fmt|ldif_format}
{/if}
{if $c.home.courrier}
{if $c.home.adr0}
homepostaladdress:: {$c.home.adr0|ldif_format}
{/if}
{if $c.home.adr1}
mozillahomepostaladdress2:: {$c.home.adr1|ldif_format}
{/if}
{if $c.home.adr2}
mozillahomepostaladdress2:: {$c.home.adr2|ldif_format}
{/if}
{if $c.home.cp}
mozillahomepostalcode: {$c.home.cp|utf8}
{/if}
{/if}
{if $c.home.ville}
mozillahomelocalityname:: {$c.home.ville|ldif_format}
{/if}
{if $c.home.name}
mozillahomestate:: {$c.home.name|ldif_format}
{/if}
{if $c.home.pays}
mozillahomecountryname:: {$c.home.name|ldif_format}
{/if}
{******************************************************************************}
{* ADDRESSES PERSO                                                            *}
{******************************************************************************}
description:: {"(X`$c.promo`)\n`$c.libre`"|ldif_format}
modifytimestamp: {$c.date|date_format:"%Y%m%dT000000Z"}
objectclass: top
objectclass: person
objectclass: organizationalPerson

{/foreach}
{* vim:set et sw=2 sts=2 sws=2: *}
