<?php

$payment = $_SESSION['Zahlungsart'];

if (array_key_exists('paymill_error', $_SESSION)) {
    $pluginPath = gibShopUrl() . "/" . PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/paymentmethod";
    $error = $_SESSION['paymill_error'];
    
$html = <<<HTML
    <link rel="stylesheet" type="text/css" href="$pluginPath/css/paymill.css" />
    <div class="payment-error" style="display: block;">$error</div>
HTML;

    foreach ($smarty->_tpl_vars['Zahlungsarten'] as $payment) {
        if ($_SESSION['paymill_method'] === $payment->cName) {
            $payment->cHinweisText[$_SESSION['cISOSprache']] = $html . $payment->cHinweisText[$_SESSION['cISOSprache']];
        }
    }
    
    unset($_SESSION['paymill_error']);
}

unset($_SESSION['pi']);
