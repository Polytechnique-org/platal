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
 ***************************************************************************}

<h1>
  Synchronisation depuis l'AX
</h1>

<form action='{$smarty.request.PHP_SELF}' method='get'>
<table class="tinybicol">
<tr>
  <th>Synchroniser un utilisateur</th>
</tr>
<tr>
  <td style='padding:5px'>
    <input type='text' name='user' value='{$smarty.request.user}' size='40' maxlength='255'/>
    <input type='submit' value='Chercher' />
  </td>
</tr>
</table>
</form>

<form action='{$smarty.request.PHP_SELF}' method='post'>
{if $x.profile_from_ax}
<div style="text-align:center;margin:5px;background:green">
<strong>Cet utilisateur a accpeté la synchronisation</strong>
</div>
{else}
<div style="text-align:center;margin:5px;background:red">
<strong>ATTENTION !  Cet utilisateur n'a pas accepté la synchronisation</strong>
</div>
{/if}
<table class="bicol" cellpadding="0" cellspacing="0" border="1">
  <tr>
    <th>champ</th>
    <th style='width:50%'>x.org</th>
    <th style='width:50%'>AX</th>
  </tr>
{foreach from=$ax item='val' key='i'}
  {if ($i neq 'adr') and ($i neq 'adr_pro')}
    <tr class="{cycle values='impair,pair'}">
      <td>
        {$i}
      </td>
      <td>
        {$x[$i]}
      </td>
      <td>
        {if ($i eq 'epouse') or ($i eq 'mobile')}
        <div style='float:right'>
          <input style='flat:right' type='checkbox' name='{$i}' />
        </div>
        {/if}
        {$val}
      </td>
    </tr>
  {/if}
{/foreach}
  <tr class='impair'>
    <td>
      adresses
    </td>
    <td>
    {foreach from=$x.adr item='adr'}
      <div style="padding:5px">
        <div style='float:right'>
          <input type='checkbox' name='del_address{$adr.adrid}' />
        </div>
        {include file='geoloc/address.tpl' address=$adr no_div=1}
      </div>
    {/foreach}
    </td>
    <td>
    {foreach from=$ax.adr item='adr' key='adrid'}
      <div style='padding:5px'>
        <div style='float:right'>
          <input type='checkbox' name='add_address{$adrid}' />
        </div>
        {include file='geoloc/address.tpl' address=$adr no_div=1}
      </div>
    {/foreach}
    </td>
  </tr>
  <tr class='pair'>
    <td>
      adr_pro
    </td>
    <td>
    {foreach from=$x.adr_pro item='pro'}
    {if ($pro.poste) or ($pro.fonction) or ($pro.entreprise)}
      <div style='padding:5px'>
        <div style='float:right'>
          <input type='checkbox' name='add_pro{$pro.entrid}' />
        </div>
        {if $pro.entreprise}
        <div>
          <em>Entreprise/Organisme : </em> <strong>{$pro.entreprise}</strong>
        </div>
        {/if}
        {if $pro.secteur}
        <div>
          <em>Secteur : </em>
          <strong>{$pro.secteur}{if $pro.ss_secteur} ({$pro.ss_secteur}){/if}</strong>
        </div>
        {/if}
        {if $pro.fonction}
        <div>
          <em>Fonction : </em> <strong>{$pro.fonction}</strong>
        </div>
        {/if}
        {if $pro.poste}
        <div>
          <em>Poste : </em> <strong>{$pro.poste}</strong>
        </div>
        {/if}
        {include file='geoloc/address.tpl' address=$pro no_div=1}
      </div>
    {/if}
    {/foreach}
    </td>
    <td>
    {foreach from=$ax.adr_pro item='pro' key='proid'}
    {if ($pro.poste) or ($pro.fonction) or ($pro.entreprise)}
      <div style='padding:5px'>
        <div style='float:right'>
          <input type='checkbox' name='add_pro{$proid}' />
        </div>
        {if $pro.entreprise}
        <div>
          <em>Entreprise/Organisme : </em> <strong>{$pro.entreprise}</strong>
        </div>
        {/if}
        {if $pro.fonction}
        <div>
          <em>Fonction : </em> <strong>{$pro.fonction}</strong>
        </div>
        {/if}
        {include file='geoloc/address.tpl' address=$pro no_div=1}
      </div>
    {/if}
    {/foreach}
    </td>
  </tr>
</table>
<div class='center'>
  <input type='hidden' name='user' value='{$ax.uid}' />
  <input type='submit' name='importe' value='Importer' />
</div>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
