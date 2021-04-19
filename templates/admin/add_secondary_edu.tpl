{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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

<h1>Ajout de formations secondaires</h1>

<p>{icon name=information} Cet écran permet uniquement d'ajouter des formations complémentaires aux
camarades dont les prénoms, nom et promotion courante sont indiqués. Elle ne permet pas de créer les
adresses mails associées à la nouvelle formation.</p>

<form action="{$platal->pl_self()}" method="post">
<table class="bicol" style="margin-bottom: 1em">
  <tr>
    <td>
      <strong>Promotion&nbsp;:</strong>
    </td>
    <td>
      <input type="text" name="promotion" size="4" maxlength="4" {if t($promotion)}value="{$promotion}"{/if} />
    </td>
  </tr>
  <tr>
    <td>
      <strong>Formation&nbsp;:</strong>
    </td>
    <td>
      {foreach from=$cycles key=cycle_degree item=cycle_title name=loop}
      <label><input type="radio" name="degree" value="{$cycle_degree}" {if t($degree) && $degree eq $cycle_degree}checked="checked"{/if} /> {$cycle_title}</label>{if ! $smarty.foreach.loop.last}<br />{/if}
      {/foreach}
    </td>
  </tr>
  <tr>
    <td>
      <strong>Forcer l'ajout&nbsp;:</strong><br /><small>(en cas de formation du même niveau préexistante)</small>
    </td>
    <td>
      <input type="checkbox" name="force_addition" />
    </td>
  </tr>
</table>

<table class="bicol">
  <tr>
    <td>Nom</td>
    <td>Prénom</td>
    <td>Promotion principale</td>
  </tr>
  <tr>
    <td colspan="3"><textarea name="people" rows="20" cols="80">{if t($people)}{$people}{/if}</textarea></td>
  </tr>
</table>

<p class="center">
  <strong>Séparateur&nbsp;:</strong>
  <input type="text" name="separator" value=";" size="1" maxlength="1" /><br /><br />
  <input type="submit" name="verify" value="Vérifier" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="add" value="Ajouter" />
</p>
</form>

{* vim:set et sws=2 sts=2 sw=2 fenc=utf-8: *}
