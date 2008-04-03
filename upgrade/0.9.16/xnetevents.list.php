#!/usr/bin/php5
<?php

global $globals;
require_once 'connect.db.inc.php';

// Fetches the list of existing xnetevents aliases.
$events = XDB::iterRow(
    "SELECT  e.eid, e.asso_id, e.short_name, al.vid, pl.vid
       FROM  groupex.evenements AS e
  LEFT JOIN  virtual AS al ON (al.type = 'evt' AND al.alias = CONCAT(short_name, {?}))
  LEFT JOIN  virtual AS pl ON (pl.type = 'evt' AND pl.alias = CONCAT(short_name, {?}))
      WHERE  al.vid IS NOT NULL AND pl.vid IS NOT NULL
   ORDER BY  e.eid",
    '-absents@'.$globals->xnet->evts_domain,
    '-participants@'.$globals->xnet->evts_domain);

// Fixes the alias recipients for each list.
while (list($eid, $asso_id, $shortname, $absent_vid, $participant_vid) = $events->next()) {
    $recipient_count = array();
    foreach (array($absent_vid, $participant_vid) as $vid) {
        $res = XDB::query("SELECT COUNT(*) FROM  virtual_redirect WHERE vid = {?}", $vid);
        $recipient_count[$vid] = $res->fetchOneCell();
    }

    // Updates the alias for participants.
    XDB::execute("DELETE FROM virtual_redirect WHERE vid = {?}", $participant_vid);
    XDB::execute(
        "INSERT INTO  virtual_redirect (
              SELECT  {?} AS vid, IF(u.nom IS NULL, m.email, CONCAT(a.alias, {?})) AS redirect
                FROM  groupex.evenements_participants AS ep
           LEFT JOIN  groupex.membres AS m ON (ep.uid = m.uid)
           LEFT JOIN  auth_user_md5   AS u ON (u.user_id = ep.uid)
           LEFT JOIN  aliases         AS a ON (a.id = ep.uid AND a.type = 'a_vie')
               WHERE  ep.eid = {?} AND ep.nb > 0
            GROUP BY  ep.uid)",
        $participant_vid, '@'.$globals->mail->domain, $eid);

    // Updates the alias for absents.
    XDB::execute("DELETE FROM virtual_redirect WHERE vid = {?}", $absent_vid);
    XDB::execute(
        "INSERT INTO  virtual_redirect (
              SELECT  {?} AS vid, IF(u.nom IS NULL, m.email, CONCAT(a.alias, {?})) AS redirect
                FROM  groupex.membres AS m
           LEFT JOIN  groupex.evenements_participants AS ep ON (ep.uid = m.uid AND ep.eid = {?})
           LEFT JOIN  auth_user_md5   AS u ON (u.user_id = m.uid)
           LEFT JOIN  aliases         AS a ON (a.id = m.uid AND a.type = 'a_vie')
               WHERE  m.asso_id = {?} AND ep.uid IS NULL
            GROUP BY  m.uid)",
        $absent_vid, "@".$globals->mail->domain, $eid, $asso_id);

    // Lists alias recipient count changes.
    $new_recipient_count = array();
    foreach (array($absent_vid, $participant_vid) as $vid) {
        $res = XDB::query("SELECT COUNT(*) FROM  virtual_redirect WHERE vid = {?}", $vid);
        $new_recipient_count[$vid] = $res->fetchOneCell();
    }

    if ($new_recipient_count[$absent_vid] != $recipient_count[$absent_vid] ||
        $new_recipient_count[$participant_vid] != $recipient_count[$participant_vid]) {
        printf("  Fixed aliases for event %d (%s): absent list %d -> %d, participant list %d -> %d\n",
               $eid, $shortname,
               $recipient_count[$absent_vid], $new_recipient_count[$absent_vid],
               $recipient_count[$participant_vid], $new_recipient_count[$participant_vid]);
    }
}
