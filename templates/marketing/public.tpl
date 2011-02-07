{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2011 Polytechnique.org                             *}
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
Merci de nous avoir communiqué cette information&nbsp;!
</p>
<p>
Nous avions déjà connaissance de cette adresse, nous espérons donc comme toi que {$full_name} va s'inscrire au plus vite.
</p>
<p>
Si tu le connais personnellement, un petit email pour lui expliquer les atouts de Polytechnique.org
peut sans aucun doute l'aider à se décider&nbsp;!
</p>

{elseif $ok}

<p>
  Merci de nous avoir communiqué cette information&nbsp;!  Un administrateur de Polytechnique.org va
  envoyer un email de proposition d'inscription à Polytechnique.org à {$full_name} dans les
  toutes prochaines heures (ceci est fait à la main pour vérifier qu'aucun utilisateur malveillant
  ne fasse mauvais usage de cette fonctionnalité&hellip;).
</p>
<p>
  <strong>Merci de ton aide à la reconnaissance de notre site&nbsp;!</strong> Tu seras informé par email de
  l'inscription de {$full_name} si notre camarade accepte de rejoindre la communauté des X sur
  le web&nbsp;!
</p>

{else}

{if $full_name}
<h1>
  Et si nous proposions à {$full_name} de s'inscrire à Polytechnique.org&nbsp;?
</h1>

<p>
  En effet notre camarade n'a pour l'instant pas encore rejoint la communauté des X sur le web&hellip;
  C'est dommage, et en nous indiquant son adresse email, tu nous permettrais de lui envoyer une
  proposition d'inscription.
</p>
<p>
  Si tu es d'accord, merci d'indiquer ci-dessous l'adresse email de {$full_name} si tu la
  connais.  Nous nous permettons d'attirer ton attention sur le fait que nous avons besoin d'être
  sûrs que cette adresse est bien la sienne, afin que la partie privée du site reste uniquement
  accessible aux seuls polytechniciens. Merci donc de ne nous donner ce renseignement uniquement si
  tu es certain de sa véracité&nbsp;!
</p>
<p>
  Nous pouvons au choix lui écrire au nom de l'équipe Polytechnique.org, ou bien, si tu le veux
  bien, en ton nom. À toi de choisir la solution qui te paraît la plus adaptée. Une fois
  {$full_name} inscrit, nous t'enverrons un email pour te prévenir que son inscription a réussi.
</p>

<form method="post" action="{$platal->path}">
  {xsrf_token_field}
  <table class="bicol" summary="Fiche camarade">
    <tr class="impair"><td>Nom&nbsp;:</td><td>{$full_name}</td></tr>
    <tr class="impair"><td>Promo&nbsp;:</td><td>{$promo}</td></tr>
    <tr class="pair">
      <td>Adresse email&nbsp;:</td>
      <td>
        <input type="text" name="mail" size="30" maxlength="255" />
      </td>
    </tr>
    <tr class="impair">
      <td>Nous lui écrirons&nbsp;:</td>
      <td>
        <label>
          <input type="radio" name="origine" value="user" checked="checked"
                 onclick="$('#sender').html('{$perso_signature}'); $('#tr_perso').show();
                          $('#personal_notes_display').show();" />
          en ton nom
        </label><br />
        <label>
          <input type="radio" name="origine" value="staff"
                 onclick='$("#sender").html("{include file=include/signature.mail.tpl mail_part=$mail_part}");
                          $("#tr_perso").hide(); $("#personal_notes_display").hide();' />
          au nom de l'équipe Polytechnique.org
        </label>
      </td>
    </tr>
    <tr class="pair" id="tr_perso">
      <td>Texte à ajouter à l'email&nbsp;:</td>
      <td><textarea cols="60" rows="5" name="personal_notes" id="textarea_perso"
                    onkeyup="$('#personal_notes_display').html('<br />' + $('#textarea_perso').val().replace(/\n/g,
                    '<br />') + '<br />');"></textarea>
    </tr>
  </table>
  <div class="center">
    <br />
    <input type="submit" name="valide" value="Valider" />
    <br />
    <br />
  </div>
  <table class="bicol" summary="Texte de l'email">
    <tr>
      <th colspan="2">Texte de l'email</th>
    </tr>
    <tr>
      <td colspan="2">
        {$text|smarty:nodefaults}
      </td>
    </tr>
  </table>
</form>
{/if}

{/if}


{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
