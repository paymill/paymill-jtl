<?php

require_once(dirname(__FILE__) . '/../paymentmethod/classes/helpers/Util.php');
require_once(dirname(__FILE__) . '/../paymentmethod/classes/helpers/PaymentCheck.php');
$payment = PaymentCheck::getPayment();
if (Util::isPaymillPayment($payment->cName, $oPlugin)) {
    $posted = array_key_exists('paymillToken', $_POST) && PaymentCheck::checkToken($_POST['paymillToken']);
    $session = array_key_exists('paymillToken', $_SESSION['pi']) && PaymentCheck::checkToken($_SESSION['pi']['paymillToken']);
    if ($posted || $session) {
        PaymentCheck::setToken(!empty($_SESSION['pi']['paymillToken']) ? $_SESSION['pi']['paymillToken'] : $_POST['paymillToken']);
    } else {
        $_SESSION['pi_error']['error'] = 'Unerwarteter Fehler!';
        header("Location: " . gibShopURL() . '/bestellvorgang.php?editZahlungsart=1');
    }
}