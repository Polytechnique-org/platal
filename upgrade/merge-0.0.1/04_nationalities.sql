CREATE TEMPORARY TABLE IF NOT EXISTS tmp_update_fusionax_anciens (
  good CHAR(4) DEFAULT NULL,
  bad CHAR(4) DEFAULT NULL,
  PRIMARY KEY(bad),
  UNIQUE KEY(good)
) CHARSET=utf8;

INSERT IGNORE INTO  tmp_update_fusionax_anciens (bad, good)
            VALUES  ('TC', 'CAM'),
                    ('SH', 'CN'),
                    ('R', 'RO'),
                    ('TW', 'RC'),
                    ('TG', 'RT'),
                    ('U', 'ROU'),
                    ('KP', 'ROK'),
                    ('CRO', 'HR'),
                    ('UKR', 'UA'),
                    ('AM', 'ARM'),
                    ('CS', 'CZ'),
                    ('SU', 'RUS'),
                    ('LET', 'LV'),
                    ('MDA', 'MD');

    UPDATE  fusionax_anciens            AS f
INNER JOIN  tmp_update_fusionax_anciens AS t ON (f.Code_nationalite = t.bad)
       SET  f.Code_nationalite = t.good;

-- vim:set syntax=mysql:
