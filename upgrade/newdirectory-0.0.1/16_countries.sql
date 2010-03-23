ALTER TABLE geoloc_countries MODIFY COLUMN phoneFormat VARCHAR(255) NOT NULL DEFAULT "";

DELETE FROM  geoloc_countries
      WHERE  iso_3166_1_a2 = "TP" OR iso_3166_1_a2 = "YU";

INSERT INTO  geoloc_countries (iso_3166_1_a2, iso_3166_1_a3, iso_3166_1_num, worldRegion,
                               country, countryFR, capital, nationalityFR,
                               licensePlate, belongsTo, phonePrefix)
     VALUES  ("AX", "ALA", 248, "EU", "Åland, Îles", "Åland Islands", "Mariehamn", NULL, "AX", "FI", 358),
             ("GG", "GGY", 831, "EU", "Guernsey", "Guernesey", "St Peter Port", NULL, "GBG", "GB", 44),
             ("GY", "GUY", 328, NULL, "Guyana", "Guyana", "Georgetown", "Guyanien", "GUY", NULL, 592),
             ("HK", "HKG", 344, NULL, "Hong Kong", "Hong-Kong", "Hong-Kong", "Hongkongais", "HK", NULL, 852),
             ("IM", "IMN", 833, NULL, "Isle of Man", "Île de Man", "Douglas", "Mannois", "GBM", NULL, 44),
             ("JE", "JEY", 832, NULL, "Jersey", "Jersey", "Saint-Hélier", "Jersiais", "GBJ", NULL, 44),
             ("MO", "MAC", 446, NULL, "Macao", "Macao", "Macao", "Macanais", "MO", NULL, 853),
             ("ME", "MNE", 499, NULL, "Montenegro", "Monténégro", "Podgorica", "Monténégrin", "MNE",
              NULL, 382),
             ("PS", "PSE", 275, NULL, "Palestinian Territory, Occupied", "Palestiniens occupé, Territoire",
              "Jérusalem", "Palestinien", "PS", NULL, 970),
             ("BL", "BLM", 652, "EU", "Saint Barthélemy", "Saint-Barthélemy", "Gustavia", NULL, "F", "FR", 33),
             ("MF", "MAF", 663, "EU", "Saint Martin", "Saint-Martin", "Marigot", NULL, "F", "FR", 33),
             ("PM", "SPM", 666, "EU", "Saint Pierre and Miquelon", "Saint-Pierre-et-Miquelon", "Saint-Pierre",
              NULL, "F", "FR", 33),
             ("RS", "SRB", 688, NULL, "Serbia", "Serbie", "Belgrade", "Serbe", "SRB", NULL, 381),
             ("TL", "TLS", 626, NULL, "Timor-Leste", "Timor-Leste", "Dili", "Timorais", "TL", NULL, 670);

UPDATE  geoloc_countries
   SET  country = "Croatia"
 WHERE  iso_3166_1_a2 = "HR";

UPDATE  geoloc_countries
   SET  country = "Slovakia"
 WHERE  iso_3166_1_a2 = "SK";

UPDATE  geoloc_countries
   SET  country = "Saint Helena"
 WHERE  iso_3166_1_a2 = "SH";

UPDATE  geoloc_countries
   SET  countryFR = "Antigua-et-Barbuda"
 WHERE  iso_3166_1_a2 = "AG";

UPDATE  geoloc_countries
   SET  countryFR = "Cameroun"
 WHERE  iso_3166_1_a2 = "CM";

UPDATE  geoloc_countries
   SET  countryFR = "Centrafricaine, République"
 WHERE  iso_3166_1_a2 = "CF";

UPDATE  geoloc_countries
   SET  countryFR = "Cocos (Keeling), Îles"
 WHERE  iso_3166_1_a2 = "CC";

UPDATE  geoloc_countries
   SET  countryFR = "Égypte"
 WHERE  iso_3166_1_a2 = "EG";

UPDATE  geoloc_countries
   SET  countryFR = "Émirats arabes unis"
 WHERE  iso_3166_1_a2 = "AE";

UPDATE  geoloc_countries
   SET  countryFR = "Équateur"
 WHERE  iso_3166_1_a2 = "EC";

UPDATE  geoloc_countries
   SET  countryFR = "Érythrée"
 WHERE  iso_3166_1_a2 = "ER";

UPDATE  geoloc_countries
   SET  countryFR = "Èthiopie"
 WHERE  iso_3166_1_a2 = "ET";

