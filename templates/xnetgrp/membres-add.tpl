{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2015 Polytechnique.org                             *}
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

<h1>{$asso->nom}&nbsp;: Ajout d'un membre</h1>

<form method="post" action="{$platal->ns}member/new/">
  {xsrf_token_field}
  <ul class='descr'>
    <li>
      Pour ajouter un X dans ton groupe, il suffit d'entrer ici une de ses
      adresses email @polytechnique.org. Celui-ci sera prévenu de son inscription.
    </li>
    <li>
      S'il s'agit d'un X qui n'est pas inscrit à Polytechnique.org, il faut
      indiquer l'adresse email puis cocher la case qui se trouve sous le
      formulaire et indiquer ses nom, prénom et/ou promotion pour le retrouver.
    </li>
    <li>
      Pour ajouter un extérieur dans ton groupe, il suffit d'entrer ici son
      adresse email, tu seras ensuite redirigé vers une page te permettant
      d'éditer son profil (nom, prenom&hellip;).
    </li>
  </ul>
  <table class="tinybicol">
    <tr>
      <td class="center" colspan="2">
        <input type="text" id="email" name="email" size="40" value="{if t($platal->argv[1])}{$platal->argv[1]}{/if}" />
        <input type="hidden" name="force_continue" value="{if t($force_continue)}1{else}0{/if}" />
        <input type='submit' value='Ajouter'
          onclick='this.form.action += this.form.email.value' />
      </td>
    </tr>
    {include file="xnetgrp/members_new_form.tpl" registered=false}
  </table>
</form>
{literal}
<script type="text/javascript">
  $("#email").focus();
</script>
{/literal}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
