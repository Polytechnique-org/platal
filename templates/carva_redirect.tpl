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
        $Id: carva_redirect.tpl,v 1.9 2004-10-09 14:51:30 x2000habouzit Exp $
 ***************************************************************************}


{dynamic on="0$message"}
<div class="rubrique">
  Mise à jour de la redirection
</div>
{$message}
{/dynamic}

<div class="rubrique">
  Redirection de page WEB
</div>

<div class="ssrubrique">
  Pourquoi une redirection de page WEB ?
</div>
<p>
  Dans la lignée du service de redirection d'emails de <strong>Polytechnique.org</strong>,
  il est possible de faire pointer
  {dynamic}
  l'adresse <strong>http://www.carva.org/{$smarty.session.forlife}</strong>
  {/dynamic}
  vers la page WEB de ton choix. Pour de plus amples détails, consulte
  <a href="{"docs/doc_carva.php"|url}">cette page</a>
</p>

<div class="ssrubrique">
  Conditions d'usage
</div>
<p>
  L'utilisateur s'engage à ce que le contenu du site référencé soit en conformité
  avec les lois et règlements en vigueur et d'une manière générale ne porte pas
  atteinte aux droits des tiers
  (<a href="{"docs/doc_carva.php#charte"|url}">plus de précisions</a>).
</p>

<div class="rubrique">
  Mise en place de la redirection
</div>
<p>
{dynamic}
{if $carva}
  Actuellement, l'adresse <a href="http://www.carva.org/{$smarty.session.forlife}">http://www.carva.org/{$smarty.session.forlife}</a>
  {if $alias}
  ainsi que l'adresse <a href="http://www.carva.org/{$alias}">http://www.carva.org/{$alias}</a>
  sont redirigées
  {else}
  est redirigée
  {/if}
  sur <a href="http://{$carva}">http://{$carva}</a>
{else}
  La redirection n'est pas utilisée ...
{/if}
</p>

<p>
  Pour modifier cette redirection remplis le champ suivant et clique sur <strong>Modifier</strong>.
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
{if $carva}
      <td class="center">
        <input type="submit" value="Modifier" name="submit" />
      </td>
      <td class="center">
        <input type="submit" value="Supprimer" name="submit" />
      </td>
{else}
      <td colspan="2" class="center">
        <input type="submit" value="Valider" name="submit" />
      </td>
{/if}
    </tr>
  </table>
</form>
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
