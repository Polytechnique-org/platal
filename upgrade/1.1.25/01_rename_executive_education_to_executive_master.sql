-- Rename Executive Education to Executive Master
UPDATE `profile_education_degree_enum` SET degree='Executive Master', abbreviation='EM' WHERE degree='Executive Education';
UPDATE `account_types` SET description="Executive Master de l'X" WHERE type='executive';

-- vim:set syntax=mysql:
