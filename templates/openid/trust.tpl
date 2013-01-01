{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2013 Polytechnique.org                             *}
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

<h1>Demande d'identification OpenId</h1>

<p>Un site tiers demande à confirmer ton identité OpenId.
  {if $sreg_data}
    De plus, il a demandé à recevoir un certain nombre d'informations
    te concernant.
  {/if}
  Merci de nous indiquer ton choix.
</p><br />

<form method="POST" action="openid/trust?{$openid_query}">
  {xsrf_token_field}
  <table class="bicol">
    <tr><th colspan="2">Souhaitez-vous confirmer votre identité&nbsp;?</th></tr>

    <tr class="impair">
      <td>Adresse du site&nbsp;:</td>
      <td><strong>{$relying_party}</strong></td>
    </tr>
    {if $sreg_data}
    <tr class="impair">
      <td>Informations demandées&nbsp;:</td>
      <td><ul style="margin-top: 0">
        {foreach from=$sreg_data key=field item=value}
        <li><strong>{$field}</strong> ({$value})</li>
        {/foreach}
      </ul></td>
    </tr>
    {/if}

    <tr class="pair">
      <td></td>
      <td>
        <label><input type="checkbox" name="trust_always" />
          Toujours faire confiance à ce site.</label><br />
        {if $sreg_data}
        <label><input type="checkbox" checked="checked" name="trust_sreg" />
          Envoyer les données ci-dessus au site.</label><br />
        {/if}
      </td>
    </tr>
    <tr class="impair center"><td colspan="2">
      <input type="submit" name="trust_accept" value="Confirmer" />
      <input type="submit" name="trust_cancel" value="Annuler" />
    </td></tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