UPDATE  geoloc_countries
   SET  countryFR = "États-Unis"
 WHERE  iso_3166_1_a2 = "US";

UPDATE  geoloc_countries
   SET  countryFR = "Géorgie du Sud et les Îles Sandwich du Sud"
 WHERE  iso_3166_1_a2 = "GS";

UPDATE  geoloc_countries
   SET  countryFR = "Guinée-Bissau"
 WHERE  iso_3166_1_a2 = "GW";

UPDATE  geoloc_countries
   SET  countryFR = "Heard, Île et MacDonald"
 WHERE  iso_3166_1_a2 = "HM";

UPDATE  geoloc_countries
   SET  countryFR = "Îles mineures éloignées des États-Unis"
 WHERE  iso_3166_1_a2 = "UM";

UPDATE  geoloc_countries
   SET  countryFR = "Îles vierges britanniques", country = "Virgin Islands, British"
 WHERE  iso_3166_1_a2 = "VG";

UPDATE  geoloc_countries
   SET  countryFR = "Îles vierges des États-Unis", country = "Virgin Islands, US"
 WHERE  iso_3166_1_a2 = "VI";

UPDATE  geoloc_countries
   SET  countryFR = "Iran, République islamique d'"
 WHERE  iso_3166_1_a2 = "IR";

UPDATE  geoloc_countries
   SET  countryFR = "Lao, République démocratique populaire"
 WHERE  iso_3166_1_a2 = "LA";

UPDATE  geoloc_countries
   SET  countryFR = "Macédoine, L'Ex-République Yougoslave"
 WHERE  iso_3166_1_a2 = "MK";

UPDATE  geoloc_countries
   SET  countryFR = "Micronésie, États fédérés de"
 WHERE  iso_3166_1_a2 = "FM";

UPDATE  geoloc_countries
   SET  countryFR = "Océan Indien, Territoire britannique de l'"
 WHERE  iso_3166_1_a2 = "IO";

UPDATE  geoloc_countries
   SET  countryFR = "Russie, Fédération de"
 WHERE  iso_3166_1_a2 = "RU";

UPDATE  geoloc_countries
   SET  countryFR = "Saint-Siège (État de la Cité du Vatican)"
 WHERE  iso_3166_1_a2 = "VA";

UPDATE  geoloc_countries
   SET  countryFR = "Saint-Kitts-et-Nevis"
 WHERE  iso_3166_1_a2 = "KN";

UPDATE  geoloc_countries
   SET  countryFR = "Congo, La République démocratique du"
 WHERE  iso_3166_1_a2 = "CD";

UPDATE  geoloc_countries
   SET  countryFR = "Saint-Vincent-et-les-Grenadines"
 WHERE  iso_3166_1_a2 = "VC";

UPDATE  geoloc_countries
   SET  countryFR = "Samoa"
 WHERE  iso_3166_1_a2 = "WS";

UPDATE  geoloc_countries
   SET  countryFR = "Sao Tomé-et-Principe"
 WHERE  iso_3166_1_a2 = "ST";

UPDATE  geoloc_countries
   SET  countryFR = "Svalbard et Île Jan Mayen"
 WHERE  iso_3166_1_a2 = "SJ";

UPDATE  geoloc_countries
   SET  countryFR = "Trinité-et-Tobago"
 WHERE  iso_3166_1_a2 = "TT";

UPDATE  geoloc_countries
   SET  countryFR = "Venezuela"
 WHERE  iso_3166_1_a2 = "VE";

UPDATE  geoloc_countries
   SET  countryFR = "Viet Nam"
 WHERE  iso_3166_1_a2 = "VN";

UPDATE  geoloc_countries
   SET  countryFR = "Wallis et Futuna", country = "Wallis and Futuna"
 WHERE  iso_3166_1_a2 = "WF";

UPDATE  geoloc_countries
   SET  countryFR = "Yémen"
 WHERE  iso_3166_1_a2 = "YE";

UPDATE  geoloc_countries
   SET  belongsTo = "FR"
 WHERE  iso_3166_1_a2 = "GF" OR iso_3166_1_a2 = "PF" OR iso_3166_1_a2 = "TF"
        OR iso_3166_1_a2 = "GP" OR iso_3166_1_a2 = "MQ" OR iso_3166_1_a2 = "YT"
        OR iso_3166_1_a2 = "NC" OR iso_3166_1_a2 = "RE";

