<?php

unset($_SESSION['pi']);
require_once(dirname(__FILE__) . '/../paymentmethod/classes/helpers/PaymentSelection.php');
$pluginPath = gibShopUrl() . "/" . PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/paymentmethod";
$debug = ($oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_debug_mode'] == 1) ? "true" : "false";

foreach ($oPlugin->oPluginSprachvariableAssoc_arr as $key => $value) { 
    $$key = $value;
}

foreach ($oPlugin->oPluginEinstellungAssoc_arr as $key => $value) { 
    $$key = $value;
}

$js = <<<HTML
<script type="text/javascript">
    var flag = true;
    var PAYMILL_PUBLIC_KEY = '$pi_paymill_public_key';
    var debug = "$debug";
    var lang = new Array();
    // Paymill cc js lang
    lang['card_number_invalid'] = '$Credit_Card_Number_Invalid';
    lang['verfication_number_invalid'] = '$Credit_Card_Verfication_Number_Invalid';
    lang['expiration_date_invalid'] = '$Credit_Card_Expiration_Date_Invalid';
    lang['card_holder_invalid'] = '$Credit_Card_Holder_Invalid';
    // Paymill elv js lang
    lang['account_owner_invalid'] = '$Account_Holder_Invalid';
    lang['sort_code_invalid'] = '$Sort_Code_Invalid';
    lang['account_number_invalid'] = '$Account_Number_Invalid';
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript" src="https://bridge.paymill.com/"></script>
<script type="text/javascript" src="$pluginPath/js/payment.js"></script>
<link rel="stylesheet" type="text/css" href="$pluginPath/css/paymill.css" />
HTML;

PaymentSelection::setPaymillInfoTexts($smarty, $oPlugin, $pluginPath, $js);
