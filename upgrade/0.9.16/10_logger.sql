INSERT INTO logger.actions (text, description)
     VALUES ('view_profile', 'Consultation d\'une fiche'),
            ('search', 'Réalisation d\'une recherche');

use logger;
ALTER TABLE events ADD INDEX action (action);

use x4dat;

# vim:set syntax=mysql:
