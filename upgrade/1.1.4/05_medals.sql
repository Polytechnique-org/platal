ALTER TABLE profile_medals ADD COLUMN level ENUM('', 'Or', 'Argent', 'Bronze') NOT NULL DEFAULT '';
ALTER TABLE profile_medal_enum MODIFY COLUMN flags SET('validation', 'has_levels') NOT NULL DEFAULT '';
UPDATE profile_medal_enum SET flags = 'has_levels' WHERE id = 20; -- Médaille de la Défense Nationale
UPDATE profile_medal_enum SET flags = 'validation,has_levels' WHERE id IN (61, 62, 63); -- sports
DELETE FROM profile_medal_grade_enum WHERE mid IN (61, 62, 63); -- Not used in prod yet, thus no need to update.

-- vim:set syntax=mysql:
