{* $Id: valider.tpl,v 1.3 2004-02-07 17:18:15 x2000habouzit Exp $ *}

{dynamic}
{$mail}
{foreach item=valid from=$valids}
<br />
{include file=$valid->formu() valid=$valid}
{/foreach}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
