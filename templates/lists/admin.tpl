{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2018 Polytechnique.org                             *}
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


{if !$details.own}
<p class='erreur'>
Tu n'es pas administrateur de la liste, mais {if $group_admin}du groupe{else}du site{/if}.
</p>
{/if}

{include file="lists/header_listes.tpl" on=admin}

<p>
Pour inscrire un utilisateur, il faut remplir les champs prévus à cet effet en saisissant un de
ses identifiants, de la forme "prenom.nom", ou "prenom.nom.promo" en cas d'homonymie. Pour inscrire plusieurs utilisateurs, les séparer par des espaces.
L'icône {icon name=cross title='retirer membre'} permet de désinscrire de la liste quelqu'un
qui y était abonné.
</p>

{if t($unregistered)}
{if $unregistered|@count neq 0}
<h1>Marketing d'utilisateurs non-inscrits</h1>

<p>
{if $unregistered|@count eq 1}
L'utilisateur suivant n'est pas inscrit à Polytechnique.org. Tu peux l'y inciter en lui faisant envoyer un email de marketing. Une fois inscrit à Polytechnique.org, l'inscription à la liste lui sera automatiquement proposée.
{else}
Les utilisateurs suivants ne sont pas inscrits à Polytechnique.org. Tu peux les y inciter en leur faisant envoyer des
emails de marketing. Une fois inscrits à Polytechnique.org, l'inscription à la liste leur sera automatique proposée.
{/if}
<p>

<script type="text/javascript">
  {literal}
  function showEmail(val, login)
  {
      var span = document.getElementById("mk_s_mail[" + login + "]");
      var state = (val == 'marketu' || val == 'markets') ? '' : 'none';
      span.style.display = state;
  }
  {/literal}
</script>

<form method="post" action='{$smarty.server.REQUEST_URI}'>
  {xsrf_token_field}
  <table class="bicol">
  {foreach from=$unregistered key=login item=it}
    <tr class="{cycle values="pair,impair"}">
      <td>{$login}</td>
      <td>
        Camarade&nbsp;:
        <select name="mk_uid[{$login}]">
        {iterate from=$it item=user}
          <option value="{$user.uid}">{$user.full_name}</option>
        {/iterate}
        </select><br />
        Action*&nbsp;:
        <select name="mk_action[{$login}]" onchange="showEmail(this.value, '{$login}');">
          <option value="none">Aucune</option>
          <option value="marketu">Envoyer un email en ton nom</option>
          <option value="markets">Envoyer un email au nom de Polytechnique.org</option>
          <option value="sub">Lui proposer l'inscription</option>
        </select><br />
        <span id="mk_s_mail[{$login}]" style="display: none">
          Email&nbsp;: <input type="text" name="mk_email[{$login}]" value="" />
        </span>
      </td>
    </tr>
  {/foreach}
  </table>
  <p class="center">
    <input type="submit" name="send_mark" value="Envoyer les marketings !" />
  </p>
</form>

<p class="smaller">
  *La dernière action ajoute simplement la liste de diffusion aux abonnements qui seront proposés au camarade
  lors de son inscription à Polytechnique.org sans pour autant lui envoyer d'email de marketing. Cette action est
  automatique si tu choisis l'envoi d'email.
</p>

{/if}
{/if}

<h1>
  modérateurs de la liste
</h1>

<form method='post' action='{$smarty.server.REQUEST_URI}'>
  {xsrf_token_field}
  <table class='tinybicol' cellpadding='0' cellspacing='0'>
    {include file='lists/display_list.tpl' list=$owners delete='del_owner' no_sort_key='' promo=true}
    <tr class="pair">
      <td class='titre'>Ajouter</td>
      <td colspan="2">
        <input type='text' size='30' name='add_owner' />
        <input type='submit' value='ajouter' />
      </td>
    </tr>
  </table>
</form>


<h1>
  {$np_m|default:"0"} membre{if $np_m > 1}s{/if} dans la liste
</h1>

<form method='post' action='{$smarty.server.REQUEST_URI}' enctype="multipart/form-data">
  {xsrf_token_field}
  <table class='bicol' cellpadding='0' cellspacing='0'>
    {include file='lists/display_list.tpl' list=$members delete='del_member' no_sort_key='' promo=true}
    <tr>
      <th colspan="3">Ajouter</th>
    </tr>
    <tr class="pair">
      <td class="titre">Liste</td>
      <td colspan="2">
        <input type='text' size='40' name='add_member' />
      </td>
    </tr>
    <tr class="pair">
      <td class="titre">ou fichier(*)</td>
      <td colspan="2">
        <input type="file" size="40" name="add_member_file" />*
      </td>
    </tr>
    <tr class="pair">
      <td colspan="3" class="center">
        <input type='submit' value='ajouter' />
      </td>
    </tr>
  </table>
</form>

<div class="smaller">
 * Le fichier doit contenir une adresse email par ligne. Les X doivent être identifiés par une adresse
 @polytechnique.org, @m4x.org ou @melix.net/org.
</div>


{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
