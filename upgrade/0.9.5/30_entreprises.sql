ALTER TABLE entreprises
ADD pub ENUM('private', 'ax', 'public') DEFAULT 'private' NOT NULL,
ADD adr_pub ENUM('private', 'ax', 'public') DEFAULT 'private' NOT NULL,
ADD tel_pub ENUM('private', 'ax', 'public') DEFAULT 'private' NOT NULL;
UPDATE entreprises SET
pub = IF(FIND_IN_SET('entreprise_public', visibilite), 'public', IF(FIND_IN_SET('entreprise_ax', visibilite), 'ax', 'private')), 
adr_pub = IF(FIND_IN_SET('adr_public', visibilite), 'public', IF(FIND_IN_SET('adr_ax', visibilite), 'ax', 'private')), 
tel_pub = IF(FIND_IN_SET('tel_public', visibilite), 'public', IF(FIND_IN_SET('tel_ax', visibilite),'ax', 'private'));
ALTER TABLE entreprises DROP visibilite;
ALTER TABLE entreprises
ADD email VARCHAR(60) NOT NULL AFTER mobile,
ADD web VARCHAR(255) NOT NULL AFTER email,
ADD email_pub ENUM('private', 'ax', 'public') DEFAULT 'private' NOT NULL;
