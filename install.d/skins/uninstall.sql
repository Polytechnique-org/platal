alter table auth_user_quick drop column skin;
delete from admin_a where url='admin/gerer_skins.php';
DROP TABLE skins;
