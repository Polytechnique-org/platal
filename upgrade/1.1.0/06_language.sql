UPDATE profile_langskill_enum SET id = SUBSTRING(id, 1, 3);
ALTER TABLE profile_langskills DROP FOREIGN KEY profile_langskills_ibfk_2;
ALTER TABLE profile_langskill_enum CHANGE COLUMN id iso_639_2b CHAR(3) NOT NULL DEFAULT '';
ALTER TABLE profile_langskills ADD FOREIGN KEY (lid) REFERENCES profile_langskill_enum (iso_639_2b) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_langskill_enum ADD COLUMN iso_639_2t CHAR(3) NOT NULL DEFAULT '';
ALTER TABLE profile_langskill_enum ADD COLUMN iso_639_1 CHAR(2) DEFAULT NULL;
ALTER TABLE profile_langskill_enum CHANGE COLUMN langue_en language_en VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE profile_langskill_enum CHANGE COLUMN langue_fr language VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE profile_langskill_enum ADD KEY (iso_639_2t);
ALTER TABLE profile_langskill_enum ADD KEY (iso_639_1);

-- vim:set syntax=mysql:
