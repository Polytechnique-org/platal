{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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

{config_load file="mails.conf" section="inscrire"}
{if $mail_part eq 'head'}
{subject text="$subj"}
{from full=#from#}
{from full=#cc#}
{to addr="$lemail"}
{elseif $mail_part eq 'text'}
Bonjour,

Un homonyme vient de s'inscrire. La politique de Polytechnique.org est de fournir des adresses email devinables, nous ne pouvons donc pas
conserver ton alias {$emailXorg} qui correspond maintenant à deux personnes.

Tu gardes tout de même l'usage de cet alias pour un mois encore à compter de ce jour.

Lorsque cet alias sera désactivé, l'adresse {$emailXorg}@polytechnique.org renverra vers un robot qui indiquera qu'il y a plusieurs personnes portant le même nom ; cela évite que l'un des homonymes reçoive des courriels destinés à l'autre.

Pour te connecter au site, tu pourras utiliser comme identifiant n'importe lequel de tes autres alias : {$als}.
Commence dès aujourd'hui à communiquer à tes correspondants la nouvelle adresse que tu comptes utiliser !

En nous excusant pour le désagrément occasionné,
{include file="signature.mail.tpl"}
{/if}
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
