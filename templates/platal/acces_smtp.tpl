{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2008 Polytechnique.org                             *}
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
{if $actif}Modification du mot de passe SMTP/NNTP{else}Activation de ton compte SMTP/NNTP{/if}  
</h1>

{literal}
<script type="text/javascript">
  <!--
  function CheckResponse() {
    pw1 = document.forms.smtppass_form.smtppass1.value;
    pw2 = document.forms.smtppass_form.smtppass2.value;
    if (pw1 != pw2) {
      alert ("\nErreur&nbsp;: les deux champs ne sont pas identiques !");
      exit;
      return false;
    }
    if (pw1.length < 6) {
      alert ("\nErreur&nbsp;: le nouveau mot de passe doit faire au moins 6 caractères !");
      exit;
      return false;
    }
    document.forms.smtppass_form.op.value='Valider';
    document.forms.smtppass_form.submit();
    return true;
  }

  function SupprimerMdp() {
    document.forms.smtppass_form.op.value='Supprimer';
    document.forms.smtppass_form.submit();
  }
  // -->
</script>
{/literal}

<p>
{if $actif}
  Clique sur <strong>"Supprimer"</strong> si tu veux supprimer ton compte SMTP/NNTP.
{else}
  Pour activer un compte SMTP/NNTP sur <strong>ssl.polytechnique.org</strong>, tape un mot de passe ci-dessous.
{/if}
</p>
<form action="password/smtp" method="post" id="smtppass_form" style="margin-top: 1em">
  <table class="tinybicol" cellpadding="3" summary="Définition du mot de passe">
    <tr>
      <td class="titre">
        Mot de passe (15 caractères maximum)&nbsp;:
      </td>
      <td>
        <input type="password" size="15" maxlength="15" name="smtppass1" />
      </td>
    </tr>
    <tr>
      <td class="titre">
        Retape-le une fois (pour vérification)&nbsp;:
      </td>
      <td>
        <input type="password" size="15" maxlength="15" name="smtppass2" />
      </td>
    </tr>
    <tr>
      <td class="titre">Sécurité</td>
      <td>{checkpasswd prompt="smtppass1" submit="valid"}</td>
    </tr>
    <tr>
      <td colspan="2" class="center">
        <input type="hidden" name="op" value="" />
        <input type="submit" value="Valider" name="valid" onclick="CheckResponse(); return false;" />
{if $actif}
        &nbsp;&nbsp;<input type="submit" value="Supprimer" onclick="SupprimerMdp();" />
{/if}
      </td>
    </tr>
  </table>
</form>
<p>
  {icon name=error title="Attention"} Nous te déconseillons fortement d'utiliser le même
  mot de passe que pour la connexion au site. En effet ce mot de passe sert à accéder à des
  services <em>moins</em> sécurisés qui nécessitent l'enregistrement de celui-ci en clair
  dans notre base de données.
</p>
<p>
  Ce mot de passe peut être le même que celui d'accès au site. Il doit faire au
  moins <strong>6 caractères</strong> quelconques. Attention au type de clavier que tu
  utilises (qwerty?) et aux majuscules/minuscules.
</p>

{if $smarty.request.doc eq "nntp"}
<p>
  <a href="{$platal->pl_self()}?doc=smtp">Pourquoi et comment</a> utiliser le serveur SMTP de {#globals.core.sitename#}.<br />
</p>
{include wiki=Xorg.NNTPSecurise}
{elseif $smarty.request.doc eq "smtp"}
<p> 
  <a href="{$platal->pl_self()}?doc=nntp">Pourquoi et comment</a> utiliser le serveur NNTP de {#globals.core.sitename#}.<br />
</p>
{include wiki=Xorg.SMTPSecurise}
{else}
<p>
  <br />
  <a href="{$platal->pl_self()}?doc=smtp">Pourquoi et comment</a> utiliser le serveur SMTP de {#globals.core.sitename#}.<br />
  <a href="{$platal->pl_self()}?doc=nntp">Pourquoi et comment</a> utiliser le serveur NNTP de {#globals.core.sitename#}.
</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
