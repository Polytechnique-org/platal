{* $Id: index.tpl,v 1.3 2004-08-26 14:44:44 x2000habouzit Exp $ *}

<div class="rubrique">Que sont les groupes X ?</div>
<p>
  Les groupes X sont des associations, formées de Polytechniciens, anciens ou élèves. Parfois, ils intègrent aussi
  des personnes originaires d'autres grandes écoles.<br />
  Un site est dédié aux activités associatives. Il contient en particulier une liste de tous les groupes X. Tu le
  trouveras à l'adresse <a href="http://www.polytechnique.net/">http://www.polytechnique.net/</a>.
</p>

{include file='include/liste_domaines.tpl' nb_dom=$nbdom domaines=$domaines}

<div class="rubrique">Services aux Groupes X</div>
<p>
  Polytechnique.org a le plaisir d'offrir plusieurs services intéressants aux groupes X.
</p>
<p>
  D'abord, chaque groupe X peut avoir un nom de domaine sur le modèle nomdugroupe.polytechnique.org.
  Sur ce domaine, nous te donnons la possibilité d'avoir un site web et des emails. Pour obtenir un domaine,
  {mailto address="info@polytechnique.org" text="écris-nous" subject="Domaine de groupeX" encode="javascript"}.
</p>
<p>
  Pour le site web, ce peut être soit une simple redirection vers un site que tu héberges
  toi-même, soit un site complètement hébergé par nos soins. <strong>Dans le premier cas</strong>, il te suffit
  de nous donner  l'adresse web de ton site, http://nomdugroupe.polytechnique.org/ devient alors
  son &eacute;quivalent. Il faut donc avoir un hébergeur de site web comme il en existe de nombreux gratuits
  (comme <a href="http://www.free.fr/">free.fr</a> ou <a href="http://www.freesurf.fr/">freesurf.fr</a>...)
  <strong>Dans le second cas</strong>, l'hébergement se fait grâce à notre logiciel Diogenes. Plusieurs groupes ont déjà
  opté pour cette solution, comme tu pourras le constater à l'adresse suivante :
  <a href="http://diogenes.polytechnique.org/">http://diogenes.polytechnique.org/</a>.
</p>
<p>
  Pour les emails, tu pourras mettre en place tous les adresses souhaitées dans le domaine
  nomdugroupe.polytechnique.org, comme par exemple membres@nomdugroupe.polytechnique.org ou
  bureau@nomdugroupe.polytechnique.org. Ces alias peuvent être redirigés vers une liste de diffusion
  déjà existante sur polytechnique.org, vers des utilisateurs de polytechnique.org, mais aussi
  vers des personnes extérieures (non Polytechniciens par exemple).
</p>
<p>
  Dans tous les cas, pour la mise en place du domaine de ton groupe X ou pour des
  questions sur les services aux groupes X,
  {mailto address="info@polytechnique.org" text="écris-nous" subject="Domaine de groupeX" encode="javascript"}.
</p>

{* vim:set et sw=2 sts=2 sws=2: *}
