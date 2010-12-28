ALTER TABLE geoloc_administrativeareas ADD COLUMN nameLocal VARCHAR(255) DEFAULT NULL;
ALTER TABLE geoloc_subadministrativeareas ADD COLUMN nameLocal VARCHAR(255) DEFAULT NULL;
ALTER TABLE geoloc_localities ADD COLUMN nameLocal VARCHAR(255) DEFAULT NULL;

ALTER TABLE geoloc_countries CHANGE COLUMN country countryEn VARCHAR(255) DEFAULT NULL;
ALTER TABLE geoloc_countries CHANGE COLUMN countryFR country VARCHAR(255) DEFAULT NULL;
ALTER TABLE geoloc_countries CHANGE COLUMN nationality nationalityEn VARCHAR(255) DEFAULT NULL;
ALTER TABLE geoloc_countries CHANGE COLUMN nationalityFR nationality VARCHAR(255) DEFAULT NULL;
ALTER TABLE geoloc_countries ADD COLUMN countryLocal VARCHAR(255) DEFAULT NULL;
ALTER TABLE geoloc_countries ADD COLUMN countryPlain VARCHAR(255) DEFAULT NULL;

INSERT INTO  geoloc_countries (iso_3166_1_a2, iso_3166_1_a3, iso_3166_1_num, worldRegion,
                               country, countryEn, capital, nationality,
                               licensePlate, belongsTo, phonePrefix)
     VALUES  ('AW', 'ABW', 533, 'SA', 'Aruba', 'Aruba', 'Oranjestad', NULL, 'NL', 'NL', 297),
             ('GW', 'GNB', 624, 'AF', 'Guinée-Bissau', 'Republic of Guinea-Bissau', 'Bissau', 'Bissau-Guinéen', 'GW', NULL, 245);

-- List of supported languages:
  -- ar  ARABIC -- bg  BULGARIAN -- bn  BENGALI -- ca  CATALAN -- cs  CZECH -- da  DANISH -- de  GERMAN -- el  GREEK -- en  ENGLISH -- es  SPANISH -- eu  BASQUE -- fi  FINNISH -- fil FILIPINO -- fr  FRENCH -- gl  GALICIAN -- gu  GUJARATI -- hi  HINDI -- hr  CROATIAN -- sr  SERBIAN -- hu  HUNGARIAN -- id  INDONESIAN -- it  ITALIAN -- iw  HEBREW -- ja  JAPANESE -- kn  KANNADA -- ko  KOREAN -- lt  LITHUANIAN -- lv  LATVIAN -- ml  MALAYALAM -- mr  MARATHI -- nl  DUTCH -- nn  NORWEGIAN NYNORSK -- no  NORWEGIAN -- or  ORIYA -- pl  POLISH -- pt  PORTUGUESE -- pt-BR PORTUGUESE (BRAZIL) -- pt-PT PORTUGUESE (PORTUGAL) -- ro  ROMANIAN -- ru  RUSSIAN -- sk  SLOVAK -- sl  SLOVENIAN -- sv  SWEDISH -- ta  TAMIL -- te  TELUGU -- th  THAI -- tr  TURKISH -- uk  UKRAINIAN -- vi  VIETNAMESE -- zh-CN CHINESE (SIMPLIFIED) -- zh-TW CHINESE (TRADITIONAL)

