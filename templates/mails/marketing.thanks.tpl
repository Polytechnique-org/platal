{config_load file="mails.conf" section="marketing_thanks"}
{from full=#from#}
{to addr="$to"}
{subject text="$prenom $nom s'est inscrit à Polytechnique.org !"}
Bonjour,

Nous t'écrivons juste pour t'informer que {$prenom} {$nom} (X{$promo}), que tu avais incité à s'inscrire à Polytechnique.org, vient à l'instant de terminer son inscription !! :o)

Merci de ta participation active à la reconnaissance de ce site !!!

Bien cordialement,
L'équipe Polytechnique.org
"Le portail des élèves & anciens élèves de l'Ecole polytechnique"
