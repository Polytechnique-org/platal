{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: profil.tpl,v 1.10 2004-08-31 21:39:01 x2000habouzit Exp $
 ***************************************************************************}


{config_load file="profil.conf"}
{dynamic}
{if $etat_naissance}
{include file="profil/naissance.tpl"}
{/if}
{if $etat_naissance == '' || $etat_naissance == 'ok'}

{foreach from=$errs item=e}
<p class="erreur">{$e}</p>
{/foreach}

<p>Tu peux consulter <a href="javascript:x()" onclick="popWin('fiche.php?user={$smarty.session.username}','_blank','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1,width=620,height=370')">l'état actuel de ta fiche</a> tel qu'elle apparaîtra pour un camarade.</p>

<form action="{$smarty.server.PHP_SELF}" method="post" id="prof_annu">
  <table class="cadre_a_onglet" cellpadding="0" cellspacing="0">
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
        <input type="hidden" value="" name="binet_op" />
        <input type="hidden" value="" name="binet_id" />
        <input type="hidden" value="" name="groupex_op" />
        <input type="hidden" value="" name="groupex_id" />
        <input type="hidden" value="{$onglet}" name="old_tab" />
        <input type="hidden" value="" name="adresse_flag" />
      </td>
    </tr>
    <tr>
      <td>
        <div class="conteneur_tab">
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
              {/if}
            </tr>
          </table>
        </div>
      </td>
    </tr>
  </table>
</form>
{/if}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
