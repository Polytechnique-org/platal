{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2006 Polytechnique.org                             *}
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

<img src='{$platal->ns}logo' alt="LOGO" style="float: right;" />

<h1>{$asso.nom} : Éditer l'accueil</h1>

<form method="post" action="{$platal->ns}edit" enctype="multipart/form-data">
  {if $super}
  <table cellpadding="0" cellspacing="0" class='tiny'>
    <tr>
      <td class="titre">
        Nom:
      </td>
      <td>
        <input type="text" size="40" value="{$asso.nom}" name="nom" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Diminutif:
      </td>
      <td>
        <input type="text" size="40" value="{$asso.diminutif}" name="diminutif" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Domaine DNS:
      </td>
      <td>
        <input type="text" size="40" value="{$asso.mail_domain}" name="mail_domain" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Catégorie :
      </td>
      <td>
        <select name="cat">
          <option value="groupesx" {if $asso.cat eq GroupesX}selected="selected"{/if}>Groupes X</option>
          <option value="binets" {if $asso.cat eq Binets}selected="selected"{/if}>Binets</option>
          <option value="promotions" {if $asso.cat eq Promotions}selected="selected"{/if}>Promotions</option>
          <option value="institutions" {if $asso.cat eq Institutions}selected="selected"{/if}>Institutions</option>
        </select>
      </td>
    </tr>
    <tr>
      <td class="titre">
        Domaine:
      </td>
      <td>
        <select name="dom">
          <option value=""></option>
          {iterate from=$dom item=d}
          <option value="{$d.id}" {if $d.id eq $asso.dom}selected="selected"{/if}>{$d.nom} [{$d.cat}]</option>
          {/iterate}
        </select>
      </td>
    </tr>
  </table>
  <p></p>
  {/if}
  <table cellpadding="0" cellspacing="0" class='tiny'>
    <tr>
      <td class="titre">
        Logo:
      </td>
      <td>
        <input type="file" name="logo" />
      </td>
    </tr>

    <tr>
      <td class="titre">
        Site Web:
      </td>
      <td>
        <input type="text" size="40" value="{$asso.site}" name="site" />
      </td>
    </tr>

    <tr>
      <td class="titre">
        Contact:
      </td>
      <td>
        <input type="text" size="40" name="resp" value="{$asso.resp}" />
      </td>
    </tr>

    <tr>
      <td class="titre">
        Adresse mail:
      </td>
      <td>
        <input type="text" size="40" name="mail" value="{$asso.mail}" />
      </td>
    </tr>

    <tr>
      <td class="titre">
        Forum:
      </td>
      <td>
        <input type="text" size="40" name="forum" value="{$asso.forum}" />
      </td>
    </tr>

    <tr>
      <td class="titre">
        Inscription possible:
      </td>
      <td>
        <input type="radio" value="1" id="inscr_yes"
          {if $asso.inscriptible eq 1}checked="checked"{/if}
          name="inscriptible" />
        <label for="inscr_yes">oui</label>
        <input type="radio" value="0" id="inscr_no"
          {if $asso.inscriptible neq 1}checked="checked"{/if}
          name="inscriptible" />
        <label for="inscr_no">non</label>
      </td>
    </tr>

    <tr>
      <td class="titre">
        Lien pour l'inscription:<br />
        <em>laisser vide par défaut</em>
      </td>
      <td>
        <input type="text" size="40" name="sub_url" value="{$asso.sub_url}" />
      </td>
    </tr>

    <tr>
      <td class="titre center" colspan="2">
        <input type="checkbox" value="1" name="ax" {if $asso.ax}checked="checked"{/if} />
        groupe agréé par l'AX
      </td>
    </tr>

    <tr>
      <td class="titre center" colspan="2">
        <input type="checkbox" value="1" name="pub" {if $asso.pub eq 'private'}checked="checked"{/if} />
        liste des membres privée
      </td>
  </table>

  <div class="center">
    <br />
    <textarea name="descr" cols="70" rows="15">{$asso.descr}</textarea>
    <input type="submit" name="submit" value="Enregistrer" />
  </div>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
