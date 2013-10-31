#!/usr/bin/php5
<?php

require_once 'connect.db.inc.php';

// Fetches the list of unregistered users.
$users = XDB::iterRow(
    "SELECT  a.uid, a.hruid, a.full_name, a.display_name,
             COUNT(m.asso_id) AS nb_assos, m.asso_id, m.comm
       FROM  accounts AS a
  LEFT JOIN  group_members AS m ON (a.uid = m.uid)
      WHERE  a.type = 'xnet' AND a.directory_name IS NULL
   GROUP BY  a.uid");

$errors = array();

// Updates directory_names.
while (list($uid, $hruid, $full, $display, $nb_assos, $asso, $comm) = $users->next()) {
    $matches = array();
    $comment = "";
    // Match any of the following:
    // ... - _MATCH_
    // ... (_MATCH_)
    // \pL means 'any unicode letter'
    if (preg_match('/([\pL- ]+)(\((.*)\)| +- +(.*))$/', $full, $matches)) {
        $full_name = trim($matches[1]);
        $comment = trim($matches[count($matches) - 1]);
    } else {
        $full_name = $full;
    }
    $matches = array();
    if (preg_match('/^([\pL.-]+) +(.*)/u', $full_name, $matches)) {
        $dir_name = mb_strtoupper(trim($matches[2])) . " " . ucwords(trim($matches[1]));
        if ($comment != "") {
            if ($nb_assos > 1) {
                $errors[$hruid] = "Can't update comment to |$comment|: more than one asso ($nb_assos).";
                continue;
            }
            if ($comm != null && $comm != "") {
                $errors[$hruid] = "Can't update comment to |$comment|: comment already set to |$comm|.";
                continue;
            }
        }
        echo "$hruid: |$full| => |$dir_name| (display: |$display|";
        if ($comment != "") {
            echo "; comment: |$comment|";
        }
        echo ").\n";
        XDB::execute('UPDATE accounts SET directory_name = {?} WHERE hruid = {?}', $dir_name, $hruid);
        if ($comment != "") {
            XDB::execute('UPDATE group_members SET comm = {?} WHERE uid = {?} AND asso_id = {?}',
                $comm, $uid, $asso);
        }
    } else {
        $errors[$hruid] = "Unable to match |$full|.";
    }
}

echo "== ERRORS ==\n";
foreach($errors as $hruid => $error) {
    echo "$hruid\n\t$error\n";
}
?>

