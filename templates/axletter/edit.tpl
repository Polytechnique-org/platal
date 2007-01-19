{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<h1>Edition de message</h1>

<form action="{$platal->pl_self()}" method="post">
  {if $am}
  {include file="axletter/letter.tpl"}

  <p class="center">
    <input type="hidden" name="id" value="{$id}" />
    <input type="hidden" name="old_shortname" value="{$shortname}" />
    <input type="hidden" name="saved" value="{$saved}" />
    {if $echeance}
    <input type="hidden" name="echeance" value="{$echeance}" />
    {/if}
    {if !$new}
    <input type="submit" name="valid" value="Confirmer" />
    {/if}
  </p>
  {/if}

  <fieldset>
    <legend>Sujet du mail : <input type="text" name="subject" value="{$subject}" size="60"/></legend>
    <p class="center">
      <strong>Titre : </strong><input type="text" name="title" value="{$title}" size="60" /><br />
      <textarea name="body" rows="30" cols="78">{$body}</textarea><br />
      <strong>Signature : </strong><input type="text" name="signature" value="{$signature}" size="60" />
    </p>
  </fieldset>

  <table class="tinybicol">
    <tr>
      <th colspan="2">Options du message</th>
    </tr>
    <tr>
      <td class="titre">Nom raccourci</td>
      <td>
        <input type="text" name="shortname" value="{$shortname}" size="16" maxlength="16" />
        <span class="smaller">(uniquement lettres, chiffres ou -)</span>
      </td>
    </tr>
    <tr>
      <td class="titre">Promo min</td>
      <td>
        <input type="text" name="promo_min" value="{$promo_min|default:0}" size="4" maxlength="4" />
        <span class="smaller">(0 pour pas de minimum... ex: 1947)</span>
      </td>
    </tr>
    <tr>
      <td class="titre">Promo max</td>
      <td>
        <input type="text" name="promo_max" value="{$promo_max|default:0}" size="4" maxlength="4" />
        <span class="smaller">(0 pour pas de maximum... ex: 2001)</span>
      </td>
    </tr>
    {if !$saved}
    <tr>
      <td class="titre">Echéance d'envoi</td>
      <td>
        le <select name="echeance_date">{$echeance_date|smarty:nodefaults}</select>
        vers <select name="echeance_time">{$echeance_time|smarty:nodefaults}</select>
      </td>
    </tr>
    {else}
    <tr>
      <td colspan="2" class="center">
        Envoi au plus tard le {$echeance|date_format:"%x vers %Hh"}<br />
        {if $is_xorg}
        [<a href="ax/edit/valid" onclick="return confirm('Es-tu sûr de voiloir valider l\'envoi de ce message ?');">{*
          *}{icon name=thumb_up} Valider l'envoi</a>]
        {else}
        [<a href="ax/edit/cancel" onclick="return confirm('Es-tu sûr de vouloir annuler l\'envoi de ce message ?');">{*
          *}{icon name=thumb_down} Annuler l'envoi</a>]
        {/if}
      </td>
    </tr>
    {/if}
  </table>

  <p class="center">
    <input type="hidden" name="id" value="{$id}" />
    <input type="hidden" name="old_shortname" value="{$shortname}" />
    <input type="hidden" name="saved" value="{$saved}" />
    {if $echeance}
    <input type="hidden" name="echeance" value="{$echeance}" />
    {/if}
    <input type="submit" name="valid" value="Aperçu" />
    {if !$new}
    <input type="submit" name="valid" value="Confirmer" />
    {/if}
  </p>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
