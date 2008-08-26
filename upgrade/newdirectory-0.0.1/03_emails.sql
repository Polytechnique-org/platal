CREATE TABLE IF NOT EXISTS profile_directory (
    uid INT NOT NULL,
    email_directory VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (uid)
) CHARSET=utf8;

ALTER TABLE register_marketing MODIFY COLUMN type ENUM('user', 'staff', 'ax');
