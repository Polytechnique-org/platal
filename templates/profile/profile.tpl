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

{literal}
<script type="text/javascript">//<![CDATA[
function chgMainWinLoc(strPage)
{
  strPage = platal_baseurl + strPage;
  if (parent.opener) {
    try {
      parent.opener.document.location = strPage;
      window.close();
    } catch(e) {
      window.open(strPage);
    }
  } else {
    document.location = strPage;
  }
}
//]]></script>
{/literal}

<div id="fiche">
  <div id="photo" class="part">
    {assign var=photo value=$profile->getPhoto(false)}
    {if $photo}<img alt="Photo de {$profile->fullName()}" src="photo/{$profile->hrid()}" width="{$photo->width()}"/>{/if}

    {if $logged && ( $profile->section|smarty:nodefaults || $profile->getBinets()|smarty:nodefaults || ($owner && $owner->groups()|smarty:nodefaults))}
      <h2>À l'X&hellip;</h2>
      {if $profile->section}<div><em class="intitule">Section&nbsp;: </em><span>{$profile->section}</span></div>{/if}

      {assign var=binets value=$profile->getBinets()}
      {if $binets|@count}<div><em class="intitule">Binet{if count($binets) > 1}s{/if}&nbsp;: </em>
      <span>{', '|implode:$profile->getBinetsNames()}</span></div>{/if}

      {if $owner}
        {assign var=groups value=$owner->groupNames(true)}
        {if $groups|@count}<div><em class="intitule">Groupe{if count($groups) > 1}s{/if} et institution{if count($groups) > 1}s{/if} X&nbsp;: </em>
        <span><br/>
        {foreach from=$groups item=group key=gk}{if $gk != 0}, {/if}<span title="{$group.nom}"><a href="{$group.site}">{$group.nom}</a></span>{/foreach}
        </span></div>{/if}
      {/if}

    {/if}

    {assign var=networking value=$profile->getNetworking(0)}
    {if count($networking) > 0}
      <h2>Sur le web...</h2>
      {foreach from=$networking item=network}
        <img style="width: auto; padding: 0" src="profile/networking/{$network.nwid}" alt="{$network.name}" title="{$network.name}"/>
        {if $network.link}
          <a href="{$network.link|replace:'%s':$network.address}">{$network.address}</a>
        {else}
          {$network.address}
        {/if}
        <br/>
      {/foreach}
    {/if}

    {if $profile->freetext}
      <h2>Commentaires&nbsp;:</h2>
      <span>{$profile->freetext|miniwiki|smarty:nodefaults}</span>
    {/if}

  </div>

  <div id="fiche_identite" class="part">
    <div class="civilite">
      {if $profile->isFemale()}&bull;{/if}
        <span {if $profile->name_tooltip neq ""}class="hinted" title="{$profile->name_tooltip}"{/if}>{$profile->shortName()}</span>
      {if $logged}
        {if $profile->nickname} (alias {$profile->nickname}){/if}
      {/if}

      {if $logged}
        &nbsp;{if !$profile->isDead()}<a href="vcard/{$owner->login()}.vcf">{*
          *}{icon name=vcard title="Afficher la carte de visite"}</a>{/if}

        {if !$smarty.session.user->isContact($profile)}
        <a href="javascript:chgMainWinLoc('carnet/contacts?action=ajouter&amp;user={$owner->login()}&amp;token={xsrf_token}')">
          {icon name=add title="Ajouter à mes contacts"}</a>
        {else}
        <a href="javascript:chgMainWinLoc('carnet/contacts?action=retirer&amp;user={$owner->login()}&amp;token={xsrf_token}')">
          {icon name=cross title="Retirer de mes contacts"}</a>
        {/if}

        {if hasPerm('admin')}
        <a href="javascript:chgMainWinLoc('admin/user/{$owner->login()}')">
          {icon name=wrench title="administrer user"}</a>
        {/if}

        {if $owner->login() eq $smarty.session.hruid}
        <a href="javascript:chgMainWinLoc('profile/edit')">{icon name="user_edit" title="Modifier ma fiche"}</a>
        {/if}

      {/if}
    </div>

    {if $logged}
    <div class='maj'>
      Fiche mise à jour<br />
      le {$profile->last_change|date_format}
    </div>
    {/if}

    {* 121634816 is Profile::PHONE_LINK_PROFILE | Profile::PHONE_TYPE_ANY = 0x7400000 *}
    {assign var=phones value=$profile->getPhones(121634816)}
    {if $logged || count($phones) > 0}
    <div class="contact">
      {if $logged}
      <div class='email'>
        {if $profile->isDead()}
        Décédé{if $profile->isFemale()}e{/if} le {$profile->deathdate|date_format}
        {elseif $owner && $owner->lost}
        Ce{if $profile->isFemale()}tte{/if} camarade n'a plus d'adresse de redirection valide,<br />
        <a href="marketing/broken/{$owner->login()}" class="popup">clique ici si tu connais son adresse email&nbsp;!</a>
        {elseif $owner && $owner->state != 'active'}
        Cette personne n'est pas inscrite à Polytechnique.org,<br />
        <a href="marketing/public/{$owner->login()}" class="popup">clique ici si tu connais son adresse email&nbsp;!</a>
        {else}
        {if $virtualalias}
        <a href="mailto:{$virtualalias}">{$virtualalias}</a><br />
        {/if}
        <a href="mailto:{$owner->bestEmail()}">{$owner->bestEmail()}</a>
        {if $owner->bestEmail() neq $owner->forlifeEmail()}<br />
        <a href="mailto:{$owner->forlifeEmail()}">{$owner->forlifeEmail()}</a>
        {/if}
        {/if}
      </div>
      {/if}
      {if count($phones) > 0}
      <div style="float: right">
        {display_phones tels=$phones dcd=$profile->isDead()}
      </div>
      {/if}
      <div class='spacer'></div>
    </div>
    {/if}

    <div class='formation'>
      {foreach from=$profile->nationalities() item=nat}
        <img src='images/flags/{$nat}.gif' alt='{$nat}' height='11' title='{$nat}' />&nbsp;
      {/foreach}

      {$profile->promo()}

      {if $logged && $profile->mentor_expertise}
      [<a href="referent/{$profile->hrid()}" class='popup2'>Ma fiche référent</a>]
      {/if}

      {assign var=educations value=$profile->getEducations(32)}
      {if count($educations) > 0}
        &nbsp;-&nbsp;Formation&nbsp;:
        <ul>
        {foreach from=$educations item=edu}
          <li>{display_education edu=$edu profile=$profile}</li>
        {/foreach}
        </ul>
      {/if}

      {assign var=corps value=$profile->getCorps()}
      {if $corps && ($corps->current || $corps->original)}
      <ul>
        {if $corps->current}
          <li>Corps actuel&nbsp;: {$corps->current_name} {$corps->current_rank}
        {/if}
        {if $corps->original}
          <li>Corps d'origine&nbsp;: {$corps->original_name}
        {/if}
      </ul>
      {/if}

    </div>
  </div>

  {assign var=addr value=$profile->getAddresses(3)}
  {if count($addr) > 0}
  <div class="part">
    <h2>Contact&nbsp;: </h2>
    {if $profile->isDead()}
      {assign var=address_name value="Dernière adresse"}
    {else}
      {assign var=address_name value="Adresse"}
    {/if}
    {foreach from=$addr item="address" name=addresses}
      {if $smarty.foreach.addresses.iteration is even}
        {assign var=pos value="right"}
      {else}
        {assign var=pos value="left"}
      {/if}
      {if $address->hasFlag('current')}
      {include file="geoloc/address.tpl" address=$address titre_div=true titre=$address_name|@cat:" actuelle&nbsp;:"
               for="`$profile->firstname` `$profile->lastname`" pos=$pos}
      {elseif $address->hasFlag('secondary')}
      {include file="geoloc/address.tpl" address=$address titre_div=true titre=$address_name|@cat:" secondaire&nbsp;:"
               for="`$profile->firstname` `$profile->lastname`" pos=$pos}
      {else}
      {include file="geoloc/address.tpl" address=$address titre_div=true titre=$address_name|@cat:" principale&nbsp;:"
               for="`$profile->firstname` `$profile->lastname`" pos=$pos}
      {/if}
      {if $smarty.foreach.addresses.iteration is even}<div class="spacer"></div>{/if}
    {/foreach}
  </div>
  {/if}

  {assign var=jobs value=$profile->getJobs(2)}
  {if count($jobs) > 0}
  <div class="part">
    <h2>Informations professionnelles&nbsp;:</h2>
    {foreach from=$jobs item="job" key="i"}
      {if $i neq 0}<hr />{/if}
      {include file="include/emploi.tpl" job=$job}
      {if $job->address()}
        {include file="geoloc/address.tpl" address=$job->address titre="Adresse&nbsp;: " for=$job->company->name pos="left"}
      {/if}
      <div class="spacer">&nbsp;</div>
    {/foreach}
  </div>
  {/if}

  {assign var=medals value=$profile->getMedals()}
  {if count($medals) > 0}
    <div class="part">
      <h2>Distinctions&nbsp;: </h2>
      {foreach from=$medals item=m}
      <div class="medal_frame">
        <img src="profile/medal/thumb/{$m.mid}" height="50px" alt="{$m.text}" title="{$m.text}" style='float: left;' />
        <div class="medal_text">
          {$m.text}<br />{$m.grade}
        </div>
      </div>
      {/foreach}
      <div class="spacer">&nbsp;</div>
    </div>
  {/if}

  {if $logged && $profile->cv}
  <div class="part">
    <h2>Curriculum Vitae&nbsp;:</h2>
    {$profile->cv|miniwiki:title|smarty:nodefaults}
  </div>
  {/if}

  {if $view eq 'public'}
  <div class="part">
    <small>
    Cette fiche est publique et visible par tout internaute,<br />
    vous pouvez aussi voir <a href="profile/private/{$profile->hrid()}?display=light">celle&nbsp;réservée&nbsp;aux&nbsp;X</a>.
    </small>
  </div>
  {elseif $view eq 'ax'}
  <div class="part">
    <small>
    Cette fiche est privée et ne recense que les informations transmises à l'AX.
    </small>
  </div>
  {/if}

  <div class="spacer"></div>
</div>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
