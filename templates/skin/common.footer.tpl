{* $Id: common.footer.tpl,v 1.5 2004-01-29 14:18:55 x2000habouzit Exp $ *}

<p>
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
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
