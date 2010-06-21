ALTER TABLE  watch_profile
     MODIFY  field ENUM('nom', 'freetext', 'mobile', 'nationality1', 'nationality2', 'nationality3',
                        'nick', 'web','appli1', 'appli2', 'addresses', 'section',
                        'binets', 'medals', 'cv', 'jobs', 'photo');

# vim:set syntax=mysql:
