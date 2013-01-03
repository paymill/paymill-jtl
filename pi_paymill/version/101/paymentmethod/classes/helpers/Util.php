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
     * Retrieve all needed transaction params
     * 
     * @param object $order
     * @param string $token
     * @return array
     */
    public static function getCreateTransactionParams($order, $token) 
    {
        return array(
            'amount'      => round((float)$order->fGesamtsummeKundenwaehrung * 100),
            'currency'    => $order->Waehrung->cISO,
            'token'       => $token,
            'description' => 'JTL Transaction'
        );
    }

}