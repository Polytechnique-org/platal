INSERT INTO accounts SET hruid = "blah.admin.42", type = "x", is_admin = 1, password = SHA1("Dummy4dminPassw0rd"), state = "active";
SET @UID = LAST_INSERT_ID();

INSERT INTO profiles SET hrpid = "blah.admin.42", birthdate_ref = "1942-12-25";
SET @PID = LAST_INSERT_PID();

INSERT INTO account_profiles SET uid = @UID, pid = @PID, perms = "owner";

INSERT INTO profile_display SET pid = @PID, yourself = "admin", public_name = "Blah Admin", promo = "X1942", sort_name = "ADMIN Blah", directory_name = "ADMIN Blah", short_name = "Admin", private_name = "Blah Admin";

SET @x_eduid = (SELECT id FROM profile_education_enum WHERE abbreviation = 'X');
SET @ing_degreeid = (SELECT id FROM profile_education_degree_enum WHERE abbreviation = 'Ing.');
INSERT INTO profile_education SET pid = @PID, eduid = @x_eduid, degreeid = @ing_degreeid, entry_year = 1942, grad_year = 1945, flags = 'primary';

SET @name_ini_id = (SELECT id FROM profile_name_enum WHERE type = 'name_ini');
SET @firstname_ini_id = (SELECT id FROM profile_name_enum WHERE type = 'name_ini');

INSERT INTO profile_name SET pid = @PID, name = 'Admin', typeid = @name_ini_id;
INSERT INTO profile_name SET pid = @PID, name = 'Blah', typeid = @firstname_ini_id;

