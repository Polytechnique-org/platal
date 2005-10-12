{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2004 Polytechnique.org                             *}
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

{if $etat_naissance}
{include file="profil/naissance.tpl"}
{/if}
{if $etat_naissance == '' || $etat_naissance == 'ok'}

{foreach from=$errs item=e}
<p class="erreur">{$e}</p>
{/foreach}

<p>Tu peux consulter <a href="{rel}/fiche.php?user={$smarty.session.forlife}" class="popup2">l'état actuel de ta fiche</a>
telle qu'elle apparaîtra pour un camarade,
ou <a href="{rel}/fiche.php?user={$smarty.session.forlife}&amp;public=1" class="popup2">telle</a> qu'elle apparaîtra à tout le monde.</p>

<form action="{$smarty.server.PHP_SELF}" method="post" id="prof_annu">
  <table class="cadre_a_onglet" cellpadding="0" cellspacing="0" style="width: 98%; margin-left:1%;">
    <tr>
      <td>
        <ul id='onglet'>
          {foreach from=$onglets key=o item=i}
          {if $o eq $onglet}
          <li class="actif">{$i|nl2br}</li>
          {else}
          <li><a href="{$smarty.server.PHP_SELF}?old_tab={$o}">{$i|nl2br}</a></li>
          {/if}
          {/foreach}
        </ul>
        <input type="hidden" value="{$onglet}" name="old_tab" />
        <input type="hidden" value="" name="adresse_flag" />
      </td>
    </tr>
    <tr>
      <td class="conteneur_tab">
        <table style="width:100%">
          <tr>
            <td colspan="2">
              {include file=$onglet_tpl}
            </td>
          </tr>
          <tr class="center">
            <td>
              <input type="submit" value="Valider ces modifications" name="modifier" />
            </td>
            {if $onglet != $onglet_last}
            <td>
              <input type="submit" value="Valider et passer au prochain onglet" name="suivant" />
            </td>
            {else}
            <td>
              <input type="submit" value="Valider et revenir au premier onglet" name="suivant" />
            </td>
            {/if}
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
