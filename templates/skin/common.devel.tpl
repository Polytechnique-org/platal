{* $Id: common.devel.tpl,v 1.4 2004-08-30 10:00:33 x2000habouzit Exp $ *}

{dynamic}
{if $db_trace neq "\n\n"}
  <div id="db-trace">
    <div class="rubrique">
      Trace de l'exécution de cette page sur mysql (hover me)
    </div>
    <div class="hide">
      {$db_trace|smarty:nodefaults}
    </div>
  </div>
{/if}

{if $validate}
  <div id="dev">
    Validation :
    @NB_ERR@
    <a href="http://jigsaw.w3.org/css-validator/validator?uri={$validate}">CSS</a>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    références :
    <a href="http://www.w3schools.com/xhtml/xhtml_reference.asp">XHTML</a>
    <a href="http://www.w3schools.com/css/css_reference.asp">CSS2</a>
  </div>
{/if}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
