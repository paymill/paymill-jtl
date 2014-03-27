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


$fastCheckoutElv = !PaymentSelection::canPamillFastCheckout('elv', $oPlugin) ? 'false' : 'true';
$fastCheckoutCc = !PaymentSelection::canPamillFastCheckout('cc', $oPlugin) ? 'false' : 'true';

$publicKey =  trim($pi_paymill_public_key);
$js = <<<HTML
<script type="text/javascript">
    var flag = true;
    var fastCheckoutCc = "$fastCheckoutCc";
    var fastCheckoutElv = "$fastCheckoutElv";
    var PAYMILL_PUBLIC_KEY = "$publicKey";
    var debug = "$debug";
    var lang = new Array();
    // Paymill cc js lang
    lang['card_number_invalid'] = "$Credit_Card_Number_Invalid";
    lang['verfication_number_invalid'] = "$Credit_Card_Verfication_Number_Invalid";
    lang['expiration_date_invalid'] = "$Credit_Card_Expiration_Date_Invalid";
    lang['card_holder_invalid'] = "$Credit_Card_Holder_Invalid";
    // Paymill elv js lang
    lang['account_owner_invalid'] = "$Account_Holder_Invalid";
    lang['sort_code_invalid'] = "$Sort_Code_Invalid";
    lang['account_number_invalid'] = "$Account_Number_Invalid";
    lang['iban_invalid'] = "$Iban_Invalid";
    lang['bic_invalid'] = "$Bic_Invalid";
    // Paymill bridge js lang
    lang['internal_server_error'] = "$PAYMILL_internal_server_error";
    lang['invalid_public_key'] = "$PAYMILL_invalid_public_key";
    lang['invalid_payment_data'] = "$PAYMILL_invalid_payment_data";
    lang['unknown_error'] = "$PAYMILL_unknown_error";
    lang['3ds_cancelled'] = "$PAYMILL_3ds_cancelled";
    lang['field_invalid_card_number'] = "$PAYMILL_field_invalid_card_number";
    lang['field_invalid_card_exp_year'] = "$PAYMILL_field_invalid_card_exp_year";
    lang['field_invalid_card_exp_month'] = "$PAYMILL_field_invalid_card_exp_month";
    lang['field_invalid_card_exp'] = "$PAYMILL_field_invalid_card_exp";
    lang['field_invalid_card_cvc'] = "$PAYMILL_field_invalid_card_cvc";
    lang['field_invalid_card_holder'] = "$PAYMILL_field_invalid_card_holder";
    lang['field_invalid_amount_int'] = "$PAYMILL_field_invalid_amount_int";
    lang['field_field_invalid_amount'] = "$PAYMILL_field_field_invalid_amount";
    lang['field_field_field_invalid_currency'] = "$PAYMILL_field_field_field_invalid_currency";
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript" src="https://bridge.paymill.com/"></script>
<script type="text/javascript" src="$pluginPath/js/BrandDetection.js"></script>
<script type="text/javascript" src="$pluginPath/js/Iban.js"></script>
<script type="text/javascript" src="$pluginPath/js/payment.js"></script>
<link rel="stylesheet" type="text/css" href="$pluginPath/css/paymill.css" />
HTML;

PaymentSelection::setPaymillInfoTexts($smarty, $oPlugin, $pluginPath, $js);