DROP TABLE IF EXISTS geoloc_languages;
CREATE TABLE geoloc_languages (
  iso_3166_1_a2 CHAR(2) NOT NULL,
  language CHAR(5) NOT NULL,
  country VARCHAR(255) DEFAULT NULL,
  countryPlain VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (iso_3166_1_a2, language),
  FOREIGN KEY (iso_3166_1_a2) REFERENCES geoloc_countries (iso_3166_1_a2) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO  geoloc_languages (language, iso_3166_1_a2)
     VALUES  ('ar', 'IL'), ('ar', 'JO'), ('ar', 'SD'), ('ar', 'TD'), ('ar', 'KM'), ('ar', 'DJ'), ('ar', 'LB'), ('ar', 'DZ'), ('ar', 'BH'), ('ar', 'EG'), ('ar', 'ER'), ('ar', 'IQ'), ('ar', 'KW'), ('ar', 'LY'), ('ar', 'MR'), ('ar', 'MA'), ('ar', 'OM'), ('ar', 'PS'), ('ar', 'QA'), ('ar', 'SA'), ('ar', 'SO'), ('ar', 'SY'), ('ar', 'TN'), ('ar', 'AE'),
             ('bg', 'BG'), ('bn', 'BD'), ('bn', 'IN'), ('ca', 'ES'), ('cs', 'CZ'), ('da', 'DK'), ('de', 'AT'), ('de', 'DE'), ('de', 'LI'), ('de', 'BE'), ('de', 'LU'), ('de', 'CH'), ('el', 'GR'), ('el', 'CY'),
             ('en', 'IN'), ('en', 'JO'), ('en', 'SD'), ('en', 'PH'), ('en', 'AG'), ('en', 'BS'), ('en', 'BW'), ('en', 'DM'), ('en', 'FJ'), ('en', 'GM'), ('en', 'GH'), ('en', 'GD'), ('en', 'GY'), ('en', 'IE'), ('en', 'JM'), ('en', 'KE'), ('en', 'KI'), ('en', 'LS'), ('en', 'LR'), ('en', 'MW'), ('en', 'MT'), ('en', 'MH'), ('en', 'MU'), ('en', 'FM'), ('en', 'NA'), ('en', 'NR'), ('en', 'NZ'), ('en', 'NG'), ('en', 'PK'), ('en', 'PG'), ('en', 'KN'), ('en', 'LC'), ('en', 'VC'), ('en', 'WS'), ('en', 'SL'), ('en', 'SB'), ('en', 'SZ'), ('en', 'TZ'), ('en', 'TO'), ('en', 'TT'), ('en', 'TV'), ('en', 'UG'), ('en', 'ZM'), ('en', 'ZW'), ('en', 'AU'), ('en', 'GB'), ('en', 'ZA'), ('en', 'BZ'), ('en', 'US'), ('en', 'HN'), ('en', 'CM'), ('en', 'RW'), ('en', 'SC'), ('en', 'CA'), ('en', 'VU'), ('en', 'PW'), ('en', 'SG'),
             ('en', 'MO'),
             ('es', 'BZ'), ('es', 'US'), ('es', 'MX'), ('es', 'AR'), ('es', 'CL'), ('es', 'DO'), ('es', 'NI'), ('es', 'CO'), ('es', 'PE'), ('es', 'VE'), ('es', 'EC'), ('es', 'GT'), ('es', 'CU'), ('es', 'BO'), ('es', 'SV'), ('es', 'PY'), ('es', 'CR'), ('es', 'PA'), ('es', 'AD'), ('es', 'ES'), ('es', 'UY'), ('es', 'HN'), ('es', 'GQ'),
             ('eu', 'ES'), ('fi', 'FI'), ('fil', 'PH'),
             ('fr', 'CM'), ('fr', 'RW'), ('fr', 'SC'), ('fr', 'CA'), ('fr', 'VU'), ('fr', 'CD'), ('fr', 'FR'), ('fr', 'MG'), ('fr', 'CI'), ('fr', 'BF'), ('fr', 'NE'), ('fr', 'SN'), ('fr', 'ML'), ('fr', 'GN'), ('fr', 'HT'), ('fr', 'BI'), ('fr', 'BJ'), ('fr', 'TG'), ('fr', 'CF'), ('fr', 'CG'), ('fr', 'GA'), ('fr', 'MC'), ('fr', 'TD'), ('fr', 'KM'), ('fr', 'DJ'), ('fr', 'LB'), ('fr', 'BE'), ('fr', 'LU'), ('fr', 'CH'), ('fr', 'GQ'),
             ('gl', 'ES'), ('gu', 'IN'), ('hi', 'IN'), ('hr', 'HR'), ('hr', 'BA'), ('hu', 'HU'), ('id', 'ID'), ('it', 'CH'), ('it', 'IT'), ('it', 'SM'), ('it', 'VA'), ('it', 'MT'), ('iw', 'IL'), ('ja', 'JP'), ('ja', 'PW'), ('kn', 'IN'), ('ko', 'KP'), ('ko', 'KR'), ('lt', 'LT'), ('lv', 'LV'), ('ml', 'IN'), ('mr', 'IN'), ('nl', 'BE'), ('nl', 'AW'), ('nl', 'NL'), ('nl', 'SR'), ('no', 'NO'), ('nn', 'NO'), ('or', 'IN'), ('pl', 'PL'), ('pt', 'BR'), ('pt-BR', 'BR'), ('pt', 'PT'), ('pt-PT', 'PT'), ('pt', 'MZ'), ('pt', 'AO'), ('pt', 'GW'), ('pt', 'TL'), ('pt', 'CV'), ('pt', 'ST'), ('pt', 'UY'), ('pt', 'MO'), ('rm', 'CH'), ('ro', 'MD'), ('ro', 'RO'), ('ru', 'RU'), ('ru', 'BY'), ('ru', 'KZ'), ('ru', 'KG'), ('sk', 'CZ'), ('sk', 'SK'), ('sl', 'SI'), ('sr', 'BA'), ('sr', 'RS'), ('sv', 'FI'), ('sv', 'SE'), ('ta', 'IN'), ('ta', 'LK'), ('ta', 'SG'), ('te', 'IN'), ('th', 'TH'), ('tr', 'CY'), ('tr', 'TR'), ('ua', 'UA'), ('vi', 'VN'),
             ('zh-CN', 'SG'), ('zh-CN', 'CN'), ('zh-CN', 'TW'), ('zh-CN', 'MY'), ('zh-CN', 'HK'), ('zh-CN', 'MO'), ('zh-TW', 'SG'), ('zh-TW', 'CN'), ('zh-TW', 'TW'), ('zh-TW', 'MY'), ('zh-TW', 'HK'), ('zh-TW', 'MO');

-- vim:set syntax=mysql:
