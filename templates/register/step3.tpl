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

{include file="register/breadcrumb.tpl"}

{if $smarty.session.subState.forlife}

<h1>Formulaire de pré-inscription</h1>

<form action="register" method="post" id="changepass2">
  {if $smarty.session.subState.emailXorg2}
  <p>
  Tu n'as pour le moment aucun homonyme dans notre base de données. Nous allons
  donc te donner l'adresse <strong>{$smarty.session.subState.bestalias}@{$smarty.session.subState.main_mail_domain}</strong>,
  en plus de ton adresse à vie <strong>{$smarty.session.subState.forlife}@{$smarty.session.subState.main_mail_domain}</strong>.
  Note que tu pourrais perdre l'adresse <strong>{$smarty.session.subState.bestalias}@{$smarty.session.subState.main_mail_domain}</strong>
  si un homonyme s'inscrivait, même si cela reste assez rare.
  </p>
  {else}
  <p>
  Tu as déjà un homonyme inscrit dans notre base de données, dans une autre promotion. Nous allons
  donc te donner l'adresse <strong>{$smarty.session.subState.bestalias}@{$smarty.session.subState.main_mail_domain}</strong>, en plus
  de ton adresse à vie <strong>{$smarty.session.subState.forlife}@{$smarty.session.subState.main_mail_domain}</strong>.
  </p>
  {/if}

  <p>
  Ces adresses sont des redirections vers une ou plusieurs adresses email de ton choix.
  Indiques-en une pour terminer ton inscription. Tu pourras la modifier ou ajouter d'autres
  adresses une fois inscrit.
  </p>
  <p>
  Attention, cette adresse doit <strong>impérativement être valide</strong> pour que nous puissions
  t'envoyer tes informations de connexion.
  </p>

  <p>Nous te demandons également un <strong>mot de passe</strong>, qui te permettra de te reconnecter au site ultérieurement.
  Pour une sécurité optimale, ton mot de passe ne circulera jamais en clair, et sera stocké sous une forme chiffrée
  irréversiblement sur nos serveurs.
  </p>


  <table class="bicol">
    <tr>
      <th colspan="2">
        Contact et sécurité
      </th>
    </tr>
    <tr>
      <td class="titre">
        Email<br />
        <span class="smaller">(adresse de ton choix pour reçevoir tes emails)</span>
      </td>
      <td>
        <input type="text" size="35" maxlength="255" name="email" value="{$smarty.post.email}" />
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">
        Date de naissance<br />
        <span class="smaller">jour/mois/année</span>
      </td>
      <td>
        <input type="text" size="10" maxlength="10" name="birthdate"  value="{$smarty.post.birthdate}" />
        (demandée si tu perds ton mot de passe)
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">
        Mot de passe<br />
        <span class="smaller">au moins 6 caractères</span>
      </td>
      <td>
        <input type="hidden" name="pwhash" />
        <input type="password" size="10" maxlength="256" name="password1" />
      </td>
    </tr>
    <tr class="impair">
      <td class="titre">
        Confirmation du mot de passe
      </td>
      <td>
        <input type="password" size="10" maxlength="256" name="password2" /> (retape ton mot de passe)<br />
        {checkpasswd prompt="password1" text="Terminer la pré-inscription"}
      </td>
    </tr>
  </table>
  <table class="bicol">
    <tr>
      <th>Services supplémentaires</th>
    </tr>
    <tr class="impair">
      <td>Pour profiter pleinement de ta nouvelle inscription, nous te proposons&nbsp;:</td>
    </tr>
    <tr class="pair">
      <td>
        <dl>
          <dt><label><input type="checkbox" checked="checked" name="nl" /> lettre mensuelle</label></dt>
          <dd>
            de recevoir chaque mois la lettre mensuelle de Polytechnique.org contenant les activités de la communauté des X.
          </dd>
          <dt><label><input type="checkbox" checked="checked" name="com_letters" /> lettres de la communauté</label></dt>
          <dd>
            de recevoir les informations importantes de l'École, de l'AX, de la FX et de la communauté.
          </dd>
          {if $smarty.session.subState.edu_type eq #Profile::DEGREE_X#}
          <dt><label><input type="checkbox" checked="checked" name="ml_promo" /> ta promotion</label></dt>
          <dd>
            de recevoir les informations plus spécifiques de ta promotion pour pouvoir participer plus facilement aux événements
            qu'elle organise. Nous t'inscrivons donc dans le groupe de la promotion {$smarty.session.subState.promo}.
          </dd>
          {/if}
          <dt><label><input type="checkbox" checked="checked" name="imap" />sauvegardes d'emails</label></dt>
          <dd>
            d'avoir un accès de secours aux 30 derniers jours d'emails reçus sur ton adresse Polytechnique.org.
          </dd>
        </dl>
      </td>
    </tr>
    <tr class="impair">
      <td>Valider mon inscription&nbsp;:</td>
    </tr>
    <tr class="impair">
      <td class="center">
        <input type="submit" name="submitn" value="Continuer" onclick="return hashResponse('password1', 'password2', true, false);" />
      </td>
    </tr>
  </table>
</form>
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