UPDATE  geoloc_countries
   SET  belongsTo = "US", nationalityFR = NULL
 WHERE  iso_3166_1_a2 = "UM";

UPDATE  geoloc_countries
   SET  nationalityFR = "Afghan"
 WHERE  iso_3166_1_a2 = "AF";

UPDATE  geoloc_countries
   SET  nationalityFR = "Andorran"
 WHERE  iso_3166_1_a2 = "AD";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "GB", capital = "The Valley"
 WHERE  iso_3166_1_a2 = "AI";

UPDATE  geoloc_countries
   SET  nationalityFR = "Angolais"
 WHERE  iso_3166_1_a2 = "AO";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL
 WHERE  iso_3166_1_a2 = "AQ";

UPDATE  geoloc_countries
   SET  nationalityFR = "Antiguais"
 WHERE  iso_3166_1_a2 = "AG";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "NL", capital = "Willemstad"
 WHERE  iso_3166_1_a2 = "AN";

UPDATE  geoloc_countries
   SET  nationalityFR = "Saoudien", capital = "Riyad"
 WHERE  iso_3166_1_a2 = "SA";

UPDATE  geoloc_countries
   SET  nationalityFR = "Arménien"
 WHERE  iso_3166_1_a2 = "AM";

UPDATE  geoloc_countries
   SET  nationalityFR = "Arubais", capital = "Oranjestad"
 WHERE  iso_3166_1_a2 = "AW";

UPDATE  geoloc_countries
   SET  nationalityFR = "Azerbaïdjanais"
 WHERE  iso_3166_1_a2 = "AZ";

UPDATE  geoloc_countries
   SET  nationalityFR = "Bahaméen"
 WHERE  iso_3166_1_a2 = "BS";

UPDATE  geoloc_countries
   SET  nationalityFR = "Bahreïnien"
 WHERE  iso_3166_1_a2 = "BH";

UPDATE  geoloc_countries
   SET  nationalityFR = "Bangladais"
 WHERE  iso_3166_1_a2 = "BD";

UPDATE  geoloc_countries
   SET  nationalityFR = "Barbadien"
 WHERE  iso_3166_1_a2 = "BB";

UPDATE  geoloc_countries
   SET  nationalityFR = "Biélorusse", capital = "Minsk"
 WHERE  iso_3166_1_a2 = "BY";

UPDATE  geoloc_countries
   SET  nationalityFR = "Béliziens"
 WHERE  iso_3166_1_a2 = "BZ";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "GB"
 WHERE  iso_3166_1_a2 = "BM";

UPDATE  geoloc_countries
   SET  nationalityFR = "Bolivien"
 WHERE  iso_3166_1_a2 = "BO";

UPDATE  geoloc_countries
   SET  nationalityFR = "Bosnien"
 WHERE  iso_3166_1_a2 = "BA";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "NO"
 WHERE  iso_3166_1_a2 = "BV";

UPDATE  geoloc_countries
   SET  nationalityFR = "Brunéiens"
 WHERE  iso_3166_1_a2 = "BN";

UPDATE  geoloc_countries
   SET  nationalityFR = "Burundais"
 WHERE  iso_3166_1_a2 = "BI";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "GB", capital = "George Town"
 WHERE  iso_3166_1_a2 = "KY";

UPDATE  geoloc_countries
   SET  nationalityFR = "Cap-verdiens"
 WHERE  iso_3166_1_a2 = "CV";

UPDATE  geoloc_countries
   SET  nationalityFR = "Centrafricain"
 WHERE  iso_3166_1_a2 = "CF";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "AU", capital = "Flying Fish Cove"
 WHERE  iso_3166_1_a2 = "CX";

UPDATE  geoloc_countries
   SET  nationalityFR = "Chypriotes"
 WHERE  iso_3166_1_a2 = "CY";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "AU", capital = "West Island"
 WHERE  iso_3166_1_a2 = "CC";

UPDATE  geoloc_countries
   SET  nationalityFR = "Comorien"
 WHERE  iso_3166_1_a2 = "KM";

UPDATE  geoloc_countries
   SET  nationalityFR = "Congolais"
 WHERE  iso_3166_1_a2 = "CG";

UPDATE  geoloc_countries
   SET  nationalityFR = "Nord-Coréens"
 WHERE  iso_3166_1_a2 = "CK";

