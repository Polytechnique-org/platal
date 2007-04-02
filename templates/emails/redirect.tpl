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

{if $retour == $smarty.const.ERROR_INACTIVE_REDIRECTION}
  <p class="erreur">
  Tu ne peux pas avoir aucune adresse de redirection active, sinon ton adresse
  {$smarty.session.forlife}@{#globals.mail.domain#} ne fonctionnerait plus.
  </p>
{/if}
{if $retour == $smarty.const.ERROR_INVALID_EMAIL}
  <p class="erreur">
  Erreur: l'email n'est pas valide.
  </p>
{/if}
{if $retour == $smarty.const.ERROR_LOOP_EMAIL}
  <p class="erreur">
  Erreur: {$smarty.session.forlife}@{#globals.mail.domain#} ne doit pas être renvoyé
  vers lui-même, ni vers son équivalent en {#globals.mail.domain2#} ni vers polytechnique.edu.
  </p>
{/if}
  <h1>
    Tes adresses de redirection
  </h1>
  <p>
  Tu configures ici les adresses emails vers lesquelles tes adresses (listées ci-dessous) sont dirigées :
  </p>
  <ul>
    {if $melix}
    <li>
    <strong>{$melix}@{#globals.mail.alias_dom#}</strong>,
    <strong>{$melix}@{#globals.mail.alias_dom2#}</strong>
    </li>
    {/if}
    {foreach from=$alias item=a}
    <li>
    <strong>{$a.alias}@{#globals.mail.domain#}</strong>
    {if $a.expire}<span class='erreur'>(expire le {$a.expire|date_format})</span>{/if}
    </li>
    {/foreach}
  </ul>
  <p>
    Le routage est en place pour les adresses dont la case "<strong>Actif</strong>" est cochée.
    Si tu modifies souvent ton routage, tu as tout intérêt à rentrer toutes les
    adresses qui sont susceptibles de recevoir ton routage, de sorte qu'en
    jouant avec les cases "<strong>Actif</strong>" tu pourras facilement mettre en place les unes
    ou bien les autres.
  </p>
  <p>
    Enfin, la <strong>réécriture</strong> consiste à substituer à ton adresse email habituelle
    (adresse wanadoo, yahoo, free, ou autre) ton adresse {#globals.mail.domain#} ou
    {#globals.mail.domain2#} dans l'adresse d'expédition de tes messages, lorsque le courrier
    passe par nos serveurs. Ceci arrive lorsque tu écris à un camarade sur son adresse {#globals.mail.domain#} ou
    {#globals.mail.domain2#}, ou lorsque tu utilises notre
    <a href="Xorg/SMTPS%E9curis%E9">service d'envoi de courrier SMTP sécurisé</a>.
  </p>

  {javascript name=ajax}
  <script type="text/javascript">//<![CDATA[
    {literal}
    function redirectUpdate()
    {
        showTempMessage('redirect-msg', "Tes redirections ont été mise à jour.", true);
    }
    {/literal}
  //]]></script>
  {javascript name="jquery"}
  <div id="redirect-msg" style="position:absolute;"></div><br />
  <div class="center">
    <table class="bicol" summary="Adresses de redirection">
      <tr>
        <th>Email</th>
        <th>Actif</th>
        <th>Réécriture</th>
        <th>&nbsp;</th>
      </tr>
      {foreach from=$emails item=e name=redirect}
      <tr class="{cycle values="pair,impair"}" id="line_{$e->email|replace:'@':'_at_'}">
        <td>
          <strong>
            {if $e->broken}<span class="erreur">{assign var="erreur" value="1"}{/if}
            {if $e->panne neq '0000-00-00'}{assign var="panne" value="1"}{icon name=error title="En panne"}{/if}
            {$e->email}
            {if $e->broken}</span>{/if}
          </strong>
        </td>
        <td>
          <input type="checkbox" value="{$e->email}"
                 {if $e->active}checked="checked"{/if}
                 {if $smarty.foreach.redirect.total eq 1}disabled="disabled"{/if}
                 onchange="Ajax.update_html(null,'{$globals->baseurl}/emails/redirect/'+(this.checked?'':'in')+'active/{$e->email}', redirectUpdate)" /></td>
        <td>
          <select onchange="Ajax.update_html(null,'{$globals->baseurl}/emails/redirect/rewrite/{$e->email}/'+this.value, redirectUpdate)">
            <option value=''>--- aucune ---</option>
            {foreach from=$alias item=a}
            <option {if $e->rewrite eq "`$a.alias`@polytechnique.org"}selected='selected'{/if}
              value='{$a.alias}@polytechnique.org'>{$a.alias}@polytechnique.org</option>
            <option {if $e->rewrite eq "`$a.alias`@m4x.org"}selected='selected'{/if}
              value='{$a.alias}@m4x.org'>{$a.alias}@m4x.org</option>
            {/foreach}
          </select>
        </td>
        <td>
		  <a href="emails/redirect/remove/{$e->email}" onclick="if (confirm('Supprimer l\'adresse {$e->email} ?')) $.get(this.href,{literal}{}{/literal},function() {literal}{{/literal} $('tr[@id=line_{$e->email|replace:'@':'_at_'}]').remove();{literal}}{/literal}); return false">
		    {icon name=bin_empty title="retirer"}
		  </a>
		</td>
      </tr>
      {/foreach}
      <tr class="{cycle values="pair,impair"}"><td colspan="4">
        <form action="emails/redirect" method="post">
        <div>
    		&nbsp;<br />
    		Ajouter une adresse email :
            <input type="text" size="35" maxlength="60" name="email" value="" />
            &nbsp;&nbsp;<input type="submit" value="ajouter" name="emailop" />
        </div>
        </form>
      </td></tr>
    </table>
  </div>
{if $panne}
<p class="smaller">
  <strong>
    {icon name=error title="En panne"}
    <a href="Xorg/Pannes">Panne&nbsp;:</a>
  </strong>
  Les adresses marquées de cette icône sont des adresses de redirection pour lesquelles une panne
  a été détectée. Si le problème persiste, la redirection vers ces adresses sera désactivée.
</p>
{/if}
{if $erreur}
<p class="smaller">
  <strong>
    {icon name=error title="En panne"}
    <a href="Xorg/Pannes" style="color: #f00">Panne durable&nbsp;:</a>
  </strong>
  Les adresses en rouge sont des adresses qui ont été désactivées en raison d'un grand nombre de pannes. Si tu penses que
  le problème est résolu, tu peux les réactiver, mais l'adresse sera redésactivée si les problèmes persistent.
</p>
{/if}

{if $eleve}
<h1>Pour les Élèves (non encore diplômés)</h1>
<p>
  L'X te fournit aussi une adresse à vie en <strong>«prenom.nom»@polytechnique.edu</strong> qui par défaut est
  une redirection vers «login»@poly.polytechnique.fr. <a href="https://www.mail.polytechnique.edu/">
  Tu peux modifier cette redirection</a> et la faire pointer vers ton adresse
  {$smarty.session.forlife}@{#globals.mail.domain#} (attention, cela demande de la concentration).
</p>
<p>
  Si tu utilises le service POP de poly pour récupérer tes mails dans ton logiciel de courrier,
  l'équipe de Polytechnique.org te conseille de rediriger :
</p>
<ul>
  <li>«prenom.nom»@polytechnique.edu vers {$smarty.session.forlife}@{#globals.mail.domain#}</li>
  <li>{$smarty.session.forlife}@{#globals.mail.domain#} vers «login»@poly.polytechnique.fr</li>
</ul>
<p>
  Attention à ne pas faire une boucle quand tu manipules tes redirections ! Tes emails seraient
  alors perdus, jusqu'à ce que tu règles le problème.
</p>
{/if}

{* vim:set et sw=2 sts=2 sws=2 enc=utf-8: *}
