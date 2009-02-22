{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2009 Polytechnique.org                             *}
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

{assign var=profile value=$user->profile()}
<div class="contact {if ($user->state eq 'pending' && $smarty.session.auth ge AUTH_COOKIE) || $profile->deathdate}grayed{/if}"
     {if $user->state neq 'pending'}{if $smarty.session.auth ge AUTH_COOKIE}title="fiche mise à jour le {$profile->last_change|date_format}"{/if}{/if}>
  <div class="identity">
    {if $smarty.session.auth ge AUTH_COOKIE}
    <div class="photo">
      <img src="photo/{$profile->hrid()}"
           alt="{$profile->directory_name}" />
    </div>
    {/if}

    <div class="nom">
      {if $profile->isFemale()}&bull;{/if}
      {if !$profile->deathdate && ($user->state neq 'pending' || $smarty.session.auth eq AUTH_PUBLIC)}<a href="profile/{$profile->hrid}" class="popup2">{/if}
      {$profile->full_name}
      {if !$profile->deathdate && ($user->state neq 'pending' || $smarty.session.auth eq AUTH_PUBLIC)}</a>{/if}
    </div>

    <div class="edu">
      {if $profile->nationality1}
      <img src='images/flags/{$profile->nationality1}.gif' alt='{$profile->nationality1}' height='11' title='{$profile->nationality1}' />&nbsp;
      {/if}
      {if $profile->nationality2}
      <img src='images/flags/{$profile->nationality2}.gif' alt='{$profile->nationality2}' height='11' title='{$profile->nationality2}' />&nbsp;
      {/if}
      {if $profile->nationality3}
      <img src='images/flags/{$profile->nationality3}.gif' alt='{$profile->nationality3}' height='11' title='{$profile->nationality3}' />&nbsp;
      {/if}
      {$profile->promo()}
      
      {if $c.eduname0}, {education_fmt name=$c.eduname0 url=$c.eduurl0 degree=$c.edudegree0
                                     grad_year=$c.edugrad_year0 field=$c.edufield0 program=$c.eduprogram0 sexe=$c.sexe}{*
      *}{/if}{if $c.eduname1}, {education_fmt name=$c.eduname1 url=$c.eduurl1 degree=$c.edudegree1
                                     grad_year=$c.edugrad_year1 field=$c.edufield1 program=$c.eduprogram1 sexe=$c.sexe}{*
      *}{/if}{if $c.eduname2}, {education_fmt name=$c.eduname2 url=$c.eduurl2 degree=$c.edudegree2
                                     grad_year=$c.edugrad_year2 field=$c.edufield2 program=$c.eduprogram2 sexe=$c.sexe}{*
      *}{/if}{if $c.eduname3}, {education_fmt name=$c.eduname3 url=$c.eduurl3 degree=$c.edudegree3
                                     grad_year=$c.edugrad_year3 field=$c.edufield3 program=$c.eduprogram3 sexe=$c.sexe}{*
      *}{/if}{if $c.dcd}, décédé{if $c.sexe}e{/if} le {$c.deces|date_format}{/if}
    </div>
  </div>

  {if $smarty.session.auth ge AUTH_COOKIE}
  <div class="noprint bits">
    <div>
      {if $user->state eq 'pending' && !$profile->deathdate}
        {if $show_action eq ajouter}
    <a href="carnet/notifs/add_nonins/{$user->login()}?token={xsrf_token}">{*
    *}{icon name=add title="Ajouter à la liste de mes surveillances"}</a>
        {else}
    <a href="carnet/notifs/del_nonins/{$user->login()}?token={xsrf_token}">{*
    *}{icon name=cross title="Retirer de la liste de mes surveillances"}</a>
        {/if}
      {elseif $user->state neq 'pending'}
    <a href="profile/{$profile->hrid()}" class="popup2">{*
    *}{icon name=user_suit title="Afficher la fiche"}</a>
        {if !$profile->deathdate}
    <a href="vcard/{$profile->hrid()}.vcf">{*
    *}{icon name=vcard title="Afficher la carte de visite"}</a>
    <a href="mailto:{$user->bestEmail()}">{*
    *}{icon name=email title="Envoyer un email"}</a>
          {if !$smarty.session.user->isContact($user)}
    <a href="carnet/contacts?action=ajouter&amp;user={$user->login()}&amp;token={xsrf_token}">{*
    *}{icon name=add title="Ajouter à mes contacts"}</a>
          {else}
    <a href="carnet/contacts?action=retirer&amp;user={$user->login()}&amp;token={xsrf_token}">{*
    *}{icon name=cross title="Retirer de mes contacts"}</a>
          {/if}
        {/if}
      {/if}
    </div>

    {if hasPerm('admin')}
    <div>
      [{if $user->state eq 'pending' && !$profile->deathdeate}
      <a href="marketing/private/{$user->login()}">{*
        *}{icon name=email title="marketter user"}</a>
      {/if}
      <a href="admin/user/{$user->login()}">{*
      *}{icon name=wrench title="administrer user"}</a>
      <a href="http://www.polytechniciens.com/?page=AX_FICHE_ANCIEN&amp;anc_id={$profile->ax_id}">{*
      *}{icon name=user_gray title="fiche AX"}</a>]
    </div>
    {/if}
  </div>
  {/if}

  <div class="long">
  {if !$profile->deathdeate}
    {if $c.web || $c.mobile || $c.countrytxt || $c.city || $c.region || $c.entreprise || (!$c.dcd && !$c.actif )}
    <table cellspacing="0" cellpadding="0">
      {if $c.web}
      <tr>
        <td class="lt">Page web&nbsp;:</td>
        <td class="rt"><a href="{$c.web}">{$c.web}</a></td>
      </tr>
      {/if}
      {if $c.countrytxt || $c.city}
      <tr>
        <td class="lt">Géographie&nbsp;:</td>
        <td class="rt">{$c.city}{if $c.city && $c.countrytxt}, {/if}{$c.countrytxt}</td>
      </tr>
      {/if}
      {if $c.mobile && !$c.dcd}
      <tr>
        <td class="lt">Mobile&nbsp;:</td>
        <td class="rt">{$c.mobile}</td>
      </tr>
      {/if}
      {if $c.entreprise}
      <tr>
        <td class="lt">Profession&nbsp;:</td>
        <td class="rt">
          {if $c.job_web}<a href="{$c.job_web}">{$c.entreprise}</a>{else}{$c.entreprise}{/if}
          {if $c.secteur} ({$c.secteur}){/if}{if $c.fonction}<br />{$c.fonction}{/if}
        </td>
      </tr>
      {/if}
      {if $smarty.session.auth ge AUTH_COOKIE}
      {if $user->state eq 'pending'}
      <tr>
        <td class="smaller" colspan="2">
          {"Ce"|sex:"Cette":$user} camarade n'est pas {inscrit|sex:"inscrite":$user}.
          <a href="marketing/public/{$user->login()}" class='popup'>Si tu connais son adresse email,
          <strong>n'hésite pas à nous la transmettre !</a>
        </td>
      </tr>
      {elseif $user->state neq 'disabled' && $user->lost}
      <tr>
        <td class="smaller" colspan="2">
          {"Ce"|sex:"Cette":$user} camarade n'a plus d'adresse de redirection valide.
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