UPDATE  geoloc_countries
   SET  nationalityFR = "Maori des îles Cook"
 WHERE  iso_3166_1_a2 = "KP";

UPDATE  geoloc_countries
   SET  nationalityFR = "Djibouti"
 WHERE  iso_3166_1_a2 = "DJ";

UPDATE  geoloc_countries
   SET  nationalityFR = "Dominicain"
 WHERE  iso_3166_1_a2 = "DO";

UPDATE  geoloc_countries
   SET  nationalityFR = "Dominiquais"
 WHERE  iso_3166_1_a2 = "DM";

UPDATE  geoloc_countries
   SET  nationalityFR = "Salvadorien"
 WHERE  iso_3166_1_a2 = "SV";

UPDATE  geoloc_countries
   SET  nationalityFR = "Émirien"
 WHERE  iso_3166_1_a2 = "AE";

UPDATE  geoloc_countries
   SET  nationalityFR = "Érythréen"
 WHERE  iso_3166_1_a2 = "ER";

UPDATE  geoloc_countries
   SET  nationalityFR = "Éthiopiens"
 WHERE  iso_3166_1_a2 = "ET";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "GB", capital = "Port Stanley"
 WHERE  iso_3166_1_a2 = "FK";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "DK"
 WHERE  iso_3166_1_a2 = "FO";

UPDATE  geoloc_countries
   SET  nationalityFR = "Fidjiens"
 WHERE  iso_3166_1_a2 = "FJ";

UPDATE  geoloc_countries
   SET  nationalityFR = "Gambiens"
 WHERE  iso_3166_1_a2 = "GM";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "GB", capital = "Grytviken"
 WHERE  iso_3166_1_a2 = "GS";

UPDATE  geoloc_countries
   SET  nationalityFR = "Géorgien", capital = "Tbilissi"
 WHERE  iso_3166_1_a2 = "GE";

UPDATE  geoloc_countries
   SET  nationalityFR = "Ghanéen"
 WHERE  iso_3166_1_a2 = "GH";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "GB"
 WHERE  iso_3166_1_a2 = "GI";

UPDATE  geoloc_countries
   SET  nationalityFR = "Grenadien"
 WHERE  iso_3166_1_a2 = "GD";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "DK", capital = "Nuuk"
 WHERE  iso_3166_1_a2 = "GL";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "US"
 WHERE  iso_3166_1_a2 = "GU";

UPDATE  geoloc_countries
   SET  nationalityFR = "Guatémaltèque"
 WHERE  iso_3166_1_a2 = "GT";

UPDATE  geoloc_countries
   SET  nationalityFR = "Guinéens"
 WHERE  iso_3166_1_a2 = "GN";

UPDATE  geoloc_countries
   SET  nationalityFR = "Bissau-Guinéens"
 WHERE  iso_3166_1_a2 = "GW";

UPDATE  geoloc_countries
   SET  nationalityFR = "Équato-Guinéens"
 WHERE  iso_3166_1_a2 = "GQ";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "FR", capital = "Cayenne"
 WHERE  iso_3166_1_a2 = "GF";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "AU"
 WHERE  iso_3166_1_a2 = "HM";

UPDATE  geoloc_countries
   SET  nationalityFR = "Hondurien"
 WHERE  iso_3166_1_a2 = "HN";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "GB", capital = "Road Town"
 WHERE  iso_3166_1_a2 = "VG";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "US", capital = "Charlotte Amalie"
 WHERE  iso_3166_1_a2 = "VI";

UPDATE  geoloc_countries
   SET  nationalityFR = "Indonésien", capital = "Jakarta"
 WHERE  iso_3166_1_a2 = "ID";

UPDATE  geoloc_countries
   SET  nationalityFR = "Irakien", capital = "Bagdad"
 WHERE  iso_3166_1_a2 = "IQ";

UPDATE  geoloc_countries
   SET  nationalityFR = "Jordanien", capital = "Amman"
 WHERE  iso_3166_1_a2 = "JO";

UPDATE  geoloc_countries
   SET  capital = "New Delhi"
 WHERE  iso_3166_1_a2 = "IN";

UPDATE  geoloc_countries
   SET  capital = "Téhéran"
 WHERE  iso_3166_1_a2 = "IR";

UPDATE  geoloc_countries
   SET  capital = "Jérusalem"
 WHERE  iso_3166_1_a2 = "IL";

UPDATE  geoloc_countries
   SET  capital = "Tōkyō"
 WHERE  iso_3166_1_a2 = "JP";

