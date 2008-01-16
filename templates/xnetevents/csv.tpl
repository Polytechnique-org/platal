{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
{*  http://opensource.polytechnique.org/                                  *}
{*                                                                        *}
{*  This program is free software, you can redistribute it and/or modify  *}
{*  it under the terms of the GNU General Public License as published by  *}
{*  the Free Software Foundation, either version 2 of the License; or     *}
{*  (at your option) any later version.                                   *}
{*                                                                        *}
{*  This program is distributed in the hope that it will be useful,       *}
{*  but WITHOUT ANY WARRANTY, without even the implied warranty of        *}
{*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *}
{*  GNU General Public License for more details.                          *}
{*                                                                        *}
{*  You should have received a copy of the GNU General Public License     *}
{*  along with this program, if not; write to the Free Software           *}
{*  Foundation, Inc.;                                                     *}
{*  59 Temple Place, Suite 330; Boston, MA  02111-1307  USA               *}
{*                                                                        *}
{**************************************************************************}
Nom;Prénom;Promotion{if $tout}{foreach from=$moments item=m};{$m.titre}{/foreach}{if $admin && $money};À payer;{if
$telepayment}Télépaiement;Liquide/Chèque;{/if}Payé{/if}{else};Nombre{/if}

{if $participants}
{foreach from=$participants item=m}

;
{$m.nom};{$m.prenom};{$m.promo}{if $tout}{foreach from=$moments item=i};{$m[$i.item_id]}{/foreach}{if $admin &&
$money};{$m.montant};{if $telepayment}{$m.telepayment};{$m.adminpaid};{/if}{$m.paid}{/if}{else};{$m.nb}{/if}

{/foreach}
;
{/if}
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
