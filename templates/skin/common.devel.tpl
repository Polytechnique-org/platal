{* $Id: common.devel.tpl,v 1.3 2004-08-25 08:59:18 x2000habouzit Exp $ *}

{if $validate}
  <div id="dev">
    <div class="title">Outils de dev</div>
    <div>
      @NB_ERR@
      <a href="http://jigsaw.w3.org/css-validator/validator?uri={$validate}">VALIDER CSS</a>
    </div>
    <div>
      <a href="http://www.w3schools.com/xhtml/xhtml_reference.asp">XHTML ref.</a><br />
      <a href="http://www.w3schools.com/css/css_reference.asp">CSS2 ref.</a>
    </div>
  </div>
{/if}

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
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
