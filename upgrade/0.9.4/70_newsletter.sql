update auth_user_quick as q INNER JOIN newsletter_ins as n USING(user_id) SET q.core_mail_fmt = n.pref;
alter table newsletter_ins drop pref;

