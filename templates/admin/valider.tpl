{* $Id: valider.tpl,v 1.2 2004-02-07 16:57:58 x2000habouzit Exp $ *}

{dynamic}
{$mail}
{foreach item=valid from=$valids}
<br />
{include file=$valid->tpl_form valid=$valid}
{/foreach}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
