{* $Id: logger-view.tpl,v 1.2 2004-02-20 03:01:10 x2000habouzit Exp $ *}

<div class="rubrique">
  Parcours des logs du site
</div>

{dynamic}

{if $smarty.request.logsess}

<table class="bicol">
<tr>
  <th colspan="2">Propri&eacute;t&eacute;s de la session</th>
</tr>
<tr class="impair">
  <td><strong>user</strong></td>
  <td>{$session.username} {if $session.suer}(suid by {$session.suer}){/if} [<a href="?logauth={$session.auth}&amp;loguser={$session.username}">user's log</a>]</td>
</tr>
<tr class="pair">
  <td><strong>hote</strong></td>
  <td><tt>IP: {$session.ip}</tt><br /><em>{$session.host}</em></td>
</tr>
<tr class="impair">
  <td><strong>browser</strong></td>
  <td>{$session.browser}</td>
</tr>
</table>

<br />

<table class="bicol">
<tr>
  <th>stamp</th>
  <th>action</th>
  <th>data</th>
</tr>
{foreach from=$events item=myevent}
<tr class="{cycle values="impair,pair"}">
  <td style="font-size:90%;">{$myevent.stamp|date_format:"%Y-%m-%d %H:%M:%S"}</td>
  <td><strong>{$myevent.text}</strong></td>
  <td>{$myevent.data|escape}</td>
</tr>
{/foreach}
</table>


{else}

<form method="post" action="{$smarty.server.PHP_SELF}">
<table class="bicol">
<tr>
  <th colspan="2">filter by..</th>
</tr>
<tr>
  <td>..<strong>date</strong></td>
  <td>
    year
    <select name="year" onchange="this.form.submit()">
      {html_options options=$years selected=$year}
    </select>
    &nbsp;month
    <select name="month" onchange="this.form.submit()">
      {html_options options=$months selected=$month}
    </select>
    &nbsp;day
    <select name="day" onchange="this.form.submit()">
      {html_options options=$days selected=$day}
    </select>
  </td>
</tr>
<tr>
  <td>..<strong>user</strong></td>
  <td>
    <input type="text" name="loguser" value="{$loguser}" />
    {html_options name="logauth" options=$auths selected=$logauth}
    <input type="submit" />
  </td>
</tr>
</table>

</form>

<br />

<table class="bicol" style="font-size: 90%">
  <tr>
    <th>start</th>
    <th>user</th>
    <th>summary</th>
    <th>actions</th>
  </tr>
{foreach from=$sessions item=mysess}
  <tr class="{cycle values="impair,pair"}">
    <td>{$mysess.start|date_format:"%Y-%m-%d %H:%M:%S"}</td>
    <td><strong>{$mysess.username}</strong> <span class="smaller">({$mysess.lauth})</span></td>
    <td>
      {foreach from=$mysess.events item=myevent}{$myevent}<br />{/foreach}
    </td>
    <td class="action">
{foreach from=$mysess.actions item=myaction}
      {a lnk=$myaction}
{/foreach}
    </td>
  </tr>
{/foreach}
</table>
{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
