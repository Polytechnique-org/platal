<?php

/* Ce script donne un challenge et un numéro de session afin de permettre l'extraction de données pour eConfiance tout en procédant à une identification préalable... */

session_start();
session_register("chall");
$_SESSION["chall"] = uniqid(rand(), 1);
echo $_SESSION["chall"] . "\n" . session_id();

?>
