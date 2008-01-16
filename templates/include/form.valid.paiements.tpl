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


<tr class="pair">
  <td class="titre">Paiement&nbsp;:</td>
  <td>{$valid->titre}</td>
</tr>
<tr class="pair">
  <td class="titre">Site web&nbsp;:</td>
  <td>
    <a href="{$valid->site}">{$valid->site}</a>
  </td>
</tr>
<tr class="pair">
  <td class="titre">Montant&nbsp;:</td>
  <td>
    {$valid->montant} ({$valid->montant_min} &lt; x &lt; {$valid->montant_max})
  </td>
</tr>
<tr class="pair">
  <td class="titre">Contact&nbsp;:</td>
  <td>
    {$valid->bestalias}@polytechnique.org
  </td>
</tr>
<tr class="pair">
  <td class="titre">Message de confirmation&nbsp;:</td>
  <td><pre>{$valid->msg_reponse|wordwrap:"72"}</pre></td>
</tr>
{if $valid->asso_id}
<tr class="pair">
  <td class="titre">Lié à l'asso&nbsp;:</td>
  <td>{$valid->asso}</td>
</tr>
{if $valid->evt}
<tr class="pair">
  <td class="titre">événement&nbsp;:</td>
  <td>{$valid->evt_intitule}</td>
</tr>
{/if}
{/if}
{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
