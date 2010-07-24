ALTER TABLE profile_medal_enum MODIFY COLUMN type ENUM('ordre','croix','militaire','honneur','resistance','prix','sport') NOT NULL DEFAULT 'ordre';

INSERT INTO  profile_medal_enum (type, text, flags)
     VALUES  ('sport', 'Championnat du monde de vol à voile', 'validation'),
             ('sport', 'Championnat d\'Europe de vol à voile', 'validation'),
             ('sport', 'Championnat de France de vol à voile', 'validation');

INSERT INTO  profile_medal_grade_enum (mid, gid, text, pos)
     SELECT  id, 1, 'Or', 1
       FROM  profile_medal_enum
      WHERE  text = 'Championnat du monde de vol à voile'
             OR text = 'Championnat d\'Europe de vol à voile'
             OR text = 'Championnat de France de vol à voile';

INSERT INTO  profile_medal_grade_enum (mid, gid, text, pos)
     SELECT  id, 2, 'Argent', 2
       FROM  profile_medal_enum
      WHERE  text = 'Championnat du monde de vol à voile'
             OR text = 'Championnat d\'Europe de vol à voile'
             OR text = 'Championnat de France de vol à voile';

INSERT INTO  profile_medal_grade_enum (mid, gid, text, pos)
     SELECT  id, 3, 'Bronze', 3
       FROM  profile_medal_enum
      WHERE  text = 'Championnat du monde de vol à voile'
             OR text = 'Championnat d\'Europe de vol à voile'
             OR text = 'Championnat de France de vol à voile';

-- vim:set syntax=mysql:
