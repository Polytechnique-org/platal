<?php
  $fp=fopen("/etc/ssl/xorgCA/cacert.pem","r");
  $data=fread($fp,10000);
  fclose($fp);
  Header("Content-Type: application/x-x509-ca-cert");
  Header("Content-Length: ".strlen($data));
  echo $data;
?>
