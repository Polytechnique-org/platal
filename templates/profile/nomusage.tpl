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

<h1>Nom d'usage</h1>

{if $same}
  <p class="erreur">
      Si ton nom d'usage est identique à ton nom à l'X, il n'est pas
      nécessaire de le saisir ici!
  </p>
  <p>
    Pour supprimer ton nom d'usage, fais une demande avec un nom vide et en précisant la raison.
  </p>
  <p>
    [<a href='profile/usage'>Refaire une demande</a>] - [<a href='profile/edit'>Retour au profil</a>]
  </p>
{else}
  {if $myusage}
    {if $usage_old}
      <p>
        Ta demande de suppression de ton nom d'usage ainsi que de tes
        alias {$alias_old}@{#globals.mail.domain#} et
        {$alias_old}@{#globals.mail.domain2#} a bien été enregistrée. 
      </p>
    {/if}

    {if $myusage->alias}
      <p>
        Ta demande d'ajout de ton nom d'usage a bien été enregistrée. Sa
        validation engendrera la création des alias
        <strong>{$myusage->alias}@{#globals.mail.domain#}</strong> et
        <strong>{$myusage->alias}@{#globals.mail.domain2#}</strong>.
      </p>
    {/if}
  
    <p>
      Tu recevras un mail dès que les changements demandés auront été effectués. 
      Encore merci de nous faire confiance pour tes e-mails !
    </p>

  {else}

<p>
Le nom d'usage est un patronyme qui doit être <strong>reconnu par la
  loi</strong> (nom du conjoint, d'un de ses parents, ou bien plus
exceptionnellement sur changement de l'état civil, ...).
</p>

<p>
Les surnoms sont <strong>systématiquement refusés</strong>. Pour
utiliser une adresse personnalisée, il faut se tourner vers
<a href="emails/alias">l'alias @melix.net</a>.
</p>

  <p>
  Afin d'être joignable à la fois sous ton nom à l'X et sous ton nom d'usage, tu peux
  saisir ici ce dernier. Il apparaîtra alors dans l'annuaire et tu disposeras
  des adresses correspondantes @{#globals.mail.domain#} et @{#globals.mail.domain2#}, en plus de
  celles que tu possèdes déjà.
  </p>

  <br />

{literal}
  <form action="profile/usage" method="post" onsubmit="
  for (var i=this.reason.length-1; i>=0; i--) {
    if (this.reason[i].checked && 
      (this.reason[i].value != 'other' || this.other_reason.value != '')) {
      return true;
    }
  }
  alert('Tu dois préciser une raison motivant ta demande.');
  return false;
">
{/literal}
    <table class="bicol" cellpadding="4" summary="Nom d'usage">
      <tr>
        <th>Nom d'usage</th>
      </tr>
      <tr>
        <td class="center">
          <input type="text" name="nom_usage" id="nom_usage" value="{$usage_old}" />
          <script type="text/javascript">
            document.getElementById("nom_usage").focus();
          </script>
        </td>
      </tr>
      <tr>
        <th>Raison du changement de nom</th>
      </tr>
      <tr>
        <td class="rt">
          <input type="radio" name="reason" value="époux/se" id="reason_ep" onclick="this.form.other_reason.value=''" /><label for="reason_ep">Nom d'épouse / d'époux</label><br />
          {if $usage_old}
            <input type="radio" name="reason" value="divorce" id="reason_div" onclick="this.form.other_reason.value=''" /><label for="reason_div">Divorce</label><br />
          {/if}
          <input type="radio" name="reason" value="raccourci" id="reason_rac" onclick="this.form.other_reason.value=''" /><label for="reason_rac">Nom d'état civil simplifié, le nom officiel étant trop long</label><br />
          <input type="radio" name="reason" value="other" id="reason_oth" /><label for="reason_oth">Autre :</label><br />
          <input type="text" name="other_reason" onfocus="document.getElementById('reason_oth').checked='checked'" size="60" />
        </td>
      </tr>
      <tr>
        <td class="center">
          {if !$usage_old}
            <input type="submit" name="submit" value="Faire la demande" />
          {else}
            <input type="submit" name="submit" value="Modifier" />
            <input type="submit" name="submit" value="Supprimer" onClick="this.form.nom_usage.value=''" />
          {/if}
        </td>
      </tr>
    </table>
  </form>
  {/if}
{/if}


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
