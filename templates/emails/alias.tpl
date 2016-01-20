{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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


{if $success}
  <p>
  La demande de création des alias <strong>{$success}@{#globals.mail.alias_dom#}</strong> et
  <strong>{$success}@{#globals.mail.alias_dom2#}</strong> a bien été enregistrée. Après
    vérification, tu recevras un email te signalant l'ouverture de ces adresses.
  </p>
  <p>
    Encore merci de nous faire confiance pour tes emails&nbsp;!
  </p>
{else}
  <h1>
    Adresses email personnalisées
  </h1>

{if $current}
  {if $user->hasProfile()}
  <table class="flags">
    <tr>
      <td class="orange">
        <input type="checkbox" {if $mail_public}checked="checked"{/if}
            onclick="$.xget('{$globals->baseurl}/emails/alias/set/'+(this.checked?'private':'hidden')+'?token={xsrf_token}');
                $('#mail_public').html((this.checked?'public et apparaît donc sur ta fiche':'privé et n\'apparaît nulle part sur le site') + '.');
            " />
      </td>
      <td>
        Ton alias est actuellement&nbsp;: <strong>{$current}</strong>. Il est pour l'instant
        <span id="mail_public">{if $mail_public}public et apparaît donc sur ta fiche.{else}privé et n'apparaît nulle part sur le site.{/if}</span>
      </td>
    </tr>
  </table>
  {/if}
{else}
  <p>
    Pour plus de <strong>convivialité</strong> dans l'utilisation de tes emails, tu peux choisir une adresse
    email discrète et personnalisée. Ce nouvel email peut par exemple correspondre à ton surnom.
  </p>
{/if}

  <p>
    Pour de plus amples informations sur ce service, nous t'invitons à consulter
    <a href="Xorg/AliasMelix">cette documentation</a> qui répondra
    sans doute à toutes tes questions.
  </p>

  {if $current}
  <p>
  <strong>Note&nbsp;: tu as déjà l'alias {$current}, or tu ne peux avoir qu'un seul alias à la fois.
    Si tu effectues une nouvelle demande l'ancien alias sera effacé.</strong>
  </p>
  {/if}

  {if $request}
  <p>
  <strong>Note&nbsp;: tu as déjà effectué une demande pour {$request->alias}, dont le traitement est
    en cours. Si tu souhaites modifier ceci refais une demande, sinon ce n'est pas la peine.</strong>
  </p>
  {/if}

  <br />
  <form action="emails/alias/ask" method="post">
    {xsrf_token_field}
    <table class="bicol" cellpadding="4" summary="Demande d'alias">
      <tr>
        <th>Demande d'alias</th>
      </tr>
      <tr>
        <td>Alias demandé&nbsp;:</td>
      </tr>
      <tr>
        <td><input type="text" name="alias" value="{$r_alias}" />@{#globals.mail.alias_dom#} et @{#globals.mail.alias_dom2#}</td>
      </tr>
      {if $user->hasProfile()}
      <tr>
        <td>
          <table class="flags" summary="Flags" cellpadding="0" cellspacing="0">
            <tr>
              <td class="orange">
                <input type="checkbox" name="public" {if $mail_public}checked="checked"{/if} id="pub_add"/>
              </td>
              <td class="texte">
                <label for="pub_add">adresse publique (apparaît sur ta fiche).</label>
              </td>
             </tr>
          </table>
        </td>
      </tr>
      {/if}
      <tr>
        <td>Brève explication&nbsp;:</td>
      </tr>
      <tr>
        <td><textarea rows="5" cols="50" name="reason">{$r_reason}</textarea></td>
      </tr>
      <tr>
        <td><input type="submit" name="submit" value="Envoyer" /></td>
      </tr>
    </table>
  </form>
  {if $current}
  <form action="emails/alias/delete" method="post"
      onsubmit="return confirm('Es-tu sûr de vouloir supprimer {$current}&nbsp;?')">
    {xsrf_token_field}
    <table class="bicol" cellpadding="4" summary="Suppression d'alias">
      <tr>
        <th>Suppression d'alias</th>
      </tr>
      <tr>
        <td class="center">
          <input type="submit" value="Supprimer l'alias {$current}" />
        </td>
      </tr>
    </table>
  </form>
  {/if}
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
