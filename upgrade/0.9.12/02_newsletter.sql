ALTER TABLE newsletter ADD COLUMN short_name varchar(16) DEFAULT NULL;
ALTER TABLE newsletter ADD COLUMN titre_mail varchar(255) NOT NULL DEFAULT '';
UPDATE newsletter SET titre_mail = IF(locate('§|§',titre), LEFT(titre, LOCATE('§|§', titre) -1),titre);
UPDATE newsletter SET titre = IF(locate('§|§',titre), SUBSTRING(titre, LOCATE('§|§', titre) +3),titre);
