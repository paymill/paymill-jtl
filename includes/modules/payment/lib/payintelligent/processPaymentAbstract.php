<?php

/**
 * processPaymentAbstract
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
abstract class processPaymentAbstract
{
    protected $_libBase;
    
    protected $_privateKey;
    
    protected $_apiUrl;
    
    private $_clientsObject;
    
    private $_transactionsObject;
    
    private $_paymentsObject;
    
    private $_refundsObject;
    
    private $_newClient = null;
    
    private $_newPayment = null;

    /**
     * Should be Overwritten!
     * This Function should save the Client & Payment-Id for FastCheckouts.
     */
    protected function saveFastCheckoutData($userId, $newClient, $newPayment)
    {
        return true;
    }

    /**
     * Must be Overwritten!
     * This Function must set the Paymill-PrivateKey from the ShopConfiguration.
     * Save Into $this->_privateKey
     */
    abstract protected function setPrivateKey();

    /**
     * Must be Overwritten!
     * This Function must set the Path to the Paymill-Lib.
     * Save Into $this->_libBase
     */
    abstract protected function setLibBase();

    /**
     * Must be Overwritten!
     * This Function must set the Api-URL from the ShopConfiguration.
     * Save Into $this->_apiUrl
     */
    abstract protected function setApiUrl();

    /**
     * Returns the Paymill-PrivateKey
     *
     * @return string
     */
    private function getPrivateKey()
    {
        return $this->_privateKey;
    }

    /**
     * Returns the Path to the Paymill-Lib
     *
     * @return string
     */
    private function getLibBase()
    {
        return $this->_libBase;
    }

    /**
     * Returns the API-URL
     *
     * @return string
     */
    private function getApiUrl()
    {
        return $this->_apiUrl;
    }

    /**
     * Logs the given message and saves it to log.txt
     *
     * @param string $message
     */
    protected function log($message)
    {
        $logfile = dirname(__FILE__) . '/log.txt';
        $privateKey = $this->getPrivateKey();
        if (!empty($privateKey)) {
            $handle = fopen($logfile, 'a');
            fwrite($handle, "[" . date(DATE_RFC822) . "] " . $message . "\n");
            fclose($handle);
        }
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setPrivateKey();
        $this->setLibBase();
        $this->setApiUrl();
        $this->_initiatePhpWrapperClasses();
    }

    /**
     * Load the PhpWrapper-Classes and creates an instance for each class.
     */
    final private function _initiatePhpWrapperClasses()
    {
        require_once $this->getLibBase() . 'Services/Paymill/Transactions.php';
        require_once $this->getLibBase() . 'Services/Paymill/Clients.php';
        require_once $this->getLibBase() . 'Services/Paymill/Payments.php';
        require_once $this->getLibBase() . 'Services/Paymill/Refunds.php';
        
        $this->_clientsObject = new Services_Paymill_Clients(
                $this->getPrivateKey(), $this->getApiUrl()
        );
        
        $this->_transactionsObject = new Services_Paymill_Transactions(
                $this->getPrivateKey(), $this->getApiUrl()
        );
        
        $this->_paymentsObject = new Services_Paymill_Payments(
                $this->getPrivateKey(), $this->getApiUrl()
        );
        
        $this->_refundsObject = new Services_Paymill_Refunds(
                $this->getPrivateKey(), $this->getApiUrl()
        );
    }

    /**
     * @param array params(
     *    [token],               generated Token
     *    [authorizedAmount],    Tokenamount
     *    [amount],              Basketamount
     *    [currency],            Transaction currency
     *    [name],                Customer name
     *    [email],               Customer emailaddress
     *    [description],         Description for transactions
     *    [userId]               UserId used for saving fastCheckoutData(optional)
     *    [clientId],            ClientId used for fastCheckout(optional)
     *    [paymentId]            PaymentId used for fastCheckout(optional)
     * )
     * @return boolean
     */
    final public function processPayment($params)
    {
        try {
            if (!$this->_createClient($params)) {
                return false;
            }
            
            if (!$this->_createPayment($params)) {
                return false;
            }
            
            if (!$this->_createTransaction($params)) {
                return false;
            }
            
            if (!is_null($this->_newClient) && !is_null($this->_newPayment && isset($params['userId']))) {
                $this->saveFastCheckoutData($params['userId'], $this->_newClient, $this->_newPayment);
            }

            if ($params['authorizedAmount'] != $params['amount']) {
                if ($params['authorizedAmount'] > $params['amount']) {
                    // basketamount is lower than the authorized amount
                    $params['amount'] = $params['authorizedAmount'] - $params['amount'];
                    if (!$this->_createRefund($params)) {
                        return false;
                    }
                } else {
                    $params['amount'] = $params['amount'] - $params['authorizedAmount'];
                    if (!$this->_createTransaction($params)) {
                        return false;
                    }
                }
            }
            
            return true;
        } catch (Services_Paymill_Exception $ex) {
            // paymill wrapper threw an exception
            $this->log("Exception thrown from paymill wrapper: " . $ex->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Creates a Paymill-Client with the given Data
     *
     * @param array $params
     * @return boolean
     */
    final private function _createClient(&$params)
    {
        if (array_key_exists('clientId', $params) && !empty($params['clientId'])) {
            $this->log("Client using: " . $params['clientId']);
        } else {
            $client = $this->_clientsObject->create(
                    array(
                        'email' => $params['email'],
                        'description' => $params['description']
                    )
            );
            
            if (!$this->validate($client, 'Client')) {
                return false;
            }
            
            $params['clientId'] = $client['id'];
            $this->_newClient = $client['id'];
        }
        
        return true;
    }

    /**
     * Creates a Paymill-Payment with the given Data
     *
     * @param array $params
     * @return boolean
     */
    final private function _createPayment(&$params)
    {
        if (array_key_exists('paymentId', $params) && !empty($params['paymentId'])) {
            $this->log("Payment using: " . $params['paymentId']);
        } else {
            $payment = $this->_paymentsObject->create(
                    array(
                        'token' => $params['token']
                    )
            );
            
            if (!$this->validate($payment, 'Payment')) {
                return false;
            }
            
            $params['paymentId'] = $payment['id'];
            $this->_newPayment = $payment['id'];
        }
        
        return true;
    }

    /**
     * Creates a Paymill-Transaction with the given Data
     *
     * @param array $params
     * @return boolean
     */
    final private function _createTransaction(&$params)
    {
        $transaction = $this->_transactionsObject->create(
                array(
                    'amount' => $params['amount'],
                    'currency' => $params['currency'],
                    'description' => $params['description'],
                    'payment' => $params['paymentId'],
                    'client' => $params['clientId']
                )
        );
        
        if (!$this->validate($transaction, 'Transaction')) {
            return false;
        }
        
        $params['transactionId'] = $transaction['id'];
        
        return true;
    }

    /**
     * Creates a Paymill-Refund with the given Data
     *
     * @param array $params
     * @return boolean
     */
    final private function _createRefund($params)
    {
        $refund = $this->_refundsObject->create(
                array(
                    'transactionId' => $params['transactionId'],
                    'params' => array(
                        'amount' => $params['amount']
                    )
                )
        );
        
        return $this->validate($refund, 'Refund');
    }

    /**
     * Validates the created Paymill-Objects
     *
     * @param array $transaction
     * @param string $type
     * @return boolean
     */
    final private function validate($transaction, $type)
    {
        if (isset($transaction['data']['response_code']) && $transaction['data']['response_code'] !== 20000) {
            $this->log("An Error occured: " . var_export($transaction, true));
            return false;
        }

        if (!isset($transaction['id']) && !isset($transaction['data']['id'])) {
            $this->log("No $type created: " . var_export($transaction, true));
            return false;
        } else {
            $this->log("$type created: " . $transaction['id']);
        }

        // check result
        if ($type == 'Transaction') {
            if (is_array($transaction) && array_key_exists('status', $transaction)) {
                if ($transaction['status'] == "closed") {
                    // transaction was successfully issued
                    return true;
                } elseif ($transaction['status'] == "open") {
                    // transaction was issued but status is open for any reason
                    $this->log("Status is open.");
                    return false;
                } else {
                    // another error occured
                    $this->log("Unknown error." . var_export($transaction, true));
                    return false;
                }
            } else {
                // another error occured
                $this->log("$type could not be issued.");
                return false;
            }
        } else {
            return true;
        }
    }

}
