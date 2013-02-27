<?php

/**
 * Paymill payment plugin
 */
class paymill
{

    var $code, $title, $description = '', $enabled, $privateKey, $apiUrl;

    function pre_confirmation_check()
    {
        global $order, $xtPrice;
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
            $total = $order->info['total'] + $order->info['tax'];
        } else {
            $total = $order->info['total'];
        }

        if ($_SESSION['currency'] == $order->info['currency']) {
            $amount = round($total, $xtPrice->get_decimal_places($order->info['currency']));
        } else {
            $amount = round(
                $xtPrice->xtcCalculateCurrEx($total, $order->info['currency']),
                $xtPrice->get_decimal_places($order->info['currency'])
            );
        }

        $_SESSION['paymill_token'] = $_POST['paymill_token'];
        $_SESSION['pi']['paymill_amount'] = $amount;
    }

    function confirmation()
    {
        return false;
    }

    function process_button()
    {
        return false;
    }

    function before_process()
    {
        return false;
    }

    function get_error()
    {
        global $_GET;
        $error = '';

        if (isset($_GET['error'])) {
            $error = urldecode($_GET['error']);
        }

        switch ($error) {
            case '100':
                $error_text['error'] = utf8_decode(MODULE_PAYMENT_PAYMILL_TEXT_ERROR_100);
                break;
            case '200':
                $error_text['error'] = utf8_decode(MODULE_PAYMENT_PAYMILL_TEXT_ERROR_200);
                break;
        }

        return $error_text;
    }

    function update_status()
    {
        return false;
    }

    function javascript_validation()
    {
        return '';
    }

    function after_process()
    {
        global $order, $insert_id;

        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
            $total = $order->info['total'] + $order->info['tax'];
        } else {
            $total = $order->info['total'];
        }

        // process the payment
        $result = $this->_processPayment(array(
            'libVersion' => 'v2',
            'token' => $_SESSION['paymill_token'],
            'amount' => $_SESSION['pi']['paymill_amount'] * 100,
            'currency' => strtoupper($order->info['currency']),
            'name' => $order->customer['lastname'] . ', ' . $order->customer['firstname'],
            'email' => $order->customer['email_address'],
            'description' => STORE_NAME . ' Bestellnummer: ' . $insert_id,
            'libBase' => dirname(__FILE__) . '/../paymill/lib/v2/lib/',
            'privateKey' => $this->privateKey,
            'apiUrl' => $this->apiUrl,
            'loggerCallback' => array('paymill', 'logAction')
        ));

        if (!$result) {
            xtc_db_query("UPDATE " . TABLE_ORDERS . " SET orders_status = '99' WHERE orders_id = '" . $insert_id . "'");
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'step=step2&payment_error=' . $this->code . '&error=200', 'SSL', true, false));
        }

        unset($_SESSION['pi']);

        return true;
    }

    /**
     * Processes the payment against the paymill API
     * @param $params array The settings array
     * @return boolean
     */
    private function _processPayment($params)
    {
        // setup the logger
        $logger = $params['loggerCallback'];

        // setup client params
        $clientParams = array(
            'email' => $params['email'],
            'description' => $params['name']
        );

        // setup credit card params
        $paymentParams = array(
            'token' => $params['token']
        );

        // setup transaction params
        $transactionParams = array(
            'amount' => $params['amount'],
            'currency' => $params['currency'],
            'description' => $params['description']
        );

        require_once $params['libBase'] . 'Services/Paymill/Transactions.php';
        require_once $params['libBase'] . 'Services/Paymill/Clients.php';
        require_once $params['libBase'] . 'Services/Paymill/Payments.php';

        $clientsObject = new Services_Paymill_Clients(
                        $params['privateKey'], $params['apiUrl']
        );
        $transactionsObject = new Services_Paymill_Transactions(
                        $params['privateKey'], $params['apiUrl']
        );
        $paymentsObject = new Services_Paymill_Payments(
                        $params['privateKey'], $params['apiUrl']
        );

        // perform conection to the Paymill API and trigger the payment
        try {

            $allParams = array(
                'clientParams' => $clientParams,
                'paymentParams' => $paymentParams,
                'transactionParams' => $transactionParams
            );

            call_user_func_array($logger, array("Try to issue new transaction with: " . var_export($allParams, true)));

            $client = $clientsObject->create($clientParams);
            if (!isset($client['id'])) {
                call_user_func_array($logger, array("No client created" . var_export($client, true)));
                return false;
            } else {
                call_user_func_array($logger, array("Client created: " . $client['id']));
            }

            // create card
            $paymentParams['client'] = $client['id'];
            $payment = $paymentsObject->create($paymentParams);
            if (!isset($payment['id'])) {
                call_user_func_array($logger, array("No payment (credit card) created: " . var_export($payment, true) . " with params " . var_export($paymentParams, true)));
                return false;
            } else {
                call_user_func_array($logger, array("Payment (credit card) created: " . $payment['id']));
            }

            // create transaction
            //$transactionParams['client'] = $client['id'];
            $transactionParams['payment'] = $payment['id'];
            $transaction = $transactionsObject->create($transactionParams);
            if(isset($transaction['data']['response_code'])){
                call_user_func_array($logger, array("An Error occured: " . var_export($transaction, true)));
                return false;
            }

            if (!isset($transaction['id'])) {
                call_user_func_array($logger, array("No transaction created" . var_export($transaction, true)));
                return false;
            } else {
                call_user_func_array($logger, array("Transaction created: " . $transaction['id']));
            }

            // check result
            if (is_array($transaction) && array_key_exists('status', $transaction)) {
                if ($transaction['status'] == "closed") {
                    // transaction was successfully issued
                    return true;
                } elseif ($transaction['status'] == "open") {
                    // transaction was issued but status is open for any reason
                    call_user_func_array($logger, array("Status is open."));
                    return false;
                } else {
                    // another error occured
                    call_user_func_array($logger, array("Unknown error." . var_export($transaction, true)));
                    return false;
                }
            } else {
                // another error occured
                call_user_func_array($logger, array("Transaction could not be issued."));
                return false;
            }
        } catch (Services_Paymill_Exception $ex) {
            // paymill wrapper threw an exception
            call_user_func_array($logger, array("Exception thrown from paymill wrapper: " . $ex->getMessage()));
            return false;
        }

        return true;
    }

    public function logAction($message)
    {
        $logfile = dirname(__FILE__) . '/paymill/log.txt';
        if (file_exists($logfile) && is_writable($logfile)) {
            $handle = fopen($logfile, 'a');
            fwrite($handle, "[" . date(DATE_RFC822) . "] " . $message . "\n");
            fclose($handle);
        }
    }

}
