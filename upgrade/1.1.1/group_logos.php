#!/usr/bin/php5
<?php

require_once 'connect.db.inc.php';

$globals->debug = 0; // Do not store backtraces.
$MAX_X = 200;
$MAX_Y = 100;

$it = XDB::rawIterator('SELECT  id, diminutif, logo, logo_mime
                          FROM  groups
                         WHERE  logo IS NOT NULL AND logo != ""');

while ($row = $it->next()) {
  $group_id = $row['id'];
  $group_name = $row['diminutif'];
  $logo = $row['logo'];
  $mime = $row['mime'];
  $img = imagecreatefromstring($logo);
  if ($img === false) {
    print "\n\nError reading image for:\n     $group_name\n\n";
    continue;
  }
  $x = imagesx($img);
  $y = imagesy($img);
  $nx = $x;
  $ny = $y;
  if ($x > $MAX_X || $y > $MAX_Y) {
    if ($x > $MAX_X) {
      $ny = intval($y * $MAX_X / $x);
      $nx = $MAX_X;
    }
    if ($y > $MAX_Y) {
      $nx = intval($x*$MAX_Y/$y);
      $ny = $MAX_Y;
    }
    $img2 = imagecreatetruecolor($nx, $ny);
    imagealphablending($img2, false);
    imagesavealpha($img2,true);
    $transparent = imagecolorallocatealpha($img2, 255, 255, 255, 127);
    imagefilledrectangle($img2, 0, 0, $nx, $ny, $transparent);
    imagecopyresampled($img2, $img, 0, 0, 0, 0, $nx, $ny, $x, $y);
    $tmpf = tempnam('/tmp', 'upgrade_111_group_logos');
    imagepng($img2, $tmpf);
    $f = fopen($tmpf, 'r');
    $logo2 = fread($f, filesize($tmpf));
    fclose($f);
    unlink($tmpf);
    XDB::execute("UPDATE  groups
                     SET  logo = {?}, logo_mime = 'image/png'
                   WHERE  id = {?}", $logo2, $group_id);
  }
}



