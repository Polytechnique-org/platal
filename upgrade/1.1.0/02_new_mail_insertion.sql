-- 1/ Feeds email_virtual_domains from virtual_domains and aliases.

INSERT INTO  email_virtual_domains (name)
     VALUES  ('polytechnique.org');
INSERT INTO  email_virtual_domains (name)
     SELECT  domain
       FROM  virtual_domains;
UPDATE  email_virtual_domains
   SET  aliasing = id;

SET @p_domain_id = 0;
SET @m_domain_id = 0;
SELECT  @p_domain_id := id
  FROM  email_virtual_domains
 WHERE  name = 'polytechnique.org';
SELECT  @m_domain_id := id
  FROM  email_virtual_domains
 WHERE  name = 'melix.net';

DELETE FROM  email_virtual_domains
      WHERE  name IN ('m4x.org', 'melix.org', 'x-banque.m4x.org', 'staff.m4x.org', 'x-consult.m4x.org');
INSERT INTO  email_virtual_domains (name, aliasing)
     SELECT  'm4x.org', id
       FROM  email_virtual_domains
      WHERE  name = 'polytechnique.org';
INSERT INTO  email_virtual_domains (name, aliasing)
     SELECT  'melix.org', id
       FROM  email_virtual_domains
      WHERE  name = 'melix.net';
INSERT INTO  email_virtual_domains (name, aliasing)
     SELECT  'x-banque.m4x.org', id
       FROM  email_virtual_domains
      WHERE  name = 'x-banque.polytechnique.org';
INSERT INTO  email_virtual_domains (name, aliasing)
     SELECT  'staff.m4x.org', id
       FROM  email_virtual_domains
      WHERE  name = 'staff.polytechnique.org';
INSERT INTO  email_virtual_domains (name, aliasing)
     SELECT  'x-consult.m4x.org', id
       FROM  email_virtual_domains
      WHERE  name = 'x-consult.polytechnique.org';

INSERT INTO  email_virtual_domains (name, aliasing)
     VALUES  ('alumni.polytechnique.org', @p_domain_id), ('alumni.m4x.org', @p_domain_id),
             ('master.polytechnique.org', 1), ('doc.polytechnique.org', 1);
SET @master_domain_id = 0;
SET @doc_domain_id = 0;
SELECT  @master_domain_id := id
  FROM  email_virtual_domains
 WHERE  name = 'master.polytechnique.org';
SELECT  @doc_domain_id := id
  FROM  email_virtual_domains
 WHERE  name = 'doc.polytechnique.org';
UPDATE  email_virtual_domains
   SET  aliasing = @master_domain_id
 WHERE  name = 'master.polytechnique.org';
UPDATE  email_virtual_domains
   SET  aliasing = @doc_domain_id
 WHERE  name = 'doc.polytechnique.org';
INSERT INTO  email_virtual_domains (name, aliasing)
     VALUES  ('alumni.polytechnique.org', @master_domain_id), ('alumni.m4x.org', @master_domain_id),
             ('alumni.polytechnique.org', @doc_domain_id), ('alumni.m4x.org', @doc_domain_id),
             ('master.m4x.org', @master_domain_id), ('doc.m4x.org', @doc_domain_id);

-- 2/ Feeds email_source_account
INSERT INTO  email_source_account (uid, domain, email, type, flags, expire)
     SELECT  uid, @p_domain_id, alias, IF(type = 'a_vie', 'forlife', 'alias'), REPLACE(flags, 'epouse', 'marital'), expire
       FROM  aliases
      WHERE  type = 'a_vie' OR type = 'alias';
INSERT INTO  email_source_account (uid, domain, email, type)
     SELECT  a.uid, @m_domain_id, SUBSTRING_INDEX(v.alias, '@', 1), 'alias_aux'
       FROM  virtual          AS v
  LEFT JOIN  virtual_redirect AS vr ON (v.vid = vr.vid)
  LEFT JOIN  accounts         AS a  ON (a.hruid = LEFT(vr.redirect, LOCATE('@', vr.redirect) - 1))
      WHERE  v.type = 'user' AND v.alias LIKE '%@melix.net' AND a.uid IS NOT NULL;

-- 3/ Feeds email_source_other
INSERT INTO  email_source_other (hrmid, email, domain, type, expire)
     SELECT  CONCAT(CONCAT('h.', alias), '.polytechnique.org'), alias, @p_domain_id, 'homonym', IF(expire IS NULL, '0000-00-00', expire)
       FROM  aliases
      WHERE  type = 'homonyme'
   GROUP BY  alias;
