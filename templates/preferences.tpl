{***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: preferences.tpl,v 1.14 2004-11-24 10:12:47 x2000habouzit Exp $
 ***************************************************************************}

<h1>
  Préférences
</h1>

<table class="bicol" summary="Préférences: services" cellpadding="0" cellspacing="0">
  <tr>
    <th colspan="2">
    Configuration des différents services du site
    </th>
  </tr>
  {foreach from="preferences.tpl.d/*.tpl"|glob item=inc key=id name=glob}
  {if $id is even}
  <tr class="{cycle values="impair,pair"}">
  {/if}
    <td class="half">
      {include file=$inc}
    </td>
    {if $id is even && $smarty.foreach.glob.last}
    <td class="half"></td>
    {/if}
  {if $id is odd || $smarty.foreach.glob.last}
  </tr>
  {/if}
  {/foreach}
</table>

<br />

<table class="bicol" summary="Préférences: mdp" cellpadding="3">
  <tr>
    <th>Mots de passe et accès au site</th>
  </tr>
  <tr class="impair">
    <td>
      <h3><a href="{"motdepassemd5.php"|url}">Changer mon mot de passe pour le site</a></h3>
      <div class='explication'>
        permet de changer ton mot de passe pour accéder au site Polytechnique.org
      </div>
    </td>
  </tr>
  <tr class="pair">
    <td>
      <h3><a href="{"acces_smtp.php"|url}">Activer l'accès SMTP et NNTP</a></h3>
      <div class='explication'>
        Pour activer ton compte sur le serveur SMTP et NNTP de Polytechnique.org.
        Cela te permet d'envoyer tes mails plus souplement (SMTP), et de consulter
        les forums directement depuis ton logiciel habituel de courrier électronique.
      </div>
    </td>
  </tr>
  <tr class="impair">
    <td>
      {if $has_cookie}
      <h3><a href="cookie_off.php">Supprimer l'accès permanent</a></h3>
      <div class='explication'>
        Clique sur le lien ci-dessus pour retirer l'accès sans mot de passe au site. Après avoir
        cliqué, tu devras à nouveau entrer ton mot de passe pour accéder aux différentes pages
        comme initialement.
      </div>
      {else}
      <h3><a href="cookie_on.php">Attribuer un cookie d'authentification permanente</a></h3>
      <div class='explication'>
        Cette option te permet de ne plus avoir à entrer ton mot de passe pour la majorité des pages
        du site. Ce dernier reste cependant nécessaire pour le profil ou le changement de mot de
        passe. Il s'agit d'une option destinée aux utilisateurs fréquents du site, plutôt à l'aise
        avec l'informatique, et pour un ordinateur non partagé.
      </div>
      {/if}
    </td>
  </tr>
</table>

{* vim:set et sw=2 sts=2 sws=2: *}
