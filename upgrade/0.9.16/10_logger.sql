INSERT INTO logger.actions (text, description)
     VALUES ('view_profile', 'Consultation d\'une fiche'),
            ('search', 'Réalisation d\'une recherche');

user logger;
ALTER TABLE events ADD INDEX action (action);

user x4dat;

# vim:set syntax=mysql:
