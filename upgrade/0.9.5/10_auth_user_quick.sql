ALTER TABLE auth_user_quick
ADD profile_mobile VARCHAR(20) NOT NULL,
ADD profile_mobile_pub ENUM('private', 'ax', 'public') DEFAULT 'private' NOT NULL,
ADD profile_web VARCHAR(255) NOT NULL,
ADD profile_web_pub ENUM('private', 'public') DEFAULT 'private' NOT NULL,
ADD profile_freetext VARCHAR(255) NOT NULL,
ADD profile_freetext_pub ENUM('private', 'public') DEFAULT 'private' NOT NULL,
ADD profile_medals_pub ENUM('private', 'public') DEFAULT 'private' NOT NULL;
UPDATE auth_user_quick AS q INNER JOIN auth_user_md5 AS u USING(user_id) SET
q.profile_mobile = u.mobile,
q.profile_mobile_pub = IF(FIND_IN_SET('mobile_public', u.bits), 'public', IF(FIND_IN_SET('mobile_ax', u.bits), 'ax', 'private')),
q.profile_web = u.web,
q.profile_web_pub = IF(FIND_IN_SET('web_public', u.bits), 'public', 'private'),
q.profile_freetext = u.libre,
q.profile_freetext_pub = IF(FIND_IN_SET('libre_public', u.bits), 'public', 'private');
ALTER TABLE auth_user_md5 DROP mobile, DROP web, DROP libre, CHANGE bits bits SET('mail_ax') NOT NULL;
