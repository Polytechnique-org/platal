<?php
$this->addPrivateEntry(XOM_CUSTOM,   00, 'Mes emails',          'emails.php');

if ($globals->mail->send_form) {
    $this->addPrivateEntry(XOM_SERVICES, 00, 'Envoyer un mail', 'emails/send.php');
}

$this->addPrivateEntry(XOM_SERVICES, 40, 'Patte cassée',        'emails/broken.php');
?>
