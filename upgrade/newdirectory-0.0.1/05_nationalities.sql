ALTER TABLE auth_user_md5 ADD COLUMN nationalite2 CHAR(2) DEFAULT NULL,
                          ADD COLUMN nationalite3 CHAR(2) DEFAULT NULL,
                          ADD KEY nationalite2 (nationalite2),
                          ADD KEY nationalite3 (nationalite3),
                          MODIFY nationalite CHAR(2) DEFAULT NULL;

UPDATE auth_user_md5 SET nationalite=NULL WHERE nationalite="00" OR nationalite='';

DELETE FROM geoloc_pays WHERE a2="00";

ALTER TABLE watch_profile MODIFY field enum('nom', 'freetext', 'mobile', 'nationalite', 'nationalite2', 'nationalite3',
                                            'nick', 'web', 'networking', 'appli1', 'appli2', 'addresses',
                                            'section', 'binets', 'medals', 'cv', 'jobs', 'photo');

# vim:set syntax=mysql:
