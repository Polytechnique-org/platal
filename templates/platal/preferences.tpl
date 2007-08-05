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

<h1>
  Préférences
</h1>

<table class="bicol" summary="Préférences: services" cellpadding="0" cellspacing="0">
  <tr>
    <th colspan="2">
    Configuration des différents services du site
    </th>
  </tr>
  <tr class="impair">
    <td class="half">
      <h3><a href="emails">Mes adresses de redirection</a></h3>
      <div class='explication'>
        Tu peux configurer tes différentes redirections de mails ici.
      </div>
    </td>
    <td class="half">
      <h3><a href="emails/alias">Mon alias mail @{#globals.mail.alias_dom#}</a></h3>
      <div class='explication'>
        Pour choisir un alias @{#globals.mail.alias_dom#}/{#globals.mail.alias_dom2#} (en choisir un nouveau annule l'ancien).
      </div>
    </td>
  </tr>
  <tr class="pair">
    <td class="half">
      <h3><a href="prefs/webredirect">Ma redirection de page WEB</a></h3>
      <div class='explication'>
        Tu peux configurer tes redirections WEB
        http://www.carva.org/{$smarty.session.bestalias}.
      </div>
    </td>
    <td class="half">
      <h3><a href="prefs/skin">Apparence du site (skins)</a></h3>
      <div class='explication'>
        Tu peux changer les couleurs et les images du site.
      </div>
    </td>
  </tr>
  <tr class="impair">
    <td class="half">
      {if $smarty.session.mail_fmt eq html}
      <h3>
        <a href="javascript:dynpostkv('prefs', 'mail_fmt', 'texte')">Recevoir les mails en format texte</a>
      </h3>
      <div class='explication'>
        Tu recois tous les mails envoyés par le site
        (lettre mensuelle, carnet, ...) de préférence
        <strong>sous forme de html</strong>
      </div>
      {else}
      <h3>
        <a href="javascript:dynpostkv('prefs', 'mail_fmt', 'html')">Recevoir les mails en HTML</a>
      </h3>
      <div class='explication'>
        Tu recois tous les mails envoyés par le site
        (lettre mensuelle, carnet, ...) de préférence
        <strong>sous forme de texte</strong>
      </div>
      {/if}
    </td>
    <td class="half">
      <h3>
        {if $smarty.session.core_rss_hash}
        <a href="javascript:dynpostkv('prefs', 'rss', 0)">Désactiver les fils rss</a>
        {else}
        <a href="javascript:dynpostkv('prefs', 'rss', 1)">Activer les fils rss</a>
        {/if}
      </h3>
      <div class='explication'>
        Ceci te permet d'utiliser les fils rss du site.
        Attention, désactiver puis réactiver les fils en change les URL !
      </div>
    </td>
  </tr>
</table>

<br />

<table class="bicol" summary="Préférences: mdp" cellpadding="3">
  <tr>
    <th>Mots de passe et accès au site</th>
  </tr>
  <tr class="impair">
    <td>
      <h3><a href="password">Changer mon mot de passe pour le site</a></h3>
      <div class='explication'>
        permet de changer ton mot de passe pour accéder au site {#globals.core.sitename#}
      </div>
    </td>
  </tr>
  <tr class="pair">
    <td>
      <h3><a href="password/smtp">Gérer l'accès SMTP et NNTP</a></h3>
      <div class='explication'>
        Pour activer ton compte sur le serveur SMTP et NNTP de {#globals.core.sitename#},
        ou changer le mot de passe correspondant si tu as déjà activé l'accès.
        Cela te permet d'envoyer tes mails plus souplement (SMTP), et de consulter
        les forums directement depuis ton logiciel habituel de courrier électronique.
      </div>
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
