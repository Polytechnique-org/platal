{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2007 Polytechnique.org                             *}
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

{if $valid->m_relance neq '0000-00-00'}
{if $valid->m_relance neq ''} {* provided for backward compatibility, may disappear in the future *}
  <tr class="pair">
    <td class="titre">Dernière relance le :</td>
    <td>{$valid->m_relance|date_format}</td>
  </tr>
{/if}
{/if}
<tr class="pair">
  <td class="titre">Marketing sur</td>
  <td><a href='marketing/private/{$valid->m_id}'>{$valid->m_prenom} {$valid->m_nom} ({$valid->m_promo})</a></td>
</tr>
<tr class="pair">
  <td class="titre">Mail deviné</td>
  <td>{$valid->m_email}</td>
</tr>
<tr class="pair">
  <td class="titre">Envoi de mail ...</td>
  <td>{if $valid->perso}perso{else}par poly.org{/if}</td>
</tr>

{* vim:set et sw=2 sts=2 sws=2: *}
