CREATE TABLE IF NOT EXISTS profile_directory (
    uid INT NOT NULL,
    email_directory VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (uid)
) CHARSET=utf8;

INSERT INTO  profile_directory (uid, email_directory)
     SELECT  user_id, Mel_usage
       FROM  fusionax_anciens AS ax
 INNER JOIN  auth_user_md5    AS u ON (ax.id_ancien = CONVERT(u.matricule_ax, BINARY))
      WHERE  Mel_publiable != '0' AND Mel_usage != '';


ALTER TABLE register_marketing MODIFY COLUMN type ENUM('user', 'staff', 'ax');

INSERT IGNORE INTO  register_marketing (uid, email, type)
            SELECT  user_id, Mel_usage, 'ax'
              FROM  fusionax_anciens AS ax
        INNER JOIN  auth_user_md5    AS u ON (ax.id_ancien = CONVERT(u.matricule_ax, BINARY))
         LEFT JOIN  emails           AS e ON (e.uid = u.user_id AND e.flags = 'active')
             WHERE  Mel_usage != '' AND
                    Mel_usage NOT LIKE '%@polytechnique.edu' AND
                    Mel_usage NOT LIKE '%@polytechnique.org' AND
                    Mel_usage NOT LIKE '%@m4x.org' AND
                    Mel_usage NOT LIKE '%@melix.%' AND
                    e.email IS NULL;

