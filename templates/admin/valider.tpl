{* $Id: valider.tpl,v 1.1 2004-01-27 09:08:57 x2000habouzit Exp $ *}

{dynamic}
{$mail}
{foreach item=valid from=$valids}
<br />
{$valid->formu()}
{/foreach}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