UPDATE  geoloc_countries
   SET  capital = "Astana"
 WHERE  iso_3166_1_a2 = "KZ";

UPDATE  geoloc_countries
   SET  nationalityFR = "Kirghiz", capital = "Bichkek"
 WHERE  iso_3166_1_a2 = "KG";

UPDATE  geoloc_countries
   SET  nationalityFR = "Koweïtien", capital = "Koweït"
 WHERE  iso_3166_1_a2 = "KW";

UPDATE  geoloc_countries
   SET  nationalityFR = "Kényan"
 WHERE  iso_3166_1_a2 = "KE";

UPDATE  geoloc_countries
   SET  nationalityFR = "Gilbertins"
 WHERE  iso_3166_1_a2 = "KI";

UPDATE  geoloc_countries
   SET  nationalityFR = "Laotien", capital = "Vientiane"
 WHERE  iso_3166_1_a2 = "LA";

UPDATE  geoloc_countries
   SET  nationalityFR = "Lesothans"
 WHERE  iso_3166_1_a2 = "LS";

UPDATE  geoloc_countries
   SET  nationalityFR = "Libérien"
 WHERE  iso_3166_1_a2 = "LR";

UPDATE  geoloc_countries
   SET  nationalityFR = "Libyen"
 WHERE  iso_3166_1_a2 = "LY";

UPDATE  geoloc_countries
   SET  nationalityFR = "Liechtensteinois"
 WHERE  iso_3166_1_a2 = "LI";

UPDATE  geoloc_countries
   SET  capital = "Beyrouth"
 WHERE  iso_3166_1_a2 = "LB";

UPDATE  geoloc_countries
   SET  nationalityFR = "Malaisien", capital = "Putrajaya"
 WHERE  iso_3166_1_a2 = "MY";

UPDATE  geoloc_countries
   SET  nationalityFR = "Malawites"
 WHERE  iso_3166_1_a2 = "MW";

UPDATE  geoloc_countries
   SET  nationalityFR = "Maldivien", capital = "Malé"
 WHERE  iso_3166_1_a2 = "MV";

UPDATE  geoloc_countries
   SET  nationalityFR = "Maltais"
 WHERE  iso_3166_1_a2 = "MT";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "US", capital = "Saipan"
 WHERE  iso_3166_1_a2 = "MP";

UPDATE  geoloc_countries
   SET  nationalityFR = "Marshallais"
 WHERE  iso_3166_1_a2 = "MH";

UPDATE  geoloc_countries
   SET  nationalityFR = "Mauricien"
 WHERE  iso_3166_1_a2 = "MU";

UPDATE  geoloc_countries
   SET  nationalityFR = "Mauritanien"
 WHERE  iso_3166_1_a2 = "MR";

UPDATE  geoloc_countries
   SET  nationalityFR = "Micronésien"
 WHERE  iso_3166_1_a2 = "FM";

UPDATE  geoloc_countries
   SET  nationalityFR = "Moldave"
 WHERE  iso_3166_1_a2 = "MD";

UPDATE  geoloc_countries
   SET  nationalityFR = "Monégasque"
 WHERE  iso_3166_1_a2 = "MC";

UPDATE  geoloc_countries
   SET  nationalityFR = "Mongol", capital = "Oulan-Bator"
 WHERE  iso_3166_1_a2 = "MN";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "GB", capital = "Plymouth["
 WHERE  iso_3166_1_a2 = "MS";

UPDATE  geoloc_countries
   SET  nationalityFR = "Mozambicain"
 WHERE  iso_3166_1_a2 = "MZ";

UPDATE  geoloc_countries
   SET  nationalityFR = "Birman", capital = "Naypyidaw"
 WHERE  iso_3166_1_a2 = "MM";

UPDATE  geoloc_countries
   SET  capital = "Mamoudzou"
 WHERE  iso_3166_1_a2 = "YT";

UPDATE  geoloc_countries
   SET  nationalityFR = "Namibien"
 WHERE  iso_3166_1_a2 = "NA";

UPDATE  geoloc_countries
   SET  nationalityFR = "Nauruan"
 WHERE  iso_3166_1_a2 = "NR";

UPDATE  geoloc_countries
   SET  nationalityFR = "Népalais", capital = "Katmandou"
 WHERE  iso_3166_1_a2 = "NP";

