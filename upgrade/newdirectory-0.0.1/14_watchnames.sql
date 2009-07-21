ALTER TABLE watch_profile MODIFY field enum('search_names', 'nom', 'freetext', 'mobile', 'nationalite',
                                            'nationalite2', 'nationalite3', 'nick', 'web', 'networking',
                                            'edus', 'addresses', 'section', 'binets', 'medals', 'cv',
                                            'jobs', 'photo', 'corps');

UPDATE watch_profile SET field = 'search_names' WHERE field = 'nom' OR field = 'nick';

ALTER TABLE watch_profile MODIFY field enum('search_names', 'freetext', 'mobile', 'nationalite',
                                            'nationalite2', 'nationalite3', 'web', 'networking','edus',
                                            'addresses', 'section', 'binets', 'medals', 'cv', 'jobs',
                                            'photo', 'corps');

# vim:set syntax=mysql:
