{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: mescontacts_ldif.tpl,v 1.3 2004-08-31 11:25:39 x2000habouzit Exp $
 ***************************************************************************}

{foreach item=c from=$contacts}
{******************************************************************************}
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
{******************************************************************************}
description:: {"(X`$c.promo`)\n`$c.libre`"|ldif_format}
modifytimestamp: {$c.date|date_format:"%Y%m%dT000000Z"}
objectclass: top
objectclass: person
objectclass: organizationalPerson

{/foreach}
{* vim:set et sw=2 sts=2 sws=2: *}
