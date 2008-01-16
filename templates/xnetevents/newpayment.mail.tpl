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

{config_load file="mails.conf" section="payment_ready"}
{if $mail_part eq 'head'}
{from full=#from#}
{to addr=$to}
{subject text="[`$asso`] Paiement activé"}
{elseif $mail_part eq 'wiki'}
Cher {$prenom},

Nous t'écrivons pour t'informer que le télépaiement associé à l'événement '''{$evt}''' du groupe {$asso} vient d'être activé. Tu peux donc finaliser ton inscription.

Pour ceci, va simplement sur [[http://www.polytechnique.net/{$diminutif}/payment/{$payment}?montant={$topay}|cette page]].

Cordialement,\\
L'Equipe de Polytechnique.org
{/if}
{* vim:set et sw=2 sts=2 sws=2: *}
