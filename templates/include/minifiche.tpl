{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2010 Polytechnique.org                             *}
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
  <div class="identity">
    {if $withAuth}
    <div class="photo">
      <img src="photo/{$profile->hrid()}" alt="{$profile->directory_name}" />
    </div>
    {/if}

    <div class="nom">
      {if $profile->isFemale()}&bull;{/if}
      {if !$dead && $registered}<a href="profile/{$profile->hrid()}" class="popup2">{/if}
      {$profile->full_name}
      {if !$dead && $registered}</a>{/if}
    </div>

    <div class="edu">
      {foreach from=$profile->nationalities() item=nat}
      <img src='images/flags/{$nat}.gif' alt='{$nat}' height='11' title='{$nat}' />&nbsp;
      {/foreach}
      {$profile->promo()}{*
      *}{foreach from=$profile->getExtraEducations(4) item=edu}, {display_education edu=$edu profile=$profile}{/foreach}{*
      *}{if $dead}, {"décédé"|sex:"décédée":$profile} le {$profile->deathdate|date_format}{/if}
    </div>
  </div>

  {if $withAuth}
  <div class="noprint bits">
    {if $registered || (!$dead && $hasowner)}
    <div>
      {if !$registered && !$dead && $hasowner}
        {if !$smarty.session.user->isWatchedUser($profile)}
    <a href="carnet/notifs/add_nonins/{$user->login()}?token={xsrf_token}">{*
    *}{icon name=add title="Ajouter à la liste de mes surveillances"}</a>
        {else}
    <a href="carnet/notifs/del_nonins/{$user->login()}?token={xsrf_token}">{*
    *}{icon name=cross title="Retirer de la liste de mes surveillances"}</a>
        {/if}
      {elseif $registered}
    <a href="profile/{$profile->hrid()}" class="popup2">{*
    *}{icon name=user_suit title="Afficher la fiche"}</a>
        {if !$dead}
    <a href="vcard/{$profile->hrid()}.vcf">{*
    *}{icon name=vcard title="Afficher la carte de visite"}</a>
    <a href="mailto:{$user->bestEmail()}">{*
    *}{icon name=email title="Envoyer un email"}</a>
          {if !$smarty.session.user->isContact($profile)}
    <a href="carnet/contacts?action=ajouter&amp;user={$profile->hrid()}&amp;token={xsrf_token}">{*
    *}{icon name=add title="Ajouter à mes contacts"}</a>
          {else}
    <a href="carnet/contacts?action=retirer&amp;user={$profile->hrid()}&amp;token={xsrf_token}">{*
    *}{icon name=cross title="Retirer de mes contacts"}</a>
          {/if}
        {/if}
      {/if}
    </div>
    {/if}

    {if hasPerm('admin') && $hasowner}
    <div>
      [{if $registered && !$dead}
      <a href="marketing/private/{$user->login()}">{*
        *}{icon name=email title="marketter user"}</a>
      {/if}
      <a href="admin/user/{$user->login()}">{*
      *}{icon name=wrench title="administrer user"}</a>
      <a href="profile/ax/{$user->login()}">{*
      *}{icon name=user_gray title="fiche AX"}</a>]
    </div>
    {/if}
  </div>
  {/if}

  <div class="long">
  {if !$dead}
    {assign var=address value=$profile->getMainAddress()}
    {assign var=web     value=$profile->getWebSite()}
    {assign var=job     value=$profile->getMainJob()}
    {if $web || $profile->mobile || ($address && $address->country) || $job || !$registered}
    <table cellspacing="0" cellpadding="0">
      {if $web}
      <tr>
        <td class="lt">Page web&nbsp;:</td>
        <td class="rt"><a href="{$web}">{$web}</a></td>
      </tr>
      {/if}
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
      {if $job}
      <tr>
        <td class="lt">Profession&nbsp;:</td>
        <td class="rt">
          {if $job->company->url|default:$job->user_site}<a href="{$job->company->url|default:$job->user_site}">{$job->company->name}</a>{else}{$job->company->name}{/if}
          {if $job->subsubsector}&nbsp;({$job->subsubsector}){/if}{if $job->description}<br />{$job->description}{/if}
        </td>
      </tr>
      {/if}
      {if $withAuth}
      {if !$registered && $hasowner}
      <tr>
        <td class="smaller" colspan="2">
          {"Ce"|sex:"Cette":$profile} camarade n'est pas {"inscrit"|sex:"inscrite":$profile}.
          <a href="marketing/public/{$user->login()}" class='popup'>Si tu connais son adresse email,
          <strong>n'hésite pas à nous la transmettre !</strong></a>
        </td>
      </tr>
      {elseif $user->state neq 'disabled' && $user->lost}
      <tr>
        <td class="smaller" colspan="2">
          {"Ce"|sex:"Cette":$profile} camarade n'a plus d'adresse de redirection valide.
          <a href="marketing/broken/{$user->login()}">
            Si tu en connais une, <strong>n'hésite pas à nous la transmettre</strong>.
          </a>
        </td>
      </tr>
      {/if}
      {/if}
    </table>
    {/if}
  {/if}
  </div>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