UPDATE  geoloc_countries
   SET  nationalityFR = "Nicaraguayen"
 WHERE  iso_3166_1_a2 = "NI";

UPDATE  geoloc_countries
   SET  nationalityFR = "Nigérien"
 WHERE  iso_3166_1_a2 = "NE";

UPDATE  geoloc_countries
   SET  nationalityFR = "Nigérian"
 WHERE  iso_3166_1_a2 = "NG";

UPDATE  geoloc_countries
   SET  nationalityFR = "Niuéen"
 WHERE  iso_3166_1_a2 = "NU";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "AU", capital = "Kingston"
 WHERE  iso_3166_1_a2 = "NF";

UPDATE  geoloc_countries
   SET  capital = "Nouméa"
 WHERE  iso_3166_1_a2 = "NC";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "GB", capital = "Diego Garcia"
 WHERE  iso_3166_1_a2 = "IO";

UPDATE  geoloc_countries
   SET  nationalityFR = "Omani", capital = "Mascate"
 WHERE  iso_3166_1_a2 = "OM";

UPDATE  geoloc_countries
   SET  nationalityFR = "Ougandais"
 WHERE  iso_3166_1_a2 = "UG";

UPDATE  geoloc_countries
   SET  nationalityFR = "Ouzbek", capital = "Tachkent"
 WHERE  iso_3166_1_a2 = "UZ";

UPDATE  geoloc_countries
   SET  nationalityFR = "Qatari", capital = "Doha"
 WHERE  iso_3166_1_a2 = "QA";

UPDATE  geoloc_countries
   SET  nationalityFR = "Pakistanais", capital = "Islamabad"
 WHERE  iso_3166_1_a2 = "PK";

UPDATE  geoloc_countries
   SET  nationalityFR = "Philippins", capital = "Manille"
 WHERE  iso_3166_1_a2 = "PH";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "GB", capital = "Adamstown"
 WHERE  iso_3166_1_a2 = "PN";

UPDATE  geoloc_countries
   SET  capital = "Papeete"
 WHERE  iso_3166_1_a2 = "PF";

UPDATE  geoloc_countries
   SET  nationalityFR = "Palaien"
 WHERE  iso_3166_1_a2 = "PW";

UPDATE  geoloc_countries
   SET  nationalityFR = "Panaméen"
 WHERE  iso_3166_1_a2 = "PA";

UPDATE  geoloc_countries
   SET  nationalityFR = "Papouasien"
 WHERE  iso_3166_1_a2 = "PG";

UPDATE  geoloc_countries
   SET  nationalityFR = "Paraguayen"
 WHERE  iso_3166_1_a2 = "PY";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "US"
 WHERE  iso_3166_1_a2 = "PR";

UPDATE  geoloc_countries
   SET  nationalityFR = "Congolais"
 WHERE  iso_3166_1_a2 = "CD";

UPDATE  geoloc_countries
   SET  nationalityFR = "Rwandais"
 WHERE  iso_3166_1_a2 = "RW";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, capital = ""
 WHERE  iso_3166_1_a2 = "EH";

UPDATE  geoloc_countries
   SET  nationalityFR = "Vatican"
 WHERE  iso_3166_1_a2 = "VA";

UPDATE  geoloc_countries
   SET  nationalityFR = "Kititien"
 WHERE  iso_3166_1_a2 = "KN";

UPDATE  geoloc_countries
   SET  nationalityFR = "Saint-marinois"
 WHERE  iso_3166_1_a2 = "SM";

UPDATE  geoloc_countries
   SET  nationalityFR = "Saint-Vincentais, Grenadin"
 WHERE  iso_3166_1_a2 = "VC";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "GB", capital = "Jamestown"
 WHERE  iso_3166_1_a2 = "SH";

UPDATE  geoloc_countries
   SET  nationalityFR = "Saint-Lucien"
 WHERE  iso_3166_1_a2 = "LC";

UPDATE  geoloc_countries
   SET  nationalityFR = "Salomonien"
 WHERE  iso_3166_1_a2 = "SB";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "US", capital = "Fagatogo"
 WHERE  iso_3166_1_a2 = "AS";

UPDATE  geoloc_countries
   SET  nationalityFR = "Samoan"
 WHERE  iso_3166_1_a2 = "WS";

UPDATE  geoloc_countries
   SET  nationalityFR = "Santoméen"
 WHERE  iso_3166_1_a2 = "ST";

