<?php

require_once(dirname(__FILE__) . '/../paymentmethod/classes/helpers/Util.php');
require_once(dirname(__FILE__) . '/../paymentmethod/classes/helpers/PaymentCheck.php');


$payment = PaymentCheck::getPayment();
if (Util::isPaymillElv($payment->cName, $oPlugin) && isset($_SESSION['cISOSprache']) && is_numeric($oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_sepa_date'])) {
    $paymentName = $payment->angezeigterName[$_SESSION['cISOSprache']];
    $snippet = $oPlugin->oPluginSprachvariableAssoc_arr['___SEPA_MAIL___'];
    $unformatted = strtotime("+ ".$oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_sepa_date'] . " DAYS");
    $args_arr['mail']->bodyText = preg_replace("/\: $paymentName/", ": $paymentName\n" . $snippet . ": " . date("d.m.Y", $unformatted) . "\n", $args_arr['mail']->bodyText);
    $args_arr['mail']->bodyHtml = preg_replace("/\: $paymentName/", ": $paymentName<br>\n" . $snippet . ": " . date("d.m.Y", $unformatted) . "<br>\n", $args_arr['mail']->bodyHtml);
}