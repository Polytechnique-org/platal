-- http://www.medailles-decorations.com/
-- http://perso.wanadoo.fr/tnr.g/

drop table if exists profile_medals;
create table profile_medals (
        id      int not null auto_increment,
        type    enum('ordre', 'croix', 'militaire', 'honneur', 'resistance', 'prix') not null,
        text    varchar(255),
        img     varchar(255),
        primary key (id)
);

drop table if exists profile_medals_grades;
create table profile_medals_grades (
        mid     int not null,
        gid     int not null,
        text    varchar(255),
        pos     int not null,
        index (pos),
        primary key (mid, gid)
);

drop table if exists profile_medals_sub;
create table profile_medals_sub (
        uid int not null,
        mid int not null,
        gid int not null,
        primary key (uid,mid)
);

insert into profile_medals (type, text, img)
     values ('ordre',     'Ordre National de la Legion d\'Honneur',     'ordre_onlh.jpg'),
            ('ordre',     'Ordre de la libération',                     'ordre_lib.jpg'),
            ('ordre',     'Ordre National du Mérite',                   'ordre_nm.jpg'),
            ('ordre',     'Ordre des Palmes Académiques',               'ordre_pa.jpg'),
            ('ordre',     'Ordre du Mérite Agricole',                   'ordre_ma.jpg'),
            ('ordre',     'Ordre du Mérite Maritime',                   'ordre_mm.jpg'),
            ('ordre',     'Ordre des Arts et des Lettres',              'ordre_al.jpg'),
            
            ('croix',     'Croix de Guerre 1914 - 1918',                'croix_1418.jpg'),
            ('croix',     'Croix de Guerre 1939 - 1945',                'croix_3945.jpg'),
            ('croix',     'Croix des T. O. E.',                         'croix_toe.jpg'),
            ('croix',     'Croix de la Valeur Militaire',               'croix_vm.jpg'),
            ('croix',     'Croix du Combattant Volontaire 1914 - 1918', 'croix_cv1418.jpg'),
            ('croix',     'Croix du Combattant Volontaire',             'croix_cv.jpg'),
            ('croix',     'Croix du Combattant',                        'croix_cc.jpg'),

            ('militaire', 'Médaille Militaire',                             'mili_mili.jpg'),
            ('militaire', 'Médaille des Évadés',                            'mili_eva.jpg'),
            ('militaire', 'Médaille de la Gendarmerie Nationale',           'mili_gn.jpg'),
            ('militaire', 'Médaille de l\'Aéronautique',                    'mili_aero.jpg'),
            ('militaire', 'Médaille du Service de Santé des Armées',        'mili_ssa.jpg'),
            ('militaire', 'Médaille de la Défense Nationale',               'mili_defnat.jpg'),
            ('militaire', 'Médaille des Services Militaires Volontaires',   'mili_smv.jpg'),
            ('militaire', 'Médaille d\'Outre-Mer',                          'mili_om.jpg'),
            ('militaire', 'Insignes des Bléssés Militaires',                'mili_ib.jpg'),
            ('militaire', 'Médaille d\'Afrique du Nord',                    'mili_an.jpg'),
            ('militaire', 'Titre de la Reconnaissance de la Nation',        'mili_trn.jpg'),
            ('militaire', 'Médaille des Engagés Volontaires',               'mili_ev.jpg'),

            ('honneur',   'Actes de Dévouement et Faits de Sauvetage',      'honn_adfs.jpg'),
            ('honneur',   'Actes de Courage et de Dévouement',              'honn_acd.jpg'),
            ('honneur',   'Médaille des Secours Mutuels',                   'honn_sm.jpg'),
            ('honneur',   'Médaille d\'Honneur des Eaux et Forêts',         'honn_ef.jpg'),
            ('honneur',   'Enseignement du Premier Degré',                  'honn_pd.jpg'),
            ('honneur',   'Ministère du Commerce et de l\'Industrie',       'honn_mci.jpg'),
            ('honneur',   'Médaille d\'Honneur des Affaires Etrangères',    'honn_ae.jpg'),
            ('honneur',   'Médaille d\'Honneur Agricole',                   'honn_agr.jpg'),
            ('honneur',   'Médaille d\'Honneur de l\'Assistance Publique',  'honn_ap.jpg'),
            ('honneur',   'Médaille d\'Honneur des Epidémies',              'honn_epi.jpg'),
            ('honneur',   'Médaille d\'Honneur des Douanes',                'honn_dou.jpg'),
            ('honneur',   'Médaille d\'Honneur Pénitentiaire',              'honn_pen.jpg'),

            ('resistance','Médaille de la Résistance Française',            'resi_rf.jpg'),
            ('resistance','Croix du Volontaire de la Résistance',           'resi_cvr.jpg'),
            ('resistance','Médaille de la Déportation - Résistance',        'resi_dr.jpg'),

            ('prix',      'Médaille Fields',                                'prix_fields.gif'),
            ('prix',      'Prix Nobel d\'Économie',                         'prix_nb_eco.jpg'),
            ('prix',      'Prix Nobel de Littérature',                      'prix_nb_lit.jpg'),
            ('prix',      'Prix Nobel de Médecine',                         'prix_nb_med.jpg'),
            ('prix',      'Prix Nobel de la Paix',                          'prix_nb_paix.jpg'),
            ('prix',      'Prix Nobel de Physique/Chimie',                  'prix_nb_pc.jpg');

insert into admin_a values (5, 'Décorations', 'admin/gerer_decos.php', 40);

