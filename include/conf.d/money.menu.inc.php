<?php
if ($globals->money->mpay_enable) {
    $this->addPrivateEntry(XOM_SERVICES, 30, 'Micropaiments', 'paiement/');
}
?>