INSERT INTO  email_source_other (hrmid, email, type, domain)
     VALUES  ('ax.test.polytechnique.org', 'AX-test', 'ax', @p_domain_id),
             ('ax.nicolas.zarpas.polytechnique.org', 'AX-nicolas.zarpas', 'ax', @p_domain_id),
             ('ax.carrieres.polytechnique.org', 'AX-carrieres', 'ax', @p_domain_id),
             ('ax.info1.polytechnique.org', 'AX-info1', 'ax', @p_domain_id),
             ('ax.info2.polytechnique.org', 'AX-info2', 'ax', @p_domain_id),
             ('ax.bal.polytechnique.org', 'AX-bal', 'ax', @p_domain_id),
             ('ax.annuaire.polytechnique.org', 'AX-annuaire', 'ax', @p_domain_id),
             ('ax.jaune-rouge.polytechnique.org', 'AX-jaune-rouge', 'ax', @p_domain_id),
             ('honey.jean-pierre.bilah.1980.polytechnique.org', 'jean-pierre.bilah.1980', 'honeypot', @p_domain_id),
             ('honey.jean-pierre.bilah.1980.polytechnique.org', 'jean-pierre.blah.1980', 'honeypot', @p_domain_id);

-- 4/ Feeds homonyms_list
INSERT INTO  homonyms_list (hrmid, uid)
     SELECT  CONCAT(CONCAT('h.', a.alias), '.polytechnique.org'), h.uid
       FROM  homonyms AS h
 INNER JOIN  aliases  AS a ON (a.uid = h.homonyme_id)
      WHERE  a.type = 'homonyme';

-- 5/ Feeds email_redirect_account
INSERT INTO  email_redirect_account (uid, redirect, rewrite, type, action, broken_date, broken_level, last, flags, hash, allow_rewrite)
     SELECT  e.uid, e.email, e.rewrite, 'smtp', ef.email, e.panne, e.panne_level, e.last,
             IF(e.flags = '', 'inactive', IF(e.flags = 'disable', 'disabled', IF(e.flags = 'panne', 'broken', e.flags))), e.hash, e.allow_rewrite
       FROM  emails   AS e
  LEFT JOIN  emails   AS ef ON (e.uid = ef.uid)
      WHERE  e.flags != 'filter' AND ef.flags = 'filter';
INSERT INTO  email_redirect_account (uid, redirect, type, action, flags)
     SELECT  a.uid, CONCAT(a.hruid, '@g.polytechnique.org'), 'googleapps', ef.email, 'active'
       FROM  email_options AS eo
  LEFT JOIN  accounts      AS a  ON (a.uid = eo.uid)
  LEFT JOIN  emails        AS ef ON (eo.uid = ef.uid)
      WHERE  FIND_IN_SET('googleapps', eo.storage) AND ef.flags = 'filter';
INSERT INTO  email_redirect_account (uid, redirect, type, action, flags)
     SELECT  a.uid, CONCAT(a.hruid, '@imap.polytechnique.org'), 'imap', 'let_spams', 'active'
       FROM  email_options AS eo
  LEFT JOIN  accounts      AS a ON (a.uid = eo.uid)
      WHERE  FIND_IN_SET('imap', eo.storage);

-- Imap and bounce
UPDATE  email_redirect_account AS e,
        (SELECT  IF(SUM(IF(type != 'imap', 1, 0)) = 0, 'imap_only', 'normal') AS status, uid
           FROM  email_redirect_account
          WHERE  flags = 'active'
       GROUP BY  uid) AS sub
   SET  e.action = 'imap_and_bounce'
 WHERE  sub.status = 'imap_only' AND sub.uid = e.uid AND type = 'imap';

-- 6/ Feeds email_redirect_other
INSERT INTO  email_redirect_other (hrmid, type, action)
     SELECT  hrmid, 'homonym', 'homonym'
       FROM  email_source_other
      WHERE  type = 'homonym'
   GROUP BY  (hrmid);

