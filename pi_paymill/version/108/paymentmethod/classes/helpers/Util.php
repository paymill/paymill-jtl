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

    public static function getCreateClientParams($order)
    {
        return array(
            'email' => $order->oRechnungsadresse->cMail,
            'description' => $order->oRechnungsadresse->cVorname . ' ' . $order->oRechnungsadresse->cNachname
        );
    }
    
    public static function getCreatePaymentParams($token, $client)
    {
        return array(
            'token' => $token,
            'client' => $client['id']
        );
    }
    
    /**
     * Retrieve all needed transaction params
     * 
     * @param object $order
     * @param string $token
     * @return array
     */
    public static function getCreateTransactionParams($order, $payment) 
    {
        global $Einstellungen;
        return array(
            'amount'      => round((float)$order->fGesamtsummeKundenwaehrung * 100),
            'currency'    => $order->Waehrung->cISO,
            'description' => $Einstellungen['global']['global_shopname'] . 'Bestellnummer: ' . baueBestellnummer() . ', ' . $order->oRechnungsadresse->cMail,
            'payment'     => $payment['id']
        );
    }
    
    public static function paymillLog($message)
    {
        global $oPlugin;
        
        if ($oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_debug_mode']) {
            Jtllog::writeLog($message, JTLLOG_LEVEL_DEBUG);
        }
    }
}