{* $Id: parselog.tpl,v 1.2 2004-08-26 14:44:46 x2000habouzit Exp $ *}

<div class="rubrique">
  Logs de polytechnique.org
</div>

<p>
{dynamic}
{fetch file="/home/web/public/lastParselog" assign=bob}
{$bob|replace:" ":"&nbsp;"|nl2br}
{/dynamic}
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
