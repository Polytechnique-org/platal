{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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

<div class="contact {if (!$c.inscrit && $smarty.session.auth ge AUTH_COOKIE) || $c.dcd}grayed{/if}"
     {if $c.inscrit}{if $smarty.session.auth ge AUTH_COOKIE}title="fiche mise à jour le {$c.date|date_format}"{/if}{/if}>
  <div class="identity">
    {if $smarty.session.auth ge AUTH_COOKIE}
    <div class="photo">
      <img src="photo/{if $c.inscrit}{$c.forlife}{else}{make_forlife nom=$c.nom prenom=$c.prenom promo=$c.promo}{/if}"
           alt="{$c.name_display}" />
    </div>
    {/if}

    <div class="nom">
      {if $c.sexe}&bull;{/if}
      {if !$c.dcd && ($c.inscrit || $smarty.session.auth eq AUTH_PUBLIC)}<a href="profile/{if $c.inscrit}{$c.forlife}{else}{make_forlife nom=$c.nom prenom=$c.prenom promo=$c.promo}{/if}" class="popup2">{/if}
      <span {if $c.name_tooltip}class="hinted" title="{$c.name_tooltip}"{/if}>{$c.name_display}</span>
      {if !$c.dcd && ($c.inscrit || $smarty.session.auth eq AUTH_PUBLIC)}</a>{/if}
    </div>

    <div class="edu">
      {if $c.iso3166_1}
      <img src='images/flags/{$c.iso3166_1}.gif' alt='{$c.nat1}' height='11' title='{$c.nat1}' />&nbsp;
      {/if}
      {if $c.iso3166_2}
      <img src='images/flags/{$c.iso3166_2}.gif' alt='{$c.nat2}' height='11' title='{$c.nat2}' />&nbsp;
      {/if}
      {if $c.iso3166_3}
      <img src='images/flags/{$c.iso3166_3}.gif' alt='{$c.nat3}' height='11' title='{$c.nat3}' />&nbsp;
      {/if}
      X {$c.promo}{if $c.eduname0}, {education_fmt name=$c.eduname0 url=$c.eduurl0 degree=$c.edudegree0
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
      {if !$c.wasinscrit && !$c.dcd}
        {if $show_action eq ajouter}
    <a href="carnet/notifs/add_nonins/{$c.user_id}?token={xsrf_token}">{*
    *}{icon name=add title="Ajouter à la liste de mes surveillances"}</a>
        {else}
    <a href="carnet/notifs/del_nonins/{$c.user_id}?token={xsrf_token}">{*
    *}{icon name=cross title="Retirer de la liste de mes surveillances"}</a>
        {/if}
      {elseif $c.wasinscrit}
    <a href="profile/{$c.forlife}" class="popup2">{*
    *}{icon name=user_suit title="Afficher la fiche"}</a>
        {if !$c.dcd}
    <a href="vcard/{$c.forlife}.vcf">{*
    *}{icon name=vcard title="Afficher la carte de visite"}</a>
    <a href="mailto:{$c.forlife}@{#globals.mail.domain#}">{*
    *}{icon name=email title="Envoyer un email"}</a>
          {if $show_action eq ajouter}
    <a href="carnet/contacts?action={$show_action}&amp;user={$c.forlife}&amp;token={xsrf_token}">{*
    *}{icon name=add title="Ajouter à mes contacts"}</a>
          {else}
    <a href="carnet/contacts?action={$show_action}&amp;user={$c.forlife}&amp;token={xsrf_token}">{*
    *}{icon name=cross title="Retirer de mes contacts"}</a>
          {/if}
        {/if}
      {/if}
    </div>

    {if hasPerm('admin')}
    <div>
      [{if !$c.wasinscrit && !$c.dcd}
      <a href="marketing/private/{$c.user_id}">{*
        *}{icon name=email title="marketter user"}</a>
      {/if}
      <a href="admin/user/{if $c.wasinscrit}{$c.forlife}{else}{$c.user_id}{/if}">{*
      *}{icon name=wrench title="administrer user"}</a>
      <a href="http://www.polytechniciens.com/?page=AX_FICHE_ANCIEN&amp;anc_id={$c.matricule_ax}">{*
      *}{icon name=user_gray title="fiche AX"}</a>]
    </div>
    {/if}
  </div>
  {/if}

  <div class="long">
  {if $c.wasinscrit || !$c.dcd}
    {if $c.web || $c.mobile || $c.countrytxt || $c.city || $c.region || $c.entreprise || $c.freetext || (!$c.dcd && !$c.actif )}
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
          {if $c.secteur}({$c.secteur}){/if} {if $c.fonction}<br />{$c.fonction}{/if}
        </td>
      </tr>
      {/if}
      {if $c.freetext}
      <tr>
        <td class="lt">Commentaire&nbsp;:</td>
        <td class="rt">{$c.freetext|nl2br}</td>
      </tr>
      {/if}
      {if !$c.dcd && (!$c.actif || !$c.wasinscrit) && $smarty.session.auth ge AUTH_COOKIE}
      <tr>
        <td class="smaller" colspan="2">
          {if !$c.wasinscrit}
          Ce{if $c.sexe}tte{/if} camarade n'est pas inscrit{if $c.sexe}e{/if}.
          <a href="marketing/public/{$c.user_id}" class='popup'>Si tu connais son adresse email,
          <strong>n'hésite pas à nous la transmettre !</a>
          {elseif !$c.actif}
          Ce{if $c.sexe}tte{/if} camarade n'a plus d'adresse de redirection valide.
          <a href="marketing/broken/{$c.forlife}">
            Si tu en connais une, <strong>n'hésite pas à nous la transmettre</strong>.
          </a>
          {/if}
        </td>
      </tr>
      {/if}
    </table>
    {/if}
  {/if}
  </div>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
