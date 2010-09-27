ALTER TABLE profile_job DROP COLUMN sectorid;
ALTER TABLE profile_job DROP COLUMN subsectorid;
ALTER TABLE profile_job DROP COLUMN subsubsectorid;
DROP TABLE profile_mentor_sector;
DROP TABLE profile_job_sector_enum;
DROP TABLE profile_job_subsector_enum;
DROP TABLE profile_job_subsubsector_enum;
DROP TABLE profile_job_alternates;

-- vim:set syntax=mysql:
