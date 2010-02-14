CREATE TABLE  group_announces
        LIKE  groupex.announces;
 INSERT INTO  group_announces
      SELECT  *
        FROM  groupex.announces;

CREATE TABLE  group_announces_photo
        LIKE  groupex.announces_photo;
 INSERT INTO  group_announces_photo
      SELECT  *
        FROM  groupex.announces_photo;

CREATE TABLE  group_announces_read
        LIKE  groupex.announces_read;
 INSERT INTO  group_announces_read
      SELECT  *
        FROM  groupex.announces_read;

CREATE TABLE  groups
        LIKE  groupex.asso;
 INSERT INTO  groups
      SELECT  *
        FROM  groupex.asso;

CREATE TABLE  group_dom
        LIKE  groupex.dom;
 INSERT INTO  group_dom
      SELECT  *
        FROM  groupex.dom;

CREATE TABLE  group_events
        LIKE  groupex.evenements;
 INSERT INTO  group_events
      SELECT  *
        FROM  groupex.evenements;

CREATE TABLE  group_event_items
        LIKE  groupex.evenements_items;
 INSERT INTO  group_event_items
      SELECT  *
        FROM  groupex.evenements_items;

CREATE TABLE  group_event_participants
        LIKE  groupex.evenements_participants;
 INSERT INTO  group_event_participants
      SELECT  *
        FROM  groupex.evenements_participants;

CREATE TABLE  group_members
        LIKE  groupex.membres;
 INSERT INTO  group_members
      SELECT  *
        FROM  groupex.membres;

CREATE TABLE  group_member_sub_requests
        LIKE  groupex.membres_sub_requests;
 INSERT INTO  group_member_sub_requests
      SELECT  *
        FROM  groupex.membres_sub_requests;

CREATE TABLE  group_auth
        LIKE  x4dat.groupesx_auth;
 INSERT INTO  group_auth
      SELECT  *
        FROM  x4dat.groupesx_auth;

# vim:set ft=mysql:
