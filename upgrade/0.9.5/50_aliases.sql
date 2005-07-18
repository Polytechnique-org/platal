ALTER TABLE aliases CHANGE flags flags SET('bestalias','usage','epouse') NOT NULL;
UPDATE aliases SET flags = 'usage,epouse' WHERE flags='epouse';
UPDATE aliases SET flags = 'usage,epouse,bestalias' WHERE flags='epouse,bestalias';