INSERT INTO  email_redirect_other (hrmid, redirect, type, action)
     VALUES  ('ax.nicolas.zarpas.polytechnique.org', 'nicolas.zarpas-ax@wanadoo.fr', 'smtp', 'tag_spams'),
             ('ax.carrieres.polytechnique.org', 'manuela.brasseur-bdc@wanadoo.fr', 'smtp', 'tag_spams'),
             ('ax.info1.polytechnique.org', 'sylvie.clairefond-ax@wanadoo.fr', 'smtp', 'tag_spams'),
             ('ax.info2.polytechnique.org', 'catherine.perot-ax@wanadoo.fr', 'smtp', 'tag_spams'),
             ('ax.bal.polytechnique.org', 'baldelx-ax@wanadoo.fr', 'smtp', 'tag_spams'),
             ('ax.annuaire.polytechnique.org', 'annuaire-ax@wanadoo.fr', 'smtp', 'tag_spams'),
             ('ax.jaune-rouge.polytechnique.org', 'jaune_rouge@wanadoo.fr', 'smtp', 'tag_spams'),
             ('honey.jean-pierre.bilah.1980.polytechnique.org', 'jean-pierre.bilah.1980.mbox@murphy.m4x.org', 'smtp', 'let_spams'),
             ('honey.jean-pierre.bilah.1980.polytechnique.org', 'raphael.barrois.2006@polytechnique.org', 'smtp', 'let_spams');

-- 7/ Feeds email_virtual

-- Note: There are some adresses on virtual that have no match on the virtual_redirect.
--       The adresses in this situation are dropped.

INSERT INTO  email_virtual (email, domain, redirect, type)
     SELECT  SUBSTRING_INDEX(v.alias, '@', 1), d.id, vr.redirect, IF(v.type = 'evt', 'event', v.type)
       FROM  virtual               AS v
 INNER JOIN  email_virtual_domains AS d  ON (SUBSTRING_INDEX(v.alias, '@', -1) = d.name AND d.id = d.aliasing)
  LEFT JOIN  virtual_redirect      AS vr ON (vr.vid = v.vid)
      WHERE  v.alias NOT LIKE '%@melix.net' AND vr.vid IS NOT NULL AND v.type != 'dom';

INSERT INTO  email_virtual (email, type, domain, redirect)
     SELECT  alias, 'list', @p_domain_id,
             CONCAT('polytechnique.org_', REPLACE(REPLACE(REPLACE(CONCAT(alias, '+post@listes.polytechnique.org'),
                                                                  '-admin+post', '+admin'),
                                                          '-owner+post', '+owner'),
                                                  '-bounces+post', '+bounces'))
       FROM  aliases
      WHERE  type = 'liste';

INSERT INTO  email_virtual (email, redirect, domain, type)
     SELECT  SUBSTRING_INDEX(v.alias, '@', 1), vr.redirect, @m_domain_id, 'alias'
       FROM  virtual          AS v
  LEFT JOIN  virtual_redirect AS vr ON (v.vid = vr.vid)
  LEFT JOIN  accounts         AS a  ON (a.hruid = LEFT(vr.redirect, LOCATE('@', vr.redirect) - 1))
      WHERE  v.type = 'user' AND v.alias LIKE '%@melix.net' AND vr.vid IS NOT NULL AND a.uid IS NULL;

