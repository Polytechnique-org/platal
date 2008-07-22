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

{include file="register/breadcrumb.tpl"}

{if $smarty.session.sub_state.forlife}

<h1>Formulaire de pré-inscription</h1>

<form action="register" method="post">
  {if $smarty.session.sub_state.mailorg2}
  <p>
  Tu n'as pour le moment aucun homonyme dans notre base de données. Nous allons
  donc te donner l'adresse <strong>{$smarty.session.sub_state.bestalias}@{#globals.mail.domain#}</strong>,
  en plus de ton adresse à vie <strong>{$smarty.session.sub_state.forlife}@{#globals.mail.domain#}</strong>.
  Note que tu pourrais perdre l'adresse <strong>{$smarty.session.sub_state.bestalias}@{#globals.mail.domain#}</strong> 
  si un homonyme s'inscrivait &mdash; cela reste assez rare.
  </p>
  {else}
  <p>
  Tu as déjà un homonyme inscrit dans notre base de données, dans une autre promotion. Nous allons
  donc te donner l'adresse <strong>{$smarty.session.sub_state.bestalias}@{#globals.mail.domain#}</strong>, en plus
  de ton adresse à vie <strong>{$smarty.session.sub_state.forlife}@{#globals.mail.domain#}</strong>.
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
        <input type="text" size="35" maxlength="50" name="email" value="{$smarty.post.email}" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Date de naissance<br />
        <span class="smaller">jour/mois/année</span>
      </td>
      <td>
        <input type="text" size="10" maxlength="10" name="naissance"  value="{$smarty.post.naissance}" />
        (demandée si tu perds ton mot de passe)
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Terminer la pré-inscription" />
      </td>
    </tr>
  </table>
</form>

{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
