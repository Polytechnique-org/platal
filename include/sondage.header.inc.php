<?php

/*
 * Header spécial sondage (sans les menus x.org)
 */

// Pour des url relatives !!!
require_once("appel.inc.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta name="description" content="Sondage">
    <meta name="keywords" content="sondage">
<?php
	require_once('linkbar.inc.php');
	if (empty($_SESSION['skin_compatible'])) { ?>
    <link rel="stylesheet" type="text/css" href="<?php echo url("Sk/Base/Base.css");?>" media="screen">
<?php } ?>
    <link rel="stylesheet" type="text/css" href="<?php echo url("Sk/{$_SESSION['name']}/{$_SESSION['name']}.css");?>" media="screen">
    <link rel="icon" type="image/png" href="/images/favicon.png">
    <title>
      Sondage
    </title>
    <script language="JavaScript" type="text/javascript">
      <!--
      function Msg(msg) {
        status=msg;
        document.returnValue = true;
      }
      function getNow() {
        dt=new Date();
        dy=dt.getDay();
        mh=dt.getMonth();
        wd=dt.getDate();
        yr=dt.getYear();
        if (yr<1000) yr += 1900;
        hr=dt.getHours();
        mi=dt.getMinutes();
        if (mi<10)
          time=hr+":0"+mi;
        else
          time=hr+":"+mi;
        days=new Array ("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
        months=new Array ("janvier","février","mars","avril","mai","juin","juillet","août","septembre","octobre","novembre","décembre");
        return days[dy]+" "+wd+" "+months[mh]+" "+yr+"<br>"+time;
      }
      //-->
    </script>
  </head>
  <body>
