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
 ***************************************************************************}


<h1>
  Création d'une liste de diffusion
</h1>

{if $created}

<p class='erreur'>Demande de création envoyée !</p>

{else}

<p>
N'importe qui peut faire la demande de création d'une mailing-list, il suffit pour cela d'être au
moins 4 polytechniciens inscrits sur le site, et de fournir les informations suivantes concernant la
liste :
</p>

<form action='{$smarty.server.PHP_SELF}' method='post'>
  <table class='bicol' cellspacing='0' cellpadding='2'>
    <tr>
      <th colspan='2'>Caractéristiques de la Liste</th>
    </tr>
    <tr>
      <td class='titre'>Addresse&nbsp;souhaitée&nbsp;:</td>
      <td>
        <input type='text' name='liste' value='{$smarty.post.liste}' />@polytechnique.org
      </td>
    </tr>
    <tr>
      <td class='titre'>Sujet (bref) :</td>
      <td>
        <input type='text' name='desc' size='50' value="{$smarty.post.desc}" />
      </td>
    </tr>
    <tr>
      <td class='titre'>Propriétés :</td>
      <td>
        <table style='width: 100%'>
          <tr>
            <td>visibilité :</td>
            <td><input type='radio' name='advertise' value='0'
              {if $smarty.post.advertise eq 0 && $smarty.post}checked='checked'{/if} />publique</td>
            <td><input type='radio' name='advertise' value='1'
              {if $smarty.post.advertise neq 0 || !$smarty.post}checked='checked'{/if} />privée</td>
            <td></td>
          </tr>
          <tr>
            <td>diffusion :</td>
            <td><input type='radio' name='modlevel' value='0'
              {if !$smarty.post.modlevel}checked='checked'{/if} />libre</td>
            <td><input type='radio' name='modlevel' value='1'
              {if $smarty.post.modlevel eq 1}checked='checked'{/if} />restreinte</td>
            <td><input type='radio' name='modlevel' value='2'
              {if $smarty.post.modlevel eq 2}checked='checked'{/if} />modérée</td>
          </tr>
          <tr>
            <td>inscription :</td>
            <td><input type='radio' name='inslevel' value='0'
              {if $smarty.post.inslevel eq 0 && $smarty.post}checked='checked'{/if} />libre</td>
            <td><input type='radio' name='inslevel' value='1'
              {if $smarty.post.inslevel neq 0 || !$smarty.post}checked='checked'{/if} />modérée</td>
            <td></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr><th colspan='2'>Membres et Gestionnaires</th></tr>
    <tr>
      <td class='titre'>Gestionnaires</td>
      <td>
        <input type='hidden' name='owners' value='{$owners}' />
        {$owners|nl2br|default:"<span class='erreur'>pas de gestionnaires</span>"}
        <br />
        <input type='text' name='add_owner' />
        <input type='submit' name='add_owner_sub' value='Ajouter' />
      </td>
    </tr>
    <tr>
      <td class='titre'>Membres</td>
      <td>
        <input type='hidden' name='members' value='{$members}' />
        {$members|nl2br|default:"<span class='erreur'>pas de membres</span>"}
        <br />
        <input type='text' name='add_member' />
        <input type='submit' name='add_member_sub' value='Ajouter' />
      </td>
    </tr>
  </table>
  <p>
  La création de la liste sera soumise à un contrôle manuel avant d'être validée.  Ce contrôle a
  pour but notamment de vérifier qu'il n'y aura pas ambiguité entre les membres de la liste et son
  identité.  Exemple: n'importe qui ne peut pas ouvrir pointgamma@polytechnique.org, il ne suffit
  pas d'être le premier à le demander :-)
  </p>
  <p>
  La liste est habituellement créée dans les jours qui suivent la demande sauf exception.  Pour plus
  d'informations écris-nous à l'adresse {mailto address='listes@polytechnique.org'} en mettant dans
  le sujet de ton mail le nom de la liste souhaité afin de faciliter les échanges de mails
  ultérieurs éventuels.
  </p>
  <div class='center'>
    <br />
    <input type='submit' name='submit' value='Soumettre' />
  </div>
</form>

{/if}


{* vim:set et sw=2 sts=2 sws=2: *}
