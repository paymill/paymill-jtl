<?php

require_once(dirname(__FILE__) . '/../helpers/Util.php');
require_once(dirname(__FILE__) . '/../services/RequestService.php');
require_once(dirname(__FILE__) . '/../lib/Services/Paymill/Transactions.php');
require_once(PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php');

class Paymill extends PaymentMethod 
{
    /**
     * Send transaction to paymill validate result and save order or handle errors
     * 
     * @global object $oPlugin
     * @param object $order 
     */
    public function preparePaymentProcess(&$order) 
    {
        global $oPlugin;
        $requestService = new RequestService();
        if (array_key_exists('pi', $_SESSION) && array_key_exists('paymillToken', $_SESSION['pi'])) {
            $params = Util::getCreateTransactionParams($order, $_SESSION['pi']['paymillToken']);
            
            $endpoint = $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_api_endpoint'];
            
            $transaction = $requestService->createTransaction(
                    $params, $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_private_key'], $endpoint
            );

            if (!array_key_exists('error', $transaction)) {
                if ($this->finalizeOrder($order)) {
                    unset($_SESSION['pi']);
                } else { 
                    $_SESSION['pi_error']['error'] = $oPlugin->oPluginSprachvariableAssoc_arr['Order_Generate_Error'];
                    header("Location: " . gibShopURL() . '/bestellvorgang.php?editZahlungsart=1');
                }
            } else {
                $_SESSION['pi_error']['error'] = $oPlugin->oPluginSprachvariableAssoc_arr['Payment_Processing_Error'] . $transaction['error'];
                header("Location: " . gibShopURL() . '/bestellvorgang.php?editZahlungsart=1');
            }
        } else {
            $_SESSION['pi_error']['error'] = $oPlugin->oPluginSprachvariableAssoc_arr['Invalid_Token_Error'];
            header("Location: " . gibShopURL() . '/bestellvorgang.php?editZahlungsart=1');
        }
    }
    
    /**
     * Finalizes order if everything is ok
     *
     * @param  Object    $order         Current order
     * @param  Object    $hash          Current order hash
     * @param  Object    $args          response arguments
     * @return Bool
     */
    function finalizeOrder($order, $hash, $args)
    {
        parent::finalizeOrder($order, $hash, $args);
        $order->cBestellNr = baueBestellnummer();
        $order = finalisiereBestellung($order->cBestellNr);
        $incomingPayment = new stdClass();
        $this->addIncomingPayment($order, $incomingPayment);
        if (bestellungKomplett()) {
            raeumeSessionAufNachBestellung();
            return true;
        }
        
        return false;
    }
}