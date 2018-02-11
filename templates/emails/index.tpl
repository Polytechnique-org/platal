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

<h1>Gestion de mes emails</h1>

{literal}
<script type="text/javascript">
  //<![CDATA[
  $(function() {
      var url = '{/literal}{$globals->baseurl}/emails/best/{literal}';
      var tok = '{/literal}{xsrf_token}{literal}';
      var msg = "Le changement a bien été effectué.";
      $(':radio[name=best]').change(function() {
          $("#bestalias-msg").successMessage(url + $(this).val() + '?token=' + tok, msg);
      });
  });
  //]]>
</script>
{/literal}

<fieldset>
  <legend>{icon name="email"} Mes adresses polytechniciennes</legend>

  <div>
    Tes adresses polytechniciennes sont&nbsp;:
    <dl>
    {if $aliases_forlife|@count}
      <dt>Adresses garanties à vie</dt>
      <dd>
        {foreach from=$aliases_forlife item=a}
        <label>
          <input type='radio' {if $a.bestalias}checked="checked"{/if} name='best' value='{$a.email}' />
          <strong>{$a.email}</strong>
        </label>
        {if $a.expire}<span class='erreur'>(expire le {$a.expire|date_format})</span>{/if}
        <br />
        {/foreach}
      </dd>
    {/if}
    <br/>
    {if $aliases_hundred|@count}
      <dt>Adresses garanties 100 ans (*)</dt>
      <dd>
        {foreach from=$aliases_hundred item=a}
        <label>
          <input type='radio' {if $a.bestalias}checked="checked"{/if} name='best' value='{$a.email}' />
          <strong>{$a.email}</strong>
        </label>
        {if $a.expire}<span class='erreur'>(expire le {$a.expire|date_format})</span>{/if}
        <br />
        {/foreach}
      </dd>
    {/if}
    <br/>
    {if $aliases_other|@count}
      <dt>Autres adresses (**)</dt>
      <dd>
        {foreach from=$aliases_other item=a}
        {if strpos($a.email, '@melix.org') === false}
        <label>
          <input type='radio' {if $a.bestalias}checked="checked"{/if} name='best' value='{$a.email}' />
          <strong>{$a.email}</strong>
        </label>
        {if $a.expire}<span class='erreur'>(expire le {$a.expire|date_format})</span>{/if}
        {if $a.alias} et <strong>@melix.org</strong> <a href="emails/alias">(changer ou supprimer mon alias melix)</a>{/if}
        <br />
        {/if}
        {/foreach}
      </dd>
    {/if}
    </dl>
    <p class="smaller">
    L'adresse cochée est celle que tu utilises le plus (et qui sera donc affichée sur ta carte de visite, ta fiche&hellip;).
    <br />Coche une autre case pour en changer&nbsp;!
    </p>

    <p id="bestalias-msg" class="center"></p>
  </div>
  <hr />
  <div>
    (M4X signifie <em>mail for X</em>, son intérêt est de te doter d'une adresse à vie
    moins "voyante" que l'adresse {$main_email_domain}).
    {if !$alias}
    Tu peux ouvrir en supplément une adresse synonyme de ton adresse @{$main_email_domain},
    sur les domaines @{#globals.mail.alias_dom#} et @{#globals.mail.alias_dom2#} (melix = Mél X).<br />
    <div class="center"><a href="emails/alias">Créer un alias melix</a></div>
    {/if}
  </div>
</fieldset>

<p class="smaller">
{assign var="profile" value=$smarty.session.user->profile()}
(*) Ces adresses email te sont réservées pour une période de 100 ans après ton entrée à l'X (dans ton cas, jusqu'en {$profile->yearpromo()+100}).
</p>
<p class="smaller">
{if $aliases_other|@count}(**) {/if}
{if $homonyme}
Tu as un homonyme donc tu ne peux pas profiter de l'alias {$homonyme}@{$main_email_domain}. Si quelqu'un essaie
d'envoyer un email à cette adresse par mégarde il recevra une réponse d'un robot lui expliquant l'ambiguité et lui
proposant les adresses des différents homonymes.
{else}
Si tu venais à avoir un homonyme, l'alias «prenom.nom»@{$main_email_domain} sera désactivé. Si bien que
ton homonyme et toi-même ne disposeraient plus que des adresses de la forme «prenom.nom.promo»@{$main_email_domain}.
{/if}
</p>

<br />

<fieldset>
  <legend>{icon name="email_go"} Où est-ce que je reçois les emails qui m'y sont adressés&nbsp;?</legend>

  <div>
    {if count($mails) eq 0}
    <p class="erreur">
      Tu n'as actuellement aucune adresse de redirection. Tout email qui t'est envoyé sur tes
      adresses polytechniciennes génère une erreur. Modifie au plus vite ta liste de redirection.<br/>
    </p>
    {else}
    Actuellement, tout email qui t'y est adressé, est envoyé
    {if count($mails) eq 1} à l'adresse{else} aux adresses{/if}&nbsp;:
    <ul>
      {foreach from=$mails item=m}
      <li><strong>{$m->display_email}</strong></li>
      {/foreach}
    </ul>
    {/if}
    Si tu souhaites <strong>modifier ce reroutage de tes emails,</strong>
    <a href="emails/redirect">il te suffit de te rendre ici&nbsp;!</a>
    {test_email}
  </div>
</fieldset>

<br />

<fieldset>
  <legend>{icon name="bug_delete" text="Antivirus, antispam"} Antivirus, antispam</legend>

  <p>
    Tous les emails qui te sont envoyés sur tes adresses polytechniciennes sont
    <strong>filtrés par un logiciel antivirus</strong> très performant. Il te protège de ces
    vers très gênants, qui se propagent souvent par email.
  </p>
  <p>
    De même, un <strong>service antispam évolué</strong> est en place. Tu peux lui demander
    de te débarrasser des spams que tu reçois. Pour en savoir plus, et l'activer,
    <a href="emails/antispam">c'est très simple, suis ce lien</a>&nbsp;!
  </p>
</fieldset>

{* vim:set et sw=2 sts=2 sws=2 fenc=utf-8: *}
