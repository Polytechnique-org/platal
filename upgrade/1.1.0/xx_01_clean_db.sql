-- Deletes empty mentor expertise.
DELETE FROM profile_mentor WHERE expertise IS NULL OR expertise = '';
-- Deletes empty jobs.
DELETE FROM profile_job WHERE (jobid IS NULL OR jobid = '') AND description = '' AND url = '' AND email = '';
-- Deletes empty educations.
DELETE FROM profile_education WHERE degreeid IS NULL AND eduid IS NULL AND program = '';
-- Deletes empty addresses.
DELETE FROM profile_addresses WHERE text IS NULL OR text = '';

-- vim:set syntax=mysql:
