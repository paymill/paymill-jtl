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
    public static function paymillLog($message)
    {
        global $oPlugin;

        if ($oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_debug_mode']) {
            Jtllog::writeLog($message, JTLLOG_LEVEL_DEBUG);
        }
    }
    
    /**
     * Return different amount save
     * 
     * @param object $oPlugin
     * @return float
     */
    public static function getDifferentAmount($oPlugin)
    {
        $differentAmount = $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_different_amount'];
        if (!empty($differentAmount) && preg_match('/^[0-9]+(\.[0-9][0-9][0-9])?(,[0-9]{1,2})?$/', $differentAmount)) {
            $differentAmount = str_replace(".", "", $differentAmount);
            $differentAmount = str_replace(",", ".", $differentAmount);
        } else if (!empty($differentAmount) && preg_match('/^[0-9]+(\,[0-9][0-9][0-9])?(.[0-9]{1,2})?$/', $differentAmount)) {
            $differentAmount = str_replace(",", "", $differentAmount);
        } else {
            $differentAmount = 0;
        }
        
        return $differentAmount;
    }
}