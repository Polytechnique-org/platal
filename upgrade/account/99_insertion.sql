# Create a type 'X' with all permissions
insert into account_types
     values ('x', 'mail,groups,forums,list,search,portal'),
            ('xnet', 'groups');


# Insert all existing accounts
insert into accounts
     select u.user_id AS uid, hruid AS hruid, 'x' AS type,
            perms = 'admin' AS is_admin,
            IF(perms = 'admin' or perms = 'user', 'active', perms) AS state,
            IF(LENGTH(password) = 40, password, NULL) AS password,
            IF(LENGTH(q.core_rss_hash) > 0, q.core_rss_hash, NULL) AS token,
            IF(LENGTH(smtppass) = 0, NULL, smtppass) AS weak_password,
            date_ins AS registration_date,
            IF(FIND_IN_SET('watch', flags), 'watch', '') AS flags,
            IF(LENGTH(comment) > 0, comment, NULL) AS comment,
            NULL as email,
            CONCAT(prenom, ' ', IF (nom_usage != '' and nom_usage IS NOT NULL, nom_usage, nom)) AS full_name,
            prenom AS display_name,
            IF(FIND_IN_SET('femme', flags), 'female', 'male') AS sex,
            IF(q.core_mail_fmt = 'html', 'html', 'text') AS email_format,
            q.skin AS skin,
            q.last_version AS last_version
       from #x4dat#.auth_user_md5 as u
  left join #x4dat#.auth_user_quick as q on (q.user_id = u.user_id)
      where hruid is not null;

# Insert carnet-relative data
insert into watch
     select q.user_id as uid, q.watch_flags as flags,
            CONCAT(IF(ws1.cid IS NULL, '', 'profile'), ',',
                   IF(ws2.cid IS NULL, '', 'registration'), ',',
                   IF(ws3.cid IS NULL, '', 'death'), ',',
                   IF(ws4.cid IS NULL, '', 'birthday')) AS actions,
            q.watch_last as last
       from #x4dat#.auth_user_quick as q
  left join #x4dat#.watch_sub as ws1 on (ws1.uid = q.user_id and ws1.cid = 1)
  left join #x4dat#.watch_sub as ws2 on (ws2.uid = q.user_id and ws2.cid = 2)
  left join #x4dat#.watch_sub as ws3 on (ws3.uid = q.user_id and ws3.cid = 3)
  left join #x4dat#.watch_sub as ws4 on (ws4.uid = q.user_id and ws4.cid = 4);

# Insert carvas
insert into carvas
     select user_id, redirecturl
       from #x4dat#.auth_user_quick
      where LENGTH(redirecturl) > 0;

# Insert all existing profiles
insert into profiles
     select u.user_id AS pid, u.hruid AS hrpid, u.matricule AS xorg_id,
            u.matricule_ax AS ax_id, u.naissance AS birthdate, u.naissance_ini AS birthdate_ref,
            u.naissance AS next_birthday,
            IF(u.deces = 0, NULL, u.deces) AS deathdate,
            IF(u.deces = 0, NULL, u.deces) AS deathdate_rec,
            IF(FIND_IN_SET('femme', flags), 'female', 'male') AS sex,
            IF(u.section = 0, NULL, u.section) AS section,
            IF(LENGTH(u.cv) > 0, u.cv, NULL) AS cv,
            IF(LENGTH(q.profile_freetext) > 0, q.profile_freetext, NULL) AS freetext,
            IF(q.profile_freetext_pub = 'public', 'public', 'private') AS freetext_pub,
            IF(q.profile_medals_pub = 'public', 'public', 'private') AS medals_pub,
            IF(q.emails_alias_pub = 'public', 'public', 'private') AS alias_pub,
            IF(u.nationalite = '' OR u.nationalite IS NULL or u.nationalite = '00' or u.nationalite = '0', NULL, u.nationalite) AS nationality1,
            NULL AS nationality2, NULL AS nationality3, NULL AS email_directory, u.date AS last_change
       from #x4dat#.auth_user_md5 AS u
  left join #x4dat#.auth_user_quick AS q ON (u.user_id = q.user_id)
      where u.hruid is not null;

# Add associations account <-> profile
insert into account_profiles
     select user_id AS uid, user_id AS pid, 'owner' AS perms
       from #x4dat#.auth_user_md5
      where hruid is not null;

# Update banana last_seen timetamp
    update  forum_profiles as fp
inner join  #x4dat#.auth_user_quick as q ON (q.user_id = fp.uid)
       set  fp.uid = fp.uid, fp.tree_unread = fp.tree_unread, fp.tree_read = fp.tree_read,
            fp.last_seen = q.banana_last;

insert ignore into  forum_profiles (uid, last_seen)
            select  user_id as uid, banana_last as last_seen
              from  #x4dat#.auth_user_quick
             where  banana_last >= DATE_SUB(NOW(), INTERVAL 6 MONTH);

# Mail storage has been moved out of account settings
insert into email_options
     select user_id as uid, mail_storage as storage
       from #x4dat#.auth_user_md5;

# vim:set syntax=mysql:
