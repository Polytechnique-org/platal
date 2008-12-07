insert into account_types
     values ('x', 'mail,groups,forums,list,search,portal');

insert into accounts
     select u.user_id AS uid, hruid AS hruid, 'x' AS type,
            perms = 'admin' AS is_admin,
            IF(perms = 'admin' or perms = 'user', 'active', perms) AS state,
            IF(LENGTH(password) = 40, password, NULL) AS password,
            date_ins AS registration_date,
            IF(FIND_IN_SET('watch', flags), 'watch', '') AS flags,
            IF(LENGTH(comment) > 0, comment, NULL) AS comment,
            CONCAT(prenom, ' ', IF (nom_usage != '' and nom_usage IS NOT NULL, nom_usage, nom)) AS full_name,
            prenom AS display_name,
            IF(FIND_IN_SET('femme', flags), 'female', 'male') AS sex,
            IF(q.core_mail_fmt = 'html', 'html', 'plain') AS mail_format,
            q.skin AS skin
       from auth_user_md5 as u
  left join auth_user_quick as q on (q.user_id = u.user_id)
      where hruid is not null;

insert into account_profiles
     select user_id AS uid, user_id AS pid, 'owner' AS perms
       from auth_user_md5
      where hruid is not null;

# vim:set syntax=mysql:
