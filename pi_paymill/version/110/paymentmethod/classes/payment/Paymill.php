<?php

require_once(dirname(__FILE__) . '/../helpers/Util.php');
require_once(dirname(__FILE__) . '/../helpers/FastCheckout.php');
require_once(dirname(__FILE__) . '/../lib/Services/Paymill/PaymentProcessor.php');
require_once(dirname(__FILE__) . '/../lib/Services/Paymill/LoggingInterface.php');
require_once(PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php');

class Paymill extends PaymentMethod implements Services_Paymill_LoggingInterface
{

    private $_apiUrl = 'https://api.paymill.com/v2/';
    
    /**
     *
     * @var \FastCheckout
     */
    private $_fastCheckout;


    function init($moduleID)
    {
        parent::init($moduleID);
        $this->name = 'PayMILL';
        $this->_fastCheckout = new FastCheckout();
        
    }

    /**
     * Send transaction to paymill validate result and save order or handle errors
     *
     * @global object $oPlugin
     * @param object $order
     */
    public function preparePaymentProcess(&$order)
    {
        global $oPlugin, $Einstellungen;
        
        if (array_key_exists('pi', $_SESSION) && array_key_exists('paymillToken', $_SESSION['pi'])) {
            $amount = (float) $order->fGesamtsummeKundenwaehrung;
            $paymill = new Services_Paymill_PaymentProcessor();
            $paymill->setAmount((int)(string) ($amount * 100));
            $paymill->setApiUrl((string) $this->_apiUrl);
            $paymill->setCurrency((string) strtoupper($order->Waehrung->cISO));
            $paymill->setDescription((string) ($Einstellungen['global']['global_shopname'] . 'Bestellnummer: ' . baueBestellnummer()));
            $paymill->setEmail((string)  $order->oRechnungsadresse->cMail);
            $paymill->setName((string) ($order->oRechnungsadresse->cNachname . ', ' . $order->oRechnungsadresse->cVorname));
            $paymill->setPrivateKey((string) $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_private_key']);
            $paymill->setToken((string) $_SESSION['pi']['paymillToken']);
            $paymill->setLogger($this);
            $paymill->setSource($oPlugin->nVersion . '_JTL_' . JTL_VERSION);

            
            if ($this->_fastCheckout->canCustomerFastCheckoutCc($order->oRechnungsadresse->kKunde) && $order->Zahlungsart->cName == 'paymill_cc') {
                $data = $this->_fastCheckout->loadFastCheckoutData($order->oRechnungsadresse->kKunde);
                $paymill->setClientId($data->clientID);
                if (!empty($data->paymentID_CC)) {
                    $paymill->setPaymentId($data->paymentID_CC);
                }
            }
            
            if ($this->_fastCheckout->canCustomerFastCheckoutElv($order->oRechnungsadresse->kKunde) && $order->Zahlungsart->cName == 'paymill_elv') {
                $data = $this->_fastCheckout->loadFastCheckoutData($order->oRechnungsadresse->kKunde);
                $paymill->setClientId($data->clientID);
                if ($data->paymentID_ELV) {
                    $paymill->setPaymentId($data->paymentID_ELV);
                }
            }
            
            $result = $paymill->processPayment();
            
            if ($result) {
                if ($this->finalizeOrder($order)) {
                    if ((boolean) $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_fast_checkout']) {
                        
                        if ($order->Zahlungsart->cName == 'paymill_cc') {
                            $this->_fastCheckout->saveCcIds($order->oRechnungsadresse->kKunde, $paymill->getClientId(), $paymill->getPaymentId());
                        }
                        
                        if ($order->Zahlungsart->cName == 'paymill_elv') {
                            $this->_fastCheckout->saveElvIds($order->oRechnungsadresse->kKunde, $paymill->getClientId(), $paymill->getPaymentId());
                        }
                    }
                    
                    unset($_SESSION['pi']);
                    unset($_SESSION['PigmbhPaymill']);
                } else {
                    $_SESSION['pi_error']['error'] = $oPlugin->oPluginSprachvariableAssoc_arr['Order_Generate_Error'];
                    header("Location: " . gibShopURL() . '/bestellvorgang.php?editZahlungsart=1');
                }
            } else {
                $_SESSION['pi_error']['error'] = $oPlugin->oPluginSprachvariableAssoc_arr['Order_Generate_Error'];
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
        $incomingPayment->fBetrag = $order->fGesamtsummeKundenwaehrung;
        $incomingPayment->cISO = $order->Waehrung->cISO;
        $incomingPayment->cZahlungsanbieter = $this->name;
        $this->addIncomingPayment($order, $incomingPayment);
        if (bestellungKomplett()) {
            raeumeSessionAufNachBestellung();
            return true;
        }

        return false;
    }
    
    public function log($message, $debugInfo)
    {
        Util::paymillLog($message . $debugInfo);
    }

}