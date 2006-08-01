alter table emails add column panne_level tinyint(1) not null default 0 after panne;
alter table emails change flags flags enum('active', 'filter', 'panne') not null default 'active';
UPDATE emails SET last = panne WHERE last = 0 AND panne != 0;
UPDATE emails
   SET panne_level = 1 
 WHERE flags = 'active' 
       AND DATE_ADD(panne, INTERVAL 2 MONTH) > CURDATE();

