<?php

require_once(dirname(__FILE__) . '/../helpers/Util.php');
require_once(dirname(__FILE__) . '/../services/RequestService.php');
require_once(dirname(__FILE__) . '/../v2/lib/Services/Paymill/Transactions.php');
require_once(dirname(__FILE__) . '/../v2/lib/Services/Paymill/Clients.php');
require_once(dirname(__FILE__) . '/../v2/lib/Services/Paymill/Payments.php');
require_once(dirname(__FILE__) . '/../v2/lib/Services/Paymill/Refunds.php');
require_once(PFAD_ROOT . PFAD_INCLUDES_MODULES . 'PaymentMethod.class.php');

class Paymill extends PaymentMethod
{

    function init($moduleID)
    {
        parent::init($moduleID);
        $this->name = 'PayMILL';
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
        $requestService = new RequestService();
        if (array_key_exists('pi', $_SESSION) && array_key_exists('paymillToken', $_SESSION['pi'])) {
            $endpoint = $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_api_endpoint'];

            $client = $requestService->createClient(
                    Util::getCreateClientParams($order), $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_private_key'], $endpoint
            );

            if (!isset($client['id'])) {
                Util::paymillLog('No client created: ' . var_export($client, true));
                return false;
            } else {
                Util::paymillLog('Client created: ' . $client['id']);
            }

            $paymentParams = Util::getCreatePaymentParams($_SESSION['pi']['paymillToken'], $client);
            $payment = $requestService->createPayment(
                    $paymentParams, $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_private_key'], $endpoint
            );

            if (!isset($payment['id'])) {
                Util::paymillLog('No payment (' . $this->name . ') created: ' . var_export($payment, true) . " with params " . var_export($paymentParams, true));
                return false;
            } else {
                Util::paymillLog('Payment (' . $this->name . ') created: ' . $payment['id']);
            }

            $transaction = $requestService->createTransaction(
                    Util::getCreateTransactionParams($order, $payment), $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_private_key'], $endpoint
            );
            if (isset($transaction['data']['response_code'])) {
                Util::paymillLog("An Error occured: " . var_export($transaction, true));
                return false;
            }
            if (!isset($transaction['id'])) {
                Util::paymillLog('No transaction created' . var_export($transaction, true));
                return false;
            } else {
                Util::paymillLog('Transaction created: ' . $transaction['id']);
            }

            if ($_SESSION['PigmbhPaymill']['authorizedAmount'] != $amount) {
                if ($_SESSION['PigmbhPaymill']['authorizedAmount'] > $amount) {
                    // basketamount is lower than the authorized amount
                    $refundParams = array(
                        'transactionId' => $transaction['id'],
                        'params' => array(
                            'amount' => $_SESSION['PigmbhPaymill']['authorizedAmount'] - $amount
                        )
                    );
                    $refund = $requestService->createRefund(
                            $refundParams, $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_private_key'], $endpoint
                    );

                    if (isset($refund['data']['response_code']) && $refund['data']['response_code'] !== 20000) {
                        Util::paymillLog("An Error occured: " . var_export($refund, true));
                        return false;
                    }
                    if (!isset($refund['data']['id'])) {
                        Util::paymillLog('No Refund created' . var_export($refund, true));
                        return false;
                    } else {
                        Util::paymillLog('Refund created: ' . $transaction['id']);
                    }
                } else {
                    // basketamount is higher than the authorized amount (paymentfee etc.)
                    $secoundTransactionParams = array(
                        'amount' => $amount - $_SESSION['PigmbhPaymill']['authorizedAmount'],
                        'currency' => $order->Waehrung->cISO,
                        'description' => $Einstellungen['global']['global_shopname'] . 'Bestellnummer: ' . baueBestellnummer() . ', ' . $order->oRechnungsadresse->cMail,
                        'client' => $client['id'],
                        'payment' => $payment['id']
                    );

                    $transaction = $requestService->createTransaction(
                            $secoundTransactionParams, $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_private_key'], $endpoint
                    );
                    if (isset($transaction['data']['response_code'])) {
                        Util::paymillLog("An Error occured: " . var_export($transaction, true));
                        return false;
                    }
                    if (!isset($transaction['id'])) {
                        Util::paymillLog('No transaction created' . var_export($transaction, true));
                        return false;
                    } else {
                        Util::paymillLog('Transaction created: ' . $transaction['id']);
                    }
                }
            }

            if ($this->finalizeOrder($order)) {
                unset($_SESSION['pi']);
                unset($_SESSION['PigmbhPaymill']);
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

}