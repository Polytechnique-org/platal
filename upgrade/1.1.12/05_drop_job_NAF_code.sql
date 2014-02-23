UPDATE profile_job_enum SET NAF_code = NULL WHERE NAF_code NOT REGEXP '^[0-9]{4}[a-z]$';
