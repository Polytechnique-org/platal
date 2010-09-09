-- Modify tables that are refered to.
ALTER TABLE newsletter MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE payments MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;

-- Following tables all needs a sligth change in their definition to match their foreign correspondance.
ALTER TABLE group_events MODIFY COLUMN asso_id SMALLINT(5) UNSIGNED DEFAULT NULL;
ALTER TABLE newsletter_art MODIFY COLUMN id INT(11) UNSIGNED DEFAULT NULL;
ALTER TABLE newsletter_art MODIFY COLUMN cid TINYINT(3) UNSIGNED DEFAULT NULL;
ALTER TABLE newsletter_ins MODIFY COLUMN last INT(11) UNSIGNED DEFAULT NULL;
ALTER TABLE groups MODIFY COLUMN dom SMALLINT(5) UNSIGNED DEFAULT NULL;
ALTER TABLE payments MODIFY COLUMN asso_id SMALLINT(5) UNSIGNED DEFAULT NULL;
ALTER TABLE group_events MODIFY COLUMN paiement_id INT(11) UNSIGNED DEFAULT NULL;
ALTER TABLE group_event_participants MODIFY COLUMN item_id INT(11) UNSIGNED DEFAULT NULL;

UPDATE groups SET dom = NULL WHERE dom = 0;
UPDATE payments SET asso_id = NULL WHERE asso_id = 0;
UPDATE newsletter_art SET cid = NULL WHERE cid = 0;

-- Deletes things that should have been deleted ealier.
DELETE FROM newsletter_art WHERE NOT EXISTS (SELECT * FROM newsletter WHERE newsletter.id = newsletter_art.id);
DELETE FROM log_last_sessions WHERE NOT EXISTS (SELECT * FROM log_sessions WHERE log_sessions.id = log_last_sessions.id);
DELETE FROM group_event_items WHERE NOT EXISTS (SELECT * FROM group_events WHERE group_events.eid = group_event_items.eid);
DELETE FROM group_announces_photo WHERE NOT EXISTS (SELECT * FROM group_announces WHERE group_announces.id = group_announces_photo.eid);
DELETE FROM group_announces_read WHERE NOT EXISTS (SELECT * FROM group_announces WHERE group_announces.id = group_announces_read.announce_id);
DELETE FROM group_event_participants WHERE NOT EXISTS (SELECT * FROM group_event_items WHERE group_event_items.eid = group_event_participants.eid AND group_event_items.item_id = group_event_participants.item_id);
DELETE FROM group_member_sub_requests WHERE NOT EXISTS (SELECT * FROM groups WHERE groups.id = group_member_sub_requests.asso_id);
DELETE FROM group_members WHERE NOT EXISTS (SELECT * FROM groups WHERE groups.id = group_members.asso_id);


-- Finaly we add the foreign keys.
ALTER TABLE accounts ADD FOREIGN KEY (type) REFERENCES account_types (type) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE accounts ADD FOREIGN KEY (skin) REFERENCES skins (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE announce_photos ADD FOREIGN KEY (eid) REFERENCES announces (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE announce_read ADD FOREIGN KEY (evt_id) REFERENCES announces (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE forum_subs ADD FOREIGN KEY (fid) REFERENCES forums (fid) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE geoloc_administrativeareas ADD FOREIGN KEY (country) REFERENCES geoloc_countries (iso_3166_1_a2) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE geoloc_countries ADD FOREIGN KEY (belongsTo) REFERENCES geoloc_countries (iso_3166_1_a2) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE geoloc_localities ADD FOREIGN KEY (country) REFERENCES geoloc_countries (iso_3166_1_a2) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE geoloc_subadministrativeareas ADD FOREIGN KEY (country) REFERENCES geoloc_countries (iso_3166_1_a2) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE group_announces ADD FOREIGN KEY (asso_id) REFERENCES groups (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE group_announces_photo ADD FOREIGN KEY (eid) REFERENCES group_announces (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE group_announces_read ADD FOREIGN KEY (announce_id) REFERENCES group_announces (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE group_event_items ADD FOREIGN KEY (eid) REFERENCES group_events (eid) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE group_event_participants ADD FOREIGN KEY (eid, item_id) REFERENCES group_event_items (eid, item_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE group_events ADD FOREIGN KEY (asso_id) REFERENCES groups (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE group_events ADD FOREIGN KEY (paiement_id) REFERENCES payments (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE group_member_sub_requests ADD FOREIGN KEY (asso_id) REFERENCES groups (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE group_members ADD FOREIGN KEY (asso_id) REFERENCES groups (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE groups ADD FOREIGN KEY (dom) REFERENCES group_dom (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE log_events ADD FOREIGN KEY (session) REFERENCES log_sessions (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE log_events ADD FOREIGN KEY (action) REFERENCES log_actions (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE log_last_sessions ADD FOREIGN KEY (id) REFERENCES log_sessions (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE newsletter_art ADD FOREIGN KEY (id) REFERENCES newsletter (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE newsletter_art ADD FOREIGN KEY (cid) REFERENCES newsletter_cat (cid) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE newsletter_ins ADD FOREIGN KEY (last) REFERENCES newsletter (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE payments ADD FOREIGN KEY (asso_id) REFERENCES groups (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE reminder ADD FOREIGN KEY (type_id) REFERENCES reminder_type (type_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE survey_answers ADD FOREIGN KEY (vote_id) REFERENCES survey_votes (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE survey_votes ADD FOREIGN KEY (survey_id) REFERENCES surveys (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- vim:set syntax=mysql:
