ALTER TABLE  watch_profile
     MODIFY  field ENUM('search_names', 'nom', 'freetext', 'mobile', 'nationality1', 'nationality2', 'nationality3',
                        'nick', 'networking', 'edus', 'addresses', 'section', 'binets', 'medals',
                        'cv', 'jobs', 'photo', 'corps');

UPDATE watch_profile SET field = 'search_names' WHERE field = 'nom' OR field = 'nick';

ALTER TABLE  watch_profile
     MODIFY  field ENUM('search_names', 'freetext', 'mobile', 'nationality1', 'nationality2', 'nationality3',
                        'nick', 'networking', 'edus', 'addresses', 'section', 'binets', 'medals',
                        'cv', 'jobs', 'photo', 'corps');

# vim:set syntax=mysql:
