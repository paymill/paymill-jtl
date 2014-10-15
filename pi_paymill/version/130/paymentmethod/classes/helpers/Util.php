<?php

class Util
{

    /**
     * Is paymill cc payment
     *
     * @param string $paymentName
     * @param object $oPlugin
     * @return boolean
     */
    public static function isPaymillCc($paymentName, $oPlugin)
    {
        return $oPlugin->oPluginZahlungsmethodeAssoc_arr['kPlugin_' . $oPlugin->kPlugin . '_paymillcc']->cName == $paymentName;
    }

    /**
     * Is paymill elv payment
     *
     * @param string $paymentName
     * @param object $oPlugin
     * @return boolean
     */
    public static function isPaymillElv($paymentName, $oPlugin)
    {
        return $oPlugin->oPluginZahlungsmethodeAssoc_arr['kPlugin_' . $oPlugin->kPlugin . '_paymillelv']->cName == $paymentName;
    }

    /**
     * Is paymill payment
     *
     * @param string $paymentName
     * @param object $oPlugin
     * @return boolean
     */
    public static function isPaymillPayment($paymentName, $oPlugin)
    {
        return self::isPaymillCc($paymentName, $oPlugin) || self::isPaymillElv($paymentName, $oPlugin);
    }

     /**
     * Paymill log function
     *
     * @global object $oPlugin
     * @param string $message
     */
    public static function paymillLog($message, $debugInfo)
    {
        global $oPlugin;

        if ($oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_debug_mode']) {
            if (array_key_exists('paymill_identifier', $_SESSION)) {
                 $GLOBALS['DB']->executeQuery("INSERT INTO `xplugin_pi_paymill_log` "
                            . "(debug, message, identifier) "
                            . "VALUES('"
                              . $debugInfo . "', '"
                              . $message . "', '"
                              . $_SESSION['paymill_identifier']
                            . "')"
                );
            }
        }
    }

    /**
     * Returns an array with all creditcard brands
     *
     * @global object $oPlugin
     * @return array
     */
    public static function getEnabledBrands($oPlugin){
        return array(
            'visa' => $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_creditcardbrand_visa'] === "1",
            'china-unionpay' => $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_creditcardbrand_china_unionpay'] === "1",
            'mastercard' => $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_creditcardbrand_mastercard'] === "1",
            'maestro' => $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_creditcardbrand_maestro'] === "1",
            'jcb' => $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_creditcardbrand_jcb'] === "1",
            'discover' => $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_creditcardbrand_discover'] === "1",
            'dankort' => $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_creditcardbrand_dankort'] === "1",
            'diners-club' => $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_creditcardbrand_dinersclub'] === "1",
            'carte-bleue' => $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_creditcardbrand_carte_bleue'] === "1",
            'carta-si' => $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_creditcardbrand_carta_si'] === "1",
            'amex' => $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_creditcardbrand_amex'] === "1"
        );
    }


}