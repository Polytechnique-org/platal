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


{if $already}

<p>
Merci de nous avoir communiqué cette information !
</p>
<p>
Nous avions déjà connaissance de cette adresse, nous espérons donc comme toi que {$prenom} va s'inscrire au plus vite.
</p>
<p>
Si tu le connais personnellement, un petit mail pour lui expliquer les atouts de Polytechnique.org
peut sans aucun doute l'aider à se décider !
</p>

{elseif $ok}

<p>
  Merci de nous avoir communiqué cette information !  Un administrateur de Polytechnique.org va
  envoyer un email de proposition d'inscription à Polytechnique.org à {$prenom} {$nom} dans les
  toutes prochaines heures (ceci est fait à la main pour vérifier qu'aucun utilisateur malveillant
  ne fasse mauvais usage de cette fonctionnalité...).
</p>
<p>
  <strong>Merci de ton aide à la reconnaissance de notre site !</strong> Tu seras informé par email de
  l'inscription de {$prenom} {$nom} si notre camarade accepte de rejoindre la communauté des X sur
  le web !
</p>

{else}

{if $prenom}
<h1>
  Et si nous proposions à {$prenom} {$nom} de s'inscrire à Polytechnique.org ?
</h1>

<p>
  En effet notre camarade n'a pour l'instant pas encore rejoint la communauté des X sur le web...
  C'est dommage, et en nous indiquant son adresse email, tu nous permettrais de lui envoyer une
  proposition d'inscription.
</p>
<p>
  Si tu es d'accord, merci d'indiquer ci-dessous l'adresse email de {$prenom} {$nom} si tu la
  connais.  Nous nous permettons d'attirer ton attention sur le fait que nous avons besoin d'être
  sûrs que cette adresse est bien la sienne, afin que la partie privée du site reste uniquement
  accessible aux seuls polytechniciens. Merci donc de ne nous donner ce renseignement uniquement si
  tu es certain de sa véracité !
</p>
<p>
  Nous pouvons au choix lui écrire au nom de l'équipe Polytechnique.org, ou bien, si tu le veux
  bien, en ton nom. A toi de choisir la solution qui te paraît la plus adaptée !! Une fois {$prenom}
  {$nom} inscrit, nous t'enverrons un email pour te prévenir que son inscription a réussi.
</p>

<form method="post" action="{$platal->path}">
  <table class="bicol" summary="Fiche camarade">
    <tr class="impair"><td>Nom :</td><td>{$nom}</td></tr>
    <tr class="pair"><td>Prénom :</td><td>{$prenom}</td></tr>
    <tr class="impair"><td>Promo :</td><td>{$promo}</td></tr>
    <tr class="pair">
      <td>Adresse email :</td>
      <td>
        <input type="text" name="mail" size="30" maxlength="50" />
      </td>
    </tr>
    <tr class="impair">
      <td>Nous lui écrirons :</td>
      <td>
        <input type="radio" name="origine" value="user" checked="checked" /> en ton nom<br />
        <input type="radio" name="origine" value="staff" /> au nom de l'équipe Polytechnique.org
      </td>
    </tr>
  </table>
  <div>
    <br />
    <input type="submit" name="valide" value="Valider" />
  </div>
</form>
{/if}

{/if}


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