-- From aliases file
INSERT INTO  email_virtual (domain, email, redirect, type)
     VALUES  (@p_domain_id, 'otrs.platal', 'otrs.platal@svoboda.polytechnique.org', 'admin'),
             (@p_domain_id, 'validation', 'hotliners@staff.polytechnique.org', 'admin'),
             (@p_domain_id, 'listes+admin', 'br@staff.polytechnique.org', 'admin'),
             (@p_domain_id, 'listes', 'otrs.platal+listes@polytechnique.org', 'admin'),
             (@p_domain_id, 'gld', 'listes@polytechnique.org', 'admin'),
             (@p_domain_id, 'support', 'otrs.platal+support@polytechnique.org', 'admin'),
             (@p_domain_id, 'contact', 'otrs.platal+contact@polytechnique.org', 'admin'),
             (@p_domain_id, 'register', 'otrs.platal+register@polytechnique.org', 'admin'),
             (@p_domain_id, 'info', 'otrs.platal+info@polytechnique.org', 'admin'),
             (@p_domain_id, 'bug', 'otrs.platal+bug@polytechnique.org', 'admin'),
             (@p_domain_id, 'resetpass', 'otrs.platal+resetpass@polytechnique.org', 'admin'),
             (@p_domain_id, 'association', 'otrs.platal+association@polytechnique.org', 'admin'),
             (@p_domain_id, 'x-org', 'association@polytechnique.org', 'admin'),
             (@p_domain_id, 'manageurs', 'otrs@support.manageurs.com', 'partner'),
             (@p_domain_id, 'fondation', 'fondation@fondationx.org', 'partner'),
             (@p_domain_id, 'ax', 'ax@wanadoo.fr', 'partner'),
             (@p_domain_id, 'annuaire-ax', 'annuaire-ax@wanadoo.fr', 'partner'),
             (@p_domain_id, 'ax-bdc', 'ax-bdc@wanadoo.fr', 'partner'),
             (@p_domain_id, 'jaune', 'null@hruid.polytechnique.org', 'partner'),
             (@p_domain_id, 'jaune+rouge', 'jaune_rouge@wanadoo.fr', 'partner'),
             (@p_domain_id, 'xcourseaularge', 'info@xcourseaularge.polytechnique.org', 'partner'),
             (@p_domain_id, 'xim', 'membres@x-internet.polytechnique.org', 'partner'),
             (@p_domain_id, 'x-consult', 'info@x-consult.polytechnique.org', 'partner'),
             (@p_domain_id, 'xmcb', 'xmcb@x-consult.polytechnique.org', 'partner'),
             (@p_domain_id, 'x-maroc', 'allam@mtpnet.gov.ma', 'partner'),
             (@p_domain_id, 'x-musique', 'xmusique@free.fr', 'partner'),
             (@p_domain_id, 'x-resistance', 'info@xresistance.org', 'partner'),
             (@p_domain_id, 'x-israel', 'info@x-israel.polytechnique.org', 'partner'),
             (@p_domain_id, 'gpx', 'g.p.x@infonie.fr', 'partner'),
             (@p_domain_id, 'g.p.x', 'gpx@polytechnique.org', 'partner'),
             (@p_domain_id, 'pointgamma', 'gamma@frankiz.polytechnique.fr', 'partner'),
             (@p_domain_id, 'xmpentrepreneur', 'xmp.entrepreneur@gmail.com', 'partner'),
             (@p_domain_id, 'xmp-entrepreneur', 'xmp.entrepreneur@gmail.com', 'partner'),
             (@p_domain_id, 'xmpangels', 'xmpangels@xmp-ba.m4x.org', 'partner'),
             (@p_domain_id, 'xmp-angels', 'xmpangels@xmp-ba.m4x.org', 'partner'),
             (@p_domain_id, 'relex', 'relex@staff.polytechnique.org', 'admin'),
             (@p_domain_id, 'tresorier', 'tresorier@staff.polytechnique.org', 'admin'),
             (@p_domain_id, 'aaege-sso', 'aaege-sso@staff.polytechnique.org', 'admin'),
             (@p_domain_id, 'innovation', 'innovation@staff.polytechnique.org', 'admin'),
             (@p_domain_id, 'groupes', 'groupes@staff.polytechnique.org', 'admin'),
             (@p_domain_id, 'br', 'br@staff.polytechnique.org', 'admin'),
             (@p_domain_id, 'ca', 'ca@staff.polytechnique.org', 'admin'),
             (@p_domain_id, 'personnel', 'br@staff.polytechnique.org', 'admin'),
             (@p_domain_id, 'cil', 'cil@staff.polytechnique.org', 'admin'),
             (@p_domain_id, 'opensource', 'contact@polytechnique.org', 'admin'),
             (@p_domain_id, 'forums', 'forums@staff.m4x.org', 'admin'),
             (@p_domain_id, 'telepaiement', 'telepaiement@staff.m4x.org', 'admin'),
             (@p_domain_id, 'hotliners', 'hotliners@staff.m4x.org', 'admin'),
             (@p_domain_id, 'kes', 'kes@frankiz.polytechnique.fr', 'partner'),
             (@p_domain_id, 'kes1999', 'cariokes@polytechnique.org', 'partner'),
             (@p_domain_id, 'kes2000', 'kestinpowers@polytechnique.org', 'partner');

-- Drop renamed list
DELETE FROM email_virtual WHERE email LIKE 'tech-email%';

-- Deletes erroneous domains
DELETE FROM  email_virtual_domains
      WHERE  name IN ('fanfarix.polytechnique.net', 'asd', 'x-russie', 'formation', 'groupetest', 'x-sursaut');

-- Deletes unused domains
DELETE FROM  email_virtual_domains
      WHERE  name LIKE 'manageurs.%';

-- vim:set syntax=mysql:
