<?php

unset($_SESSION['pi']);
require_once(dirname(__FILE__) . '/../paymentmethod/classes/helpers/PaymentSelection.php');
$pluginPath = URL_SHOP . "/" . PFAD_PLUGIN . $oPlugin->cVerzeichnis . "/" . PFAD_PLUGIN_VERSION . $oPlugin->nVersion . "/paymentmethod/";
?>
<script type="text/javascript">
    var PAYMILL_PUBLIC_KEY = '<?php echo $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_public_key']; ?>';
    var debug = <?php echo ($oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_debug_mode'] == 1) ? "true" : "false"; ?>;
    var lang = new Array();
    // Paymill cc js lang
    lang['card_number_invalid'] = "<?php echo $oPlugin->oPluginSprachvariableAssoc_arr['Credit_Card_Number_Invalid']; ?>";
    lang['verfication_number_invalid'] = "<?php echo $oPlugin->oPluginSprachvariableAssoc_arr['Credit_Card_Verfication_Number_Invalid']; ?>";
    lang['expiration_date_invalid'] = "<?php echo $oPlugin->oPluginSprachvariableAssoc_arr['Credit_Card_Expiration_Date_Invalid']; ?>";
    lang['card_holder_invalid'] = "<?php echo $oPlugin->oPluginSprachvariableAssoc_arr['Credit_Card_Holder_Invalid']; ?>";
    // Paymill elv js lang
    lang['account_owner_invalid'] = "<?php echo $oPlugin->oPluginSprachvariableAssoc_arr['Account_Holder_Invalid']; ?>";
    lang['sort_code_invalid'] = "<?php echo $oPlugin->oPluginSprachvariableAssoc_arr['Sort_Code_Invalid']; ?>";
    lang['account_number_invalid'] = "<?php echo $oPlugin->oPluginSprachvariableAssoc_arr['Account_Number_Invalid']; ?>";
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_payment_bridge']; ?>"></script>
<script type="text/javascript" src="<?php echo $pluginPath . "js/payment.js"; ?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $pluginPath . "css/paymill.css"; ?>" />
<?php

PaymentSelection::setPaymillInfoTexts($smarty, $oPlugin, $pluginPath);
