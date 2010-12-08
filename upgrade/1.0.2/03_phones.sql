ALTER TABLE watch_profile MODIFY COLUMN field ENUM('search_names','freetext','mobile','nationality1','nationality2','nationality3','nick','networking','edus','addresses','section','binets','medals','cv','jobs','photo','corps','tels') NOT NULL DEFAULT 'search_names';

UPDATE  watch_profile
   SET  field = 'tels'
 WHERE  field = 'mobile';

ALTER TABLE watch_profile MODIFY COLUMN field ENUM('search_names','freetext','nationality1','nationality2','nationality3','nick','networking','edus','addresses','section','binets','medals','cv','jobs','photo','corps','tels') NOT NULL DEFAULT 'search_names';

-- vim:set syntax=mysql:
