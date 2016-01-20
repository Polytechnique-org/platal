{**************************************************************************}
{*                                                                        *}
{*  Copyright (C) 2003-2016 Polytechnique.org                             *}
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
  Préférences
</h1>

<script type="text/javascript">
{literal}
/* <![CDATA[ */
$(function() {
  $("form input:radio").change(function() {
    $("#form").submit();
  });
}
);
/* ]]> */
{/literal}
</script>

<fieldset>
  <legend>{icon name="wrench"} Paramètres du site</legend>

  <form action="prefs" method="post" id="form">
  {xsrf_token_field}
  <dl>
    {if hasPerm('user')}
    <dt>Apparence du site</dt>
    <dd>
      Tu peux changer l'apparence du site en choisissant une autre skin.<br />
      <a href="prefs/skin">Changer de skin</a>
    </dd>
    {/if}
    <dt>Format des emails envoyés par le site</dt>
    <dd>
      Lorsque le site t'envoie des emails (lettre mensuelle, carnet, ...) ceux-ci peuvent
      être soit sous forme de texte brut, soit formatés à l'aide de html.<br />
      texte brut <input type="radio" name="email_format" value="text" {if $smarty.session.user->email_format neq 'html'}checked="checked"{/if} />
      <input type="radio" name="email_format" value="html" {if $smarty.session.user->email_format eq 'html'}checked="checked"{/if} /> HTML
    </dd>
    <dt>Fils RSS</dt>
    <dd>
      Le site de propose plusieurs fils RSS qui te permettent d'être averti lors, par exemple, de la publication
      de nouvelles annonces, de l'anniversaires de tes contacts ou dès qu'il y a de l'activité sur le forum
      de ta promotion.<br />
      Attention, désactiver puis réactiver les fils RSS en change les URL&nbsp;!<br />
      désactivés <input type="radio" name="rss" value="off" {if !$smarty.session.user->token}checked="checked"{/if} />
      <input type="radio" name="rss" value="on" {if $smarty.session.user->token}checked="checked"{/if} /> activés
    </dd>
    <dt>Mot de passe</dt>
    <dd>
      Tu peux changer ton mot de passe d'accès au site quand tu le souhaites.<br />
      <a href="password">Changer de mot de passe</a>
    </dd>
  </dl>
  </form>
</fieldset>

{if $smarty.session.user->checkPerms('mail')}
<fieldset>
  <legend>{icon name="email"} Paramètres du service de Polytechnique.org</legend>

  <dl>
    <dt>Tes adresses de redirection</dt>
    <dd>
      Tu peux à tout moment changer les boîtes mails vers lesquelles les mails adressés
      à tes adresses polytechniciennes sont redirigés.<br />
      <a href="emails">Gérer tes redirections mail</a>
    </dd>
    <dt>Ton accès SMTP et NNTP</dt>
    <dd>
      Polytechnique.org te permet d'envoyer des emails et de consulter les forums
      directement depuis ton logiciel habituel de courrier électronique. Pour ceci il
      te faut configurer ton mot de passe SMTP et NNTP.<br />
      <a href="password/smtp">Gérer ton accès au SMTP et NNTP sécurisé</a>
    </dd>
    <dt>Ta redirection Web</dt>
    <dd>
      Polytechnique.org te propose, en plus de ta redirection mail, un service de
      redirection web. Ce service te permet de rediriger l'adresse
      http://www.carva.org/{$smarty.session.user->hruid} vers la page de ton choix.<br />
      <a href="prefs/webredirect">Gérer ta redirection Web</a>
    </dd>
    <dt>Paramètres d'envois d'emails depuis le site</dt>
    <dd>
      Tu peux faire retenir au site les paramètres d'envoi par défaut des emails depuis <a href="emails/send">le site</a>.<br />
      <a href="prefs/email">Gérer tes envois d'emails depuis le site</a>
    </dd>
  </dl>
</fieldset>
{/if}

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
