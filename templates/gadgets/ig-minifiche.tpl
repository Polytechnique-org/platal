{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2014 Polytechnique.org                             *}
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

{assign var=dead    value=$profile->deathdate}
{if $smarty.session.auth ge AUTH_COOKIE}
  {assign var=withAuth value=true}
  {assign var=user value=$profile->owner()}
  {if $user == null}
    {assign var=hasowner value=false}
    {assign var=registered value=false}
  {else}
    {assign var=hasowner value=true}
    {if $user->state neq 'pending'}
      {assign var=registered value=true}
    {else}
      {assign var=registered value=false}
    {/if}
  {/if}
{else}
  {* Without auth, all profiles appear as registered and with owner *}
  {assign var=hasowner value=true}
  {assign var=registered value=true}
  {assign var=withAuth value=false}
{/if}

<div class="contact {if !$registered || $dead }grayed{/if}"
     {if $registered}title="fiche mise à jour le {$profile->last_change|date_format}"{/if}>
  <div class="nom">
    {if $profile->isFemale()}&bull;{/if}
    {if !$dead && $registered}<a href="profile/{$profile->hrid()}" class="popup2">{/if}
    {$profile->full_name}
    {if !$dead && $registered}</a>{/if}
  </div>
  <div class="autre">
    {foreach from=$profile->nationalities() item=country key=code}
    <img src='images/flags/{$code}.gif' alt='{$code}' height='11' title='{$country}' />&nbsp;
    {/foreach}
    {$profile->promo()}{*
    *}{if $dead}, {"décédé"|sex:"décédée":$profile} le {$profile->deathdate|date_format}{/if}
    {if $withAuth}
      {if $registered || (!$dead && $hasowner)}
        {if !$registered && !$dead && $hasowner}
          {if !$smarty.session.user->isWatchedUser($profile)}
      <a href="carnet/notifs/add_nonins/{$user->login()}?token={xsrf_token}">{*
      *}{icon name=add title="Ajouter à la liste de mes surveillances"}</a>
          {else}
      <a href="carnet/notifs/del_nonins/{$user->login()}?token={xsrf_token}">{*
      *}{icon name=cross title="Retirer de la liste de mes surveillances"}</a>
          {/if}
        {elseif $registered}
          {if !$dead}
      <a href="vcard/{$profile->hrid()}.vcf">{*
      *}{icon name=vcard title="Afficher la carte de visite"}</a>
            {if !$smarty.session.user->isContact($profile)}
      <a href="carnet/contacts?action=ajouter&amp;user={$profile->hrid()}&amp;token={xsrf_token}">{*
      *}{icon name=add title="Ajouter à mes contacts"}</a>
            {else}
      <a href="carnet/contacts?action=retirer&amp;user={$profile->hrid()}&amp;token={xsrf_token}">{*
      *}{icon name=cross title="Retirer de mes contacts"}</a>
            {/if}
          {/if}
        {/if}
      {/if}
    {/if}
  </div>
  <div class="long">
  {if !$dead}
    {assign var=address value=$profile->getMainAddress()}
    {if $profile->mobile || ($address && $address->country)}
    <table cellspacing="0" cellpadding="0">
      {if $address && $address->country}
      <tr>
        <td class="lt">Géographie&nbsp;:</td>
        <td class="rt">{if $address->locality}{$address->locality}, {/if}{$address->country}</td>
      </tr>
      {/if}
      {if $profile->mobile && !$dead}
      <tr>
        <td class="lt">Mobile&nbsp;:</td>
        <td class="rt">{$profile->mobile}</td>
      </tr>
      {/if}
    </table>
    {/if}
  {/if}
  </div>
</div>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
