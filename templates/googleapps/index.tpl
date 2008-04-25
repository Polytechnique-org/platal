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

<h1>Ton compte Google Apps pour Polytechnique.org</h1>

<p>Polytechnique.org te fournit un compte <b>Google Apps</b> qui te permet de disposer
  des applications web de Google (<a href="http://mail.google.com/a/polytechnique.org/">GMail</a>,
  <a href="http://www.google.com/calendar/hosted/polytechnique.org/">Google Calendar</a>,
  <a href="http://docs.google.com/a/polytechnique.org/">Google Docs</a>, et bien d'autres)
  sur ton adresse Polytechnique.org habituelle (<a href="Xorg/GoogleApps">en savoir plus</a>).
</p>

{if !$account->g_status}
{if !$has_password_sync}
<p>Tu peux créer ce compte indépendant <i>Google Apps pour Polytechnique.org</i> en utilisant le
  formulaire ci-dessous.
</p>
<p><b>Attention :</b> ce compte Google Apps est hébergé par <b>Google</b>, et non
  par Polytechnique.org. Nous te déconseillons d'y conserver des informations
  confidentielles ou sensibles !<br />&nbsp;
</p>
{/if}

<table class="bicol" id="create">
  <col width="8%" />
  <col width="92%" />
  <tr>
    <th colspan="2">Création de ton compte Google Apps</th>
  </tr>

  {if !$has_password_sync}
    <form action="googleapps/create" method="get">
    <tr class="impair">
      <td colspan="2"><b>Mot de passe du compte :</b></td>
    </tr>
    <tr class="impair">
      <td style="text-align: center">
        <input type="radio" name="password_sync" value="1" checked="checked" id="pwsync_true" />
      </td>
      <td>
        <label for="pwsync_true">Utiliser ton mot de passe Polytechnique.org pour ton compte Google Apps.</label><br />
        <span style="font-size: smaller; font-style: italic;">
          Les futurs changements de ton mot de passe seront répercutés sur ton compte Google Apps.
        </span><br />
        <span style="font-size: smaller; font-weight: bold;">
          Réglage recommandé par Polytechnique.org.
        </span>
      </td>
    </tr>
    <tr class="impair">
      <td style="text-align: center">
        <input type="radio" name="password_sync" value="0" id="pwsync_false" />
      </td>
      <td>
        <label for="pwsync_false">Utiliser un nouveau mot de passe pour ton compte Google Apps.</label>
      </td>
    </tr>
    <tr class="impair">
      <td colspan="2" style="text-align: center">
        <input type="submit" value="Continuer &gt;&gt;" />
      </td>
    </tr>
    </form>
  {else}
    <tr class="impair">
      <td colspan="2"><b>Mot de passe du compte :</b></td>
    </tr>
    {if $password_sync}
      <tr class="impair">
        <td></td>
        <td>
          Tu as choisi d'avoir le même mot de passe pour Google Apps et Polytechnique.org.<br />
          Tu peux encore choisir d'utiliser des <a href="googleapps/create?password_sync=0">mots de passe différents</a>.
        </td>
      </tr>
    {else}
      <tr class="impair">
        <td></td>
        <td>Tu as choisi d'avoir un nouveau mot de passe pour ton compte Google Apps :</td>
      </tr>
      <tr class="impair">
        <td colspan="2">
          <form action="#" method="post" id="changepass">
          <table class="tinybicol">
            <tr>
              <td class="titre">Nouveau mot de passe</td>
              <td><input type="password" name="nouveau" onfocus="document.forms.changepass2.password_sync[1].checked = true;" /></td>
            </tr>
            <tr>
              <td class="titre">Vérification</td>
              <td><input type="password" name="nouveau2" onfocus="document.forms.changepass2.password_sync[1].checked = true;" /></td>
            </tr>
          </table>
          </form>
        </td>
      </tr>
      <tr class="impair">
        <td></td>
        <td>
          Tu peux encore choisir d'<a href="googleapps/create?password_sync=1">avoir des mots de passes synchronisés</a>.
        </td>
      </tr>
    {/if}

    <form action="googleapps/create" method="post" id="changepass2">
    {xsrf_token_field}
    <tr class="pair">
      <td colspan="2"><b>Redirection des emails :</b></td>
    </tr>
    <tr class="pair">
      <td style="text-align: center">
        <input type="radio" name="redirect_mails" value="1" checked="checked" id="redirection_true" />
      </td>
      <td>
        <label for="redirection_true">Ajouter une redirection de mes emails vers mon compte Google Apps.</label><br />
        <span style="font-size: smaller;">
          Tes <em>Polytechnique.org</em> seront redirigés vers ton nouveau webmail, en plus de tes redirections actuelles.<br /><br />
          <strong>Attention : ton compte Google Apps est hébergé par <em>Google</em>.</strong><br />
          Si tu utilises tes adresses <em>Polytechnique.org</em> pour des communications confidentielles ou dans un cadre professionnel,
          nous te déconseillons donc de rediriger tes emails vers Google Apps.
        </span>
      </td>
    </tr>
    <tr class="pair">
      <td colspan="2" style="text-align: center">- ou -</td>
    </tr>
    <tr class="pair">
      <td style="text-align: center">
        <input type="radio" name="redirect_mails" value="0" id="redirection_false" />
      </td>
      <td>
        <label for="redirection_false">Ne <i>pas</i> rediriger mes emails vers mon compte Google Apps.</label><br />
        <span style="font-size: smaller;">
          Tu ne pourras pas lire tes emails dans ton nouveau webmail Google Apps.<br />
          <strong>Ce réglage n'est pas recommandé par Polytechnique.org.</strong>
        </span>
      </td>
    </tr>

    <tr class="impair">
      <td colspan="2"><b>Création du compte :</b></td>
    </tr>
    <tr class="impair">
      <td></td>
      <td>La mise en place du compte Google Apps prends quelques minutes. Tu recevras un email explicatif dès l'opération terminée.</td>
    </tr>
    <tr class="impair">
      <td colspan="2" style="text-align:center">
        <input type="hidden" name="password_sync" value="{$password_sync}" />
        {if $password_sync}
          <input type="submit" value="Créer mon compte !" />
        {else}
          <input type="hidden" name="response2"  value="" />
          <input type="submit" value="Créer mon compte !" onclick="EnCryptedResponse(); return false;" />
        {/if}
      </td>
    </tr>
    </form>
  {/if}
</table>

{elseif $account->g_status eq 'unprovisioned' or $account->pending_create}
<br />
<table class="bicol" id="status">
  <tr>
    <th>État de ton compte</th>
  </tr>
  <tr class="impair">
    <td>
      Ton compte Google Apps est en cours de création.<br />
      Tu recevras un email dès que l'opération sera terminée.
    </td>
  </tr>
</table>

{elseif $account->pending_delete}
<br />
<table class="bicol" id="status">
  <tr>
    <th>État de ton compte</th>
  </tr>
  <tr class="impair">
    <td>
      Ton compte Google Apps est en cours de suppression.
    </td>
  </tr>
</table>

{elseif $account->suspended() or $account->pending_update_suspension}
<br />
<table class="bicol" id="status">
  <tr>
    <th>État de ton compte</th>
  </tr>
  <tr class="impair">
    <td>
      Ton compte Google Apps est actuellement <b>désactivé</b>. Tu ne reçois donc plus aucun
      message sur ce compte.
    </td>
  </tr>
  {if $account->pending_validation_unsuspend or ($account->suspended() and $account->pending_update_suspension)}
  <tr class="pair">
    <td>
      La réactivation de ton compte est en attente de validation.<br />
      Tu recevras un email dès que l'opération sera terminée.
    </td>
  </tr>
  {elseif !$account->suspended() and $account->pending_update_suspension}
  <tr class="pair">
    <td>
      Les opérations de désactivation de ton compte Google Apps ne sont pas terminées, tu ne peux donc pas encore demander sa réactivation.
    </td>
  </tr>
  {else}
  {if $account->g_suspension}
  <tr class="pair">
    <td>
      Ton compte est actuellement suspendu pour la raison suivante: <em>{$account->g_suspension}</em>.
    </td>
  </tr>
  {/if}
  <tr class="impair">
    <td>
      La réactivation de ton compte est soumise à une validation manuelle par un administrateur de Polytechnique.org.
      Ton compte réactivé contiendra toutes tes anciennes données.
      <br /><br />
      <div class="center">
        <form action="googleapps/unsuspend" method="post">
          <input type="hidden" name="redirect_mails" value="1" />
          <input type="submit" name="unsuspend" value="Réactiver mon compte Google Apps et y rediriger mes emails" />
        </form>
        <br />
        <form action="googleapps/unsuspend" method="post">
          <input type="hidden" name="redirect_mails" value="0" />
          <input type="submit" name="unsuspend" value="Réactiver mon compte Google Apps et ne pas y rediriger mes emails" />
        </form>
      </div><br />
      Tu pourras toujours <a href="emails/redirect">changer la redirection de tes emails</a> plus tard.
    </td>
  </tr>
  {/if}
</table>

{else}
<p>Tu peux utiliser ces services :</p>
<ul>
  <li>Soit en passant par la <a href="http://google.polytechnique.org/">la page d'accueil Google de Polytechnique.org</a> ;</li>
  <li>Soit en utilisant directement les différents services :
    <ul>
      <li>Pour tes mails, sur le <a href="http://mail.google.com/a/polytechnique.org/">GMail de Polytechnique.org</a> ;</li>
      <li>Pour ton calendrier, sur <a href="http://www.google.com/calendar/hosted/polytechnique.org/">Google Calendar</a> ;</li>
      <li>Pour tes documents, sur <a href="http://docs.google.com/a/polytechnique.org/">Google Docs</a>.</li>
    </ul>
  </li>
</ul>

<table class="bicol" id="status">
  <tr>
    <th>État de ton compte</th>
  </tr>
  <tr class="impair">
    <td>Ton compte <b>{$account->g_account_name}</b> existe{if $account->r_creation} depuis le {$account->r_creation|date_format:"%d/%m/%Y"}{/if}.</td>
  </tr>
  {if $account->reporting_date and $account->r_disk_usage}
  <tr class="pair">
    <td>Au {$account->reporting_date|date_format:"%d %B %Y"}, tu avais {$account->r_disk_usage/1024/1024|string_format:"%.1f"} Mo de mails.</td>
  </tr>
  {/if}
  <tr class="impair">
    {if $redirect_active and $redirect_unique}
    <td>
      Ta seule adresse de redirection de tes mails est celle de ton compte Google Apps.<br />
      Si tu souhaites désactiver celui-ci, tu dois d'abord <a href="emails/redirect">ajouter une nouvelle adresse de redirection</a>.
    </td>
    {else}
    <td>
      Si tu ne souhaites plus utiliser ton compte, tu peux le désactiver :<br /><br />
      <div class="center">
        <form action="googleapps/suspend" method="post">
          {xsrf_token_field}
          <input type="submit" name="suspend" value="Désactiver mon compte Google Apps" />
        </form>
      </div>
      <div style="margin-top: 0.5em">
        {icon name=error} Une fois ton compte désactivé, tu ne pourras plus accéder à tes mails sur Google Apps.<br />
        {icon name=information} La réactivation est possible, mais nécessite d'être validée par un administrateur.
      </div>
    </td>
    {/if}
  </tr>
</table>
<br />

<table class="bicol" id="password">
  <tr>
    <th>Ton mot de passe Google Apps</th>
  </tr>
  {if $account->sync_password}
  <tr class="impair">
    <td>
      Le mot de passe de ton compte Google Apps est actuellement celui que tu utilises pour
      Polytechnique.org. Tu peux :
    </td>
  </tr>

  {if $account->pending_update_password}
  <tr class="pair">
    <td><div class="erreur">
      Ton mot de passe est en cours de changement.<br />
      Tu pourras à nouveau le modifier d'ici quelques secondes.
    </div></td>
  </tr>
  {else}
  <tr class="impair">
    <td>
      <ul style="margin-top: 0">
        <li><a href="password">Changer ce mot de passe commun</a></li>
        <li><a href="googleapps/password/nosync#password">Ne plus répercuter les changements de mot de passe vers Google Apps</a></li>
      </ul>
    </td>
  </tr>
  {/if}
  {else}
  <tr class="impair">
    <td>
      Tu as actuellement deux mots de passes indépendants (pour ton compte Polytechnique.org et pour ton compte Google Apps).
      Tu peux :
    </td>
  </tr>

  {if $account->pending_update_password}
  <tr class="pair">
    <td><div class="erreur">
      Ton mot de passe est en cours de changement.<br />
      Tu pourras à nouveau le modifier d'ici quelques secondes.
    </div></td>
  </tr>
  {else}
  <tr class="impair">
    <td>
      <ul style="margin-top: 0">
        <li style="margin-bottom: 1em">
          <a href="googleapps/password/sync">Choisir d'utiliser le même mot de passe pour les deux comptes.</a><br />
          Attention, cette opération changera ton mot de passe Google Apps.
        </li>
        <li>
          Changer le mot de passe de ton compte Google Apps:<br /><br />
          <form action="googleapps/password#password" method="post" id="changepass">
            <table class="tinybicol">
              <tr>
                <td class="titre">Nouveau mot de passe</td>
                <td><input type="password" name="nouveau" /></td>
              </tr>
              <tr>
                <td class="titre">Vérification</td>
                <td><input type="password" name="nouveau2" /></td>
              </tr>
              <tr>
                <td></td>
                <td><input type="submit" value="Changer" onclick="EnCryptedResponse(); return false;" /></td>
              </tr>
            </table>
          </form>
          <form action="googleapps/password#password" method="post" id="changepass2">
            {xsrf_token_field}
            <input type="hidden" name="response2"  value="" />
          </form><br />
          Pour une sécurité optimale, ton mot de passe circule de manière sécurisée (https).
          Il est chiffré irréversiblement sur nos serveurs, ainsi que sur ceux de Google.
        </li>
      </ul>
    </td>
  </tr>
  {/if}
  {/if}
</table>
<br />

<table class="bicol" id="emails">
  <tr>
    <th>Redirection des emails vers Google Apps</th>
  </tr>

  <tr class="impair">
    {if $redirect_active and !$redirect_unique}
    <td>Tes emails Polytechnique.org sont redirigés vers Google Apps, en plus de tes autres redirections.</td>
    {elseif $redirect_active}
    <td>Tes emails Polytechnique.org ne sont redirigés que vers Google Apps.</td>
    {else}
    <td>Tu ne reçois <em>pas</em> tes emails Polytechnique.org sur ton webmail Google Apps.</td>
    {/if}
  </tr>
  <tr class="pair">
    <td class="center"><a href="emails/redirect">Changer mes redirections mail</a></td>
  </tr>
</table>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
