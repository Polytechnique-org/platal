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

<tr class="pair">
  <td class="titre">Récupération de</td>
  <td><a href='admin/user/{$valid->m_forlife}'>{$valid->m_prenom} {$valid->m_nom} ({$valid->m_promo})</a></td>
</tr>
<tr class="pair">
  <td class="titre">Mail proposé</td>
  <td>{$valid->m_email}</td>
</tr>
{if $valid->m_comment}
<tr class="pair">
  <td class="titre">Commentaire</td>
  <td>{$valid->m_comment|nl2br}</td>
</tr>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
