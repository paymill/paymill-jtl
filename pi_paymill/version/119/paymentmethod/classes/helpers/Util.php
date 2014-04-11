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
}