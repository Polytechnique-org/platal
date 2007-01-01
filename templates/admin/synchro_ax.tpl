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

<h1>
  Synchronisation depuis l'AX
</h1>

{if $no_private_key}
<p>
  Impossible d'utiliser ce service, il manque la clef privée AX dans la configuration de plat/al.
</p>
{else}
<form action='admin/synchro_ax' method='post'>
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
{/if}
{if $x}
<form action='admin/synchro_ax/{$x.forlife}/import' method='post'>
{if $x.profile_from_ax}
<div style="text-align:center;margin:5px;background:green">
<strong>Cet utilisateur a accepeté la synchronisation</strong>
</div>
{else}
<div style="text-align:center;margin:5px;background:red">
<strong>ATTENTION !  Cet utilisateur n'a pas accepté la synchronisation</strong>
</div>
{/if}
<div>Les fiches de cet utilisateur :
<ul>
<li><a href='profile/{$x.user_id}' class='popup2'>polytechnique.org</a></li>
<li><a href='http://www.polytechniciens.com/?page=AX_FICHE_ANCIEN&amp;anc_id={$x.matricule_ax}'>polytechniciens.com</a></li>
</ul>
</div>
{if $diff}
<table class="bicol" cellpadding="0" cellspacing="0" border="1">
{foreach from=$diff key='k' item='i'}
{if ($k neq 'adr') and ($k neq 'adr_pro')}
    <tr class="pair">
      <td>
        {$k}
      </td>
      <td>
        {$x[$k]}
      </td>
      <td class='center'>
      </td>
      <td>
        {$i}
      </td>
    </tr>
{/if}
{/foreach}

{if $diff.adr}
<tr><th>
Adresses
</th></tr>
{foreach from=$diff.adr item='adr'}
<tr><td>
{if $adr.remove}
    Effacer l'adresse {$adr.adrid}.
{else}
    {if $adr.adrid}Modifier l'adresse {$adr.adrid} :{else}Ajouter l'adresse :{/if}
  {include file='geoloc/address.tpl' address=$adr no_div=1}
{/if}
</td></tr>
{/foreach}
{/if}

{if $diff.adr_pro}
<tr><th>
Emplois
</th></tr>
{foreach from=$diff.adr_pro item='pro'}
<tr><td>
{if $pro.remove}
    Effacer l'emploi {$pro.entrid}.
{else}
    {if $pro.entrid || $pro.entrid === 0}Modifier l'emploi {$pro.entrid} :{else}Ajouter l'emploi :{/if}
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
{/if}
</td></tr>
{/foreach}
{/if}
</table>
<div class='center'>
  <input type='submit' value='Importer' />
</div>
{else}
<div class='center'>
    Le profil actuel est synchronisé avec les données de l'AX.
</div>
{/if}

</form>
{/if}

{* vim:set et sw=2 sts=2 sws=2: *}