UPDATE  geoloc_countries
   SET  nationalityFR = "Seychellois"
 WHERE  iso_3166_1_a2 = "SC";

UPDATE  geoloc_countries
   SET  nationalityFR = "Sierra-Léonais"
 WHERE  iso_3166_1_a2 = "SL";

UPDATE  geoloc_countries
   SET  capital = "Singapour"
 WHERE  iso_3166_1_a2 = "SG";

UPDATE  geoloc_countries
   SET  nationalityFR = "Slovaques"
 WHERE  iso_3166_1_a2 = "SK";

UPDATE  geoloc_countries
   SET  nationalityFR = "Somalien"
 WHERE  iso_3166_1_a2 = "SO";

UPDATE  geoloc_countries
   SET  nationalityFR = "Soudanais"
 WHERE  iso_3166_1_a2 = "SD";

UPDATE  geoloc_countries
   SET  nationalityFR = "Sri Lankais", capital = "Sri Jayawardenapura"
 WHERE  iso_3166_1_a2 = "LK";

UPDATE  geoloc_countries
   SET  nationalityFR = "Surinamien"
 WHERE  iso_3166_1_a2 = "SR";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "NO"
 WHERE  iso_3166_1_a2 = "SJ";

UPDATE  geoloc_countries
   SET  nationalityFR = "Swazi", capital = "Mbabane"
 WHERE  iso_3166_1_a2 = "SZ";

UPDATE  geoloc_countries
   SET  nationalityFR = "Syrien", capital = "Damas"
 WHERE  iso_3166_1_a2 = "SY";

UPDATE  geoloc_countries
   SET  capital = "Douchanbé"
 WHERE  iso_3166_1_a2 = "TJ";

UPDATE  geoloc_countries
   SET  capital = "Taipei"
 WHERE  iso_3166_1_a2 = "TW";

UPDATE  geoloc_countries
   SET  capital = "Saint-Pierre", nationalityFR = NULL, belongsTo = "FR"
 WHERE  iso_3166_1_a2 = "TF";

UPDATE  geoloc_countries
   SET  nationalityFR = "Thaïlandais", capital = "Bangkok"
 WHERE  iso_3166_1_a2 = "TH";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "NZ"
 WHERE  iso_3166_1_a2 = "TK";

UPDATE  geoloc_countries
   SET  nationalityFR = "Turkmène", capital = "Achgabat"
 WHERE  iso_3166_1_a2 = "TM";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "GB", capital = "Cockburn Town"
 WHERE  iso_3166_1_a2 = "TC";

UPDATE  geoloc_countries
   SET  capital = "Ankara"
 WHERE  iso_3166_1_a2 = "TR";

UPDATE  geoloc_countries
   SET  nationalityFR = "Tanzanien"
 WHERE  iso_3166_1_a2 = "TZ";

UPDATE  geoloc_countries
   SET  nationalityFR = "Tchadien"
 WHERE  iso_3166_1_a2 = "TD";

UPDATE  geoloc_countries
   SET  nationalityFR = "Togolais"
 WHERE  iso_3166_1_a2 = "TG";

UPDATE  geoloc_countries
   SET  nationalityFR = "Tongien"
 WHERE  iso_3166_1_a2 = "TO";

UPDATE  geoloc_countries
   SET  nationalityFR = "Trinidadien"
 WHERE  iso_3166_1_a2 = "TT";

UPDATE  geoloc_countries
   SET  nationalityFR = "Tuvaluan"
 WHERE  iso_3166_1_a2 = "TV";

UPDATE  geoloc_countries
   SET  capital = "Hanoi"
 WHERE  iso_3166_1_a2 = "VN";

UPDATE  geoloc_countries
   SET  nationalityFR = "Vanuatais"
 WHERE  iso_3166_1_a2 = "VU";

UPDATE  geoloc_countries
   SET  nationalityFR = NULL, belongsTo = "FR", capital = "Mata-Utu"
 WHERE  iso_3166_1_a2 = "WF";

UPDATE  geoloc_countries
   SET  nationalityFR = "Yéménite", capital = "Sanaa"
 WHERE  iso_3166_1_a2 = "YE";

UPDATE  geoloc_countries
   SET  nationalityFR = "Zambien"
 WHERE  iso_3166_1_a2 = "ZM";

UPDATE  geoloc_countries
   SET  nationalityFR = "Zimbabwéen"
 WHERE  iso_3166_1_a2 = "ZW";

-- vim:set syntax=mysql:
