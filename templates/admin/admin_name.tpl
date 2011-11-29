{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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

<h1>Gestion des noms{if t($hruid)} de {profile user=$uid promo=true directory=false}{/if}</h1>

{if t($hruid)}
<form method="post" action="{$platal->ns}admin/name/{$hruid}">
  <table class="bicol">
    <tr>
      <th></th>
      <th>Version actuelle</th>
      <th>Version suggérée</th>
    </tr>
    {foreach from=$names item=name key=type}
    <tr>
      <th>{$name_types.$type}</th>
      <td><input type="text" size="40" name="{$type}" value="{$name.value}" {if $name.different}class="warning"{/if} /></td>
      <td>{$name.standard}</td>
    </tr>
    {/foreach}
  </table>
  <p class="center">
    <input type="submit" name="correct" value="Corriger" />
  </p>
</form>
<p>
  <a href="admin/name">Retour à la gestion des noms.</a>
</p>
{else}
<form method="post" action="{$platal->ns}admin/name">
  <p>
    Il est possible d'entrer ici n'importe quelle adresse email&nbsp;: redirection, melix ou alias.<br />
    <input type="text" size="60" name="id" />
    <input type="submit" value="Chercher" />
  </p>
</form>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
