{dynamic}
{if $error}
  <div class="rubrique">
    Dernière étape
  </div>
  <p>
    Tu as maintenant accès au site en utilisant les paramètres reçus par mail.
    Les adresses électroniques <strong>{$username}@polytechnique.org</strong>
    et <strong>{$username}@m4x.org</strong> sont déjà ouvertes, essaie-les !
  </p>
  <p>
    Remarque: m4x.org est un domaine "discret" qui veut dire "mail for X" et
    qui comporte exactement les mêmes adresses que le domaine polytechnique.org.
  </p>
  <p>
    <strong><a href="{if $dev eq 0}https://www.polytechnique.org/{/if}motdepassemd5.php">Clique ici pour changer ton mot de passe.</a></strong>
  </p>
  <p>
    N'oublie pas : si tu perds ton mot de passe, nous n'avons aucun engagement, en
    particulier en termes de rapidité, mais pas seulement, à te redonner accès au
    site. Cela peut prendre plusieurs semaines, les pertes de mot de passe sont
    traitées avec la priorité minimale.
  </p>
{elseif $error eq $smarty.const.ERROR_DB}
  {$error_db}

  <p>
    Une erreur s'est produite lors de la mise en place définitive de ton inscription,
    essaie à nouveau, si cela ne fonctionne toujours pas, envoie un mail à
    <a href="mailto:webmestre@polytechnique.org">webmaster@polytechnique.org</a>
  </p>
{elseif $error eq $smarty.const.ERROR_ALREADY_SUBSCRIBED}
  <p>
    Tu es déjà inscrit à polytechnique.org. Tu as sûrement cliqué deux fois sur le même lien de
    référence ou effectué un double clic. Consultes tes mails pour obtenir ton identifiant et ton
    mot de passe.
  </p>
{elseif $error eq $smarty.const.ERROR_REF}
  <div class="rubrique">
    OOOooups !
  </div>
  <p>
    Cette adresse n'existe pas, ou plus, sur le serveur.
  </p>
  <p>
    Causes probables :
  </p>
  <ol>
    <li>
      Vérifie que tu visites l'adresse du dernier e-mail reçu s'il y en a eu plusieurs.
    </li>
    <li>
      Tu as peut-être mal copié l'adresse reçue par mail, vérifie-la à la main.
    </li>
    <li>
      Tu as peut-être attendu trop longtemps pour confirmer. Les
      pré-inscriptions sont annulées tous les 30 jours.
    </li>
  </ol>
{/if}
{/dynamic}
