ALTER TABLE adresses
ADD pub ENUM('private', 'ax', 'public') DEFAULT 'private' NOT NULL,
ADD tel_pub ENUM('private', 'ax', 'public') DEFAULT 'private' NOT NULL;
UPDATE adresses SET pub = IF(FIND_IN_SET('adr_public', visibilite), 'public', IF(FIND_IN_SET('adr_ax', visibilite), 'ax', 'private')), tel_pub = IF(FIND_IN_SET('tel_public', visibilite), 'public', IF(FIND_IN_SET('tel_ax', visibilite),'ax', 'private'));
ALTER TABLE adresses DROP visibilite;
