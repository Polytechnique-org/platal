{* $Id: common.footer.tpl,v 1.6 2004-02-11 21:02:42 x2000habouzit Exp $ *}

<div>
  Copyright © 1999-2003 Association <a href="http://x-org.polytechnique.org/">Polytechnique.org</a>
  &nbsp;-&nbsp;
  <a href="docs/apropos.php">A propos de ce site</a>
<br />
  <a href="{"docs/secu.php"|url}">Sécurité et confidentialité</a>
  | <a href="{"docs/ethique.php"|url}">Services et Ethique</a>
  | <a href="{"docs/charte.php"|url}">Charte</a>
{min_auth level=cookie}
  | <a href="{"stats/coupure.php"|url}">Disponibilité</a>
  | <a href="{"stats/"|url}">Statistiques</a>
{/min_auth}
</div>

{* vim:set et sw=2 sts=2 sws=2: *}
