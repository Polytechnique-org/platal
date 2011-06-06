UPDATE  newsletter_issues
   SET  sufb_json = REPLACE(sufb_json, 'Ing.', 'Ingénieur');
UPDATE  newsletter_issues
   SET  sufb_json = REPLACE(sufb_json, 'M%', 'Master');
UPDATE  newsletter_issues
   SET  sufb_json = REPLACE(sufb_json, 'PhD', 'Doctorat');

-- vim:set syntax=mysql:
