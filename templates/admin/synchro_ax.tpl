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

{if $x}
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
    <th>importer</th>
    <th style='width:50%'>AX</th>
  </tr>
  <tr class="impair">
    <td>fiche</td>
    <td>
      <a href='{rel}/fiche.php?user={$x.user_id}' class='popup2'>polytechnique.org</a>
    </td>
    <td>
    </td>
    <td>
      <a href='http://www.polytechniciens.com/index.php?page=AX_FICHE_ANCIEN&amp;anc_id={$x.matricule_ax}'>polytechniciens.com</a>
    </td>
  </tr>
{foreach from=$watch_champs item='i'}
  {if $x[$i] or $ax[$i]}
    <tr class="{if ($x[$i] eq $ax[$i]) or !$ax[$i]}im{/if}pair">
      <td>
        {$i}
      </td>
      <td>
        {$x[$i]}
      </td>
      <td class='center'>
        {if $x[$i] eq $ax[$i]}
          ==
        {else}
          {if $ax[$i]}
            <input style='flat:right' type='checkbox' name='{$i}' />
          {/if}
        {/if}
      </td>
      <td>
        {$ax[$i]}
      </td>
    </tr>
  {/if}
{/foreach}
</table>

<table>
<tr>
<td>
{if $ax.adr[0]}
{if $x.adr}
<div>
  Supprimer les adresses suivantes :
</div>
<table>
    {foreach from=$x.adr item='adr'}
      <tr style="padding:5px">
        <td>
          <input type='checkbox' name='del_address{$adr.adrid}' />
        </td>
        <td>
          {include file='geoloc/address.tpl' address=$adr no_div=1}
        </td>
      </tr>
    {/foreach}
</table>
<div>
  et les remplacer par les adresses suivantes de l'AX :
</div>
{else}
<div>
  Importer les adresses AX suivantes :
</div>
{/if}
<table>
    {foreach from=$ax.adr item='adr' key='adrid'}
      <tr style='padding:5px'>
        <td>
          <input type='checkbox' name='add_address{$adrid}' />
        </td>
        <td>
          {include file='geoloc/address.tpl' address=$adr no_div=1}
        </td>
      </tr>
    {/foreach}
</table>
{/if}
</td>

<td>
{if $ax.adr_pro[0].entreprise}
{if $x.adr_pro}
<div>
  Supprimer les emplois suivants :
</div>
<table>
    {foreach from=$x.adr_pro item='pro'}
    {if ($pro.poste) or ($pro.fonction) or ($pro.entreprise)}
      <tr style='padding:5px'>
        <td>
          <input type='checkbox' name='del_pro{$pro.entrid}' />
        </td>
        <td>
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
        </td>
      </tr>
    {/if}
    {/foreach}
</table>

<div>
  et les remplacer par les emplois suivants de l'AX :
</div>
{else}
<div>
  Importer les emplois suivants depuis l'AX :
</div>
{/if}
<table>
    {foreach from=$ax.adr_pro item='pro' key='proid'}
    {if ($pro.poste) or ($pro.fonction) or ($pro.entreprise)}
      <tr style='padding:5px'>
        <td>
          <input type='checkbox' name='add_pro{$proid}' />
        </td>
        <td>
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
        </td>
      </tr>
    {/if}
    {/foreach}
</table>
{/if}
</td>
</tr>
</table>
<div class='center'>
  <input type='hidden' name='user' value='{$ax.uid}' />
  <input type='submit' name='importe' value='Importer' />
</div>
</form>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
