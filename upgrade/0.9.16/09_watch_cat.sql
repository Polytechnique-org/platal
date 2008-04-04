ALTER TABLE watch_cat ADD COLUMN mail_sg_xette varchar(80) DEFAULT NULL AFTER mail_sg;
UPDATE watch_cat SET mail_sg_xette = 'Cette camarade a mis sa fiche à jour' WHERE id = 1;
UPDATE watch_cat SET mail_sg_xette = 'Cette camarade s\'est inscrite' WHERE id = 2;
UPDATE watch_cat SET mail_sg_xette = 'Cette camarade nous a quittés' WHERE id = 3;
UPDATE watch_cat SET mail_sg_xette = 'Cette camarade fête son anniversaire cette semaine' WHERE id = 4;

# vim:set syntax=mysql:
