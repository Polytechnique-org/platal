{* $Id: logger-view.tpl,v 1.4 2004-08-26 12:31:15 x2000habouzit Exp $ *}

<div class="rubrique">
  Parcours des logs du site
</div>

{dynamic}

{if $smarty.request.logsess}

<table class="bicol">
<tr>
  <th colspan="2">{$msg_session_properties}</th>
</tr>
<tr class="impair">
  <td class="titre">{$msg_user}</td>
  <td>{$session.username} {if $session.suer}(suid by {$session.suer}){/if} [<a href="?logauth={$session.auth}&amp;loguser={$session.username}">user's log</a>]</td>
</tr>
<tr class="pair">
  <td class="titre">{$msg_host}</td>
  <td><em>{$session.host}</em> <tt>IP: {$session.ip}</tt></td>
</tr>
<tr class="impair">
  <td class="titre">{$msg_browser}</td>
  <td>{$session.browser}</td>
</tr>
</table>

<br />

<table class="bicol">
<tr>
  <th>{$msg_date}</th>
  <th>{$msg_action}</th>
  <th>{$msg_data}</th>
</tr>
{foreach from=$events item=myevent}
<tr class="{cycle values="impair,pair"}">
  <td style="font-size:90%;">{$myevent.stamp|date_format:"%Y-%m-%d %H:%M:%S"}</td>
  <td><strong>{$myevent.text}</strong></td>
  <td>{$myevent.data}</td>
</tr>
{/foreach}
</table>

{else}

<form method="post" action="{$smarty.server.PHP_SELF}">
<table class="bicol">
<tr>
  <th colspan="2">{$msg_filter_by}</th>
</tr>
<tr>
  <td><strong>{$msg_date}</strong></td>
  <td>
    {$msg_year}
    <select name="year" onchange="this.form.submit()">
      {html_options options=$years selected=$year}
    </select>
    &nbsp;{$msg_month}
    <select name="month" onchange="this.form.submit()">
      {html_options options=$months selected=$month}
    </select>
    &nbsp;{$msg_day}
    <select name="day" onchange="this.form.submit()">
      {html_options options=$days selected=$day}
    </select>
  </td>
</tr>
<tr>
  <td><strong>{$msg_user}</strong></td>
  <td>
    <input type="text" name="loguser" value="{$loguser}" />
    {html_options name="logauth" options=$auths selected=$logauth}
    <input type="submit" value="{$msg_submit}" />
  </td>
</tr>
</table>

</form>

<br />

<table class="bicol" style="font-size: 90%">
  <tr>
    <th>{$msg_start}</th>
    <th>{$msg_user}</th>
    <th>{$msg_summary}</th>
    <th>{$msg_actions}</th>
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
{if $msg_nofilters}
<tr>
  <td>{$msg_nofilters}</td>
</tr>
{/if}
</table>
{/if}

{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
