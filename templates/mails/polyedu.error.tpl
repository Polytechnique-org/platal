{config_load file="mails.conf" section="polyedu_error"}
{from full=#from#}
{to addr=#to#}
{subject text="Erreur d'inscription sur polyedu pour le username $username"}

Erreur d'inscription sur polyedu pour le username {$username}.
Pour corriger, un petit tour dans la page d'admin suffit normalement.
Le message d'erreur mysql est (s'il est vide, c'est qu'il n'y a pas d'entrée dans la table X, sûrement un homonyme qui devrait envoyer un mail avec une proposition d'alias) :

{$polyedu_error}
