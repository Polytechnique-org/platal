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


<h1>Redirection de page WEB</h1>

<h2>Pourquoi une redirection de page WEB ?</h2>
<p>
  Dans la lignée du service de redirection d'emails de <strong>{#globals.core.sitename#}</strong>,
  il est possible de faire pointer
    les adresses <strong>http://www.carva.org/{$smarty.session.bestalias}</strong>
  et <strong>http://www.carva.org/{$smarty.session.forlife}</strong>
    vers la page WEB de ton choix. Pour de plus amples détails, consulte
  <a href="Xorg/MaRedirectionWeb">cette page</a>
</p>

<h2>Conditions d'usage</h2>
<p>
  L'utilisateur s'engage à ce que le contenu du site référencé soit en conformité
  avec les lois et règlements en vigueur et d'une manière générale ne porte pas
  atteinte aux droits des tiers
  (<a href="Xorg/MaRedirectionWeb">plus de précisions</a>).
</p>

<h1>
  Mise en place de la redirection
</h1>
<p>
{if $carva}
  Actuellement, les adresses
  <a href="http://www.carva.org/{$smarty.session.bestalias}">
    http://www.carva.org/{$smarty.session.bestalias}
  </a> et <a href="http://www.carva.org/{$smarty.session.forlife}">
    http://www.carva.org/{$smarty.session.forlife}
  </a> sont redirigées sur <a href="http://{$carva}">http://{$carva}</a>
{else}
  La redirection n'est pas utilisée ...
{/if}
</p>

<p>
  Pour modifier cette redirection remplis le champ suivant et clique sur <strong>Valider</strong>.
{if $carva}
  Si tu veux annuler ta redirection, clique sur <strong>Supprimer</strong>.
{/if}
</p>

<br />

<form action="{$smarty.server.REQUEST_URI}" method="post">
  <table class="bicol" summary="[ redirection ]">
    <tr>
      <th colspan="2">
        Adresse de redirection
      </th>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <strong>http://</strong>&nbsp;<input size="50" maxlength="255" name="url"
        value="{$smarty.post.url|default:$carva}" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="submit" value="Valider" name="submit" />
{if $carva}
        <input type="submit" value="Supprimer" name="submit" />
{/if}
      </td>
    </tr>
  </table>
</form>

{* vim:set et sw=2 sts=2 sws=2: *}
