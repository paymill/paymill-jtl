<?php

require_once(dirname(__FILE__) . '/../helpers/Util.php');
require_once(dirname(__FILE__) . '/../helpers/FastCheckout.php');
require_once(dirname(__FILE__) . '/../lib/Services/Paymill/PaymentProcessor.php');
require_once(dirname(__FILE__) . '/../lib/Services/Paymill/LoggingInterface.php');
require_once(dirname(__FILE__) . '/../lib/Services/Paymill/Clients.php');
require_once(PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php');

class Paymill extends PaymentMethod implements Services_Paymill_LoggingInterface
{

    /**
     * Api endpoint
     * @var string
     */
    public $apiUrl = 'https://api.paymill.com/v2/';

    /**
     * FastCheckout helper
     * @var \FastCheckout
     */
    private $_fastCheckout;

    /**
     * OrderId
     * @var string
     */
    private $_orderId;

    /**
     * Module name
     * @var string
     */
    public $name;

    /**
     * Initialize payment object
     * 
     * @param string $moduleID
     */
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
    public function preparePaymentProcess($order)
    {
        global $oPlugin, $Einstellungen;

        if (array_key_exists('pi', $_SESSION) && array_key_exists('paymillToken', $_SESSION['pi'])) {

            $this->_orderId = baueBestellnummer();

            $amount = (float) $order->fGesamtsumme;
            $paymill = new Services_Paymill_PaymentProcessor();
            $paymill->setAmount((int) (string) ($amount * 100));
            $paymill->setApiUrl((string) $this->apiUrl);
            $paymill->setCurrency((string) strtoupper($order->Waehrung->cISO));
            $paymill->setDescription((string) ($Einstellungen['global']['global_shopname'] . ' Bestellnummer: ' . $this->_orderId));
            $paymill->setEmail((string) $order->oRechnungsadresse->cMail);
            $paymill->setName((string) ($order->oRechnungsadresse->cNachname . ', ' . $order->oRechnungsadresse->cVorname));
            $paymill->setPrivateKey(trim((string) $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_private_key']));
            $paymill->setToken((string) $_SESSION['pi']['paymillToken']);
            $paymill->setLogger($this);
            $paymill->setSource($oPlugin->nVersion . '_JTL_' . JTL_VERSION);

            if (array_key_exists('authorized_amount', $_SESSION['pi'])) {
                $paymill->setPreAuthAmount($_SESSION['pi']['authorized_amount']);
            }

            $data = $this->_fastCheckout->loadFastCheckoutData($order->oRechnungsadresse->kKunde);
            if (!empty($data->clientID)) {
                $clientId = $this->_getUpdatedClientId($data, $order);
                $paymill->setClientId($clientId);
            }

            if ($_SESSION['pi']['paymillToken'] === 'dummyToken') {
                if ($this->_fastCheckout->canCustomerFastCheckoutCc($order->oRechnungsadresse->kKunde) && $order->Zahlungsart->cName == 'paymill_cc') {
                    $data = $this->_fastCheckout->loadFastCheckoutData($order->oRechnungsadresse->kKunde);
                    if (!empty($data->paymentID_CC)) {
                        $paymill->setPaymentId($data->paymentID_CC);
                    }
                }

                if ($this->_fastCheckout->canCustomerFastCheckoutElv($order->oRechnungsadresse->kKunde) && $order->Zahlungsart->cName == 'paymill_elv') {
                    $data = $this->_fastCheckout->loadFastCheckoutData($order->oRechnungsadresse->kKunde);
                    if ($data->paymentID_ELV) {
                        $paymill->setPaymentId($data->paymentID_ELV);
                    }
                }
            }

            $result = $paymill->processPayment();

            $_SESSION['pi_error']['method'] = $order->Zahlungsart->cName;

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
                    unset($_SESSION['pi_error']);
                } else {
                    unset($_SESSION['pi']);
                    $_SESSION['pi_error']['error'] = $oPlugin->oPluginSprachvariableAssoc_arr['Order_Generate_Error'];
                    header("Location: " . gibShopURL() . '/bestellvorgang.php?editZahlungsart=1');
                }
            } else {
                unset($_SESSION['pi']);
                $_SESSION['pi_error']['error'] = $this->_getErrorMessage($oPlugin, $paymill->getErrorCode());
                header("Location: " . gibShopURL() . '/bestellvorgang.php?editZahlungsart=1');
            }
        } else {
            unset($_SESSION['pi']);
            $_SESSION['pi_error']['error'] = $oPlugin->oPluginSprachvariableAssoc_arr['Invalid_Token_Error'];
            header("Location: " . gibShopURL() . '/bestellvorgang.php?editZahlungsart=1');
        }
    }

    private function _getErrorMessage($oPlugin, $code)
    {
        if (array_key_exists('PAYMILL_' . $code, $oPlugin->oPluginSprachvariableAssoc_arr)) {
            return $oPlugin->oPluginSprachvariableAssoc_arr['PAYMILL_' . $code];
        } else {
            return $oPlugin->oPluginSprachvariableAssoc_arr['Order_Generate_Error'];
        }
    }

    private function _getUpdatedClientId($data, $order)
    {
        global $oPlugin;
        $clients = new Services_Paymill_Clients(
                trim((string) $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_private_key']), (string) $this->apiUrl
        );

        $client = $clients->getOne($data->clientID);
        if ($client['email'] !== $order->oRechnungsadresse->cMail) {
            $clients->update(
                    array(
                        'id' => $data->clientID,
                        'email' => $order->oRechnungsadresse->cMail
                    )
            );
        }

        return $client['id'];
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

        $order->cBestellNr = $this->_orderId;
        $order = finalisiereBestellung($this->_orderId);


        $incomingPayment = new stdClass();
        $incomingPayment->fBetrag = $order->fGesamtsummeKundenwaehrung;
        $incomingPayment->cISO = $order->Waehrung->cISO;
        $incomingPayment->cZahlungsanbieter = $this->name;
        $this->addIncomingPayment($order, $incomingPayment);
        if (bestellungKomplett()) {
            raeumeSessionAufNachBestellung();
            return true;
        }

        return true;
    }

    /**
     * Paymill log wrapper
     * 
     * @param string $message
     * @param string $debugInfo
     */
    public function log($message, $debugInfo)
    {
        Util::paymillLog($message . $debugInfo);
    }

}