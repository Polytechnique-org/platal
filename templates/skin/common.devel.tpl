{* $Id: common.devel.tpl,v 1.1 2004-02-20 11:44:07 x2000habouzit Exp $ *}

{if $validate}
  <div id="dev">
    <div class="title">Outils de dev</div>
    <div>
      <a href="http://validator.w3.org/check?uri={$validate}">VALIDER XHTML 1.1</a><br />
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
  {$db_trace}
    </div>
  </div>
{/if}
{/dynamic}

{* vim:set et sw=2 sts=2 sws=2: *}
