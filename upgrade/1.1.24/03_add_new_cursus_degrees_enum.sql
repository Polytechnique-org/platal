-- bachelor already exists in profile_education_degree_enum table
INSERT INTO `profile_education_degree_enum` SET degree='Executive Education', level = 0;
INSERT INTO `profile_education_degree_enum` SET degree='Graduate Degree', level= 5;
INSERT INTO `profile_education_degree_enum` SET degree='Master spécialisé', level = 5;

-- Add the degrees to Ecole polytechnique
SET @xedu = (SELECT id FROM `profile_education_enum` WHERE abbreviation = 'X');
INSERT INTO `profile_education_degree` SET eduid=@xedu, degreeid=(SELECT id FROM `profile_education_degree_enum` WHERE degree='Bachelor');
INSERT INTO `profile_education_degree` SET eduid=@xedu, degreeid=(SELECT id FROM `profile_education_degree_enum` WHERE degree='Executive Education');
INSERT INTO `profile_education_degree` SET eduid=@xedu, degreeid=(SELECT id FROM `profile_education_degree_enum` WHERE degree='Graduate Degree');
INSERT INTO `profile_education_degree` SET eduid=@xedu, degreeid=(SELECT id FROM `profile_education_degree_enum` WHERE degree='Master spécialisé');

-- vim:set syntax=mysql:
