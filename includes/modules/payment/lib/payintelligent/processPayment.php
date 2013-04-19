<?php

require_once 'processPaymentAbstract.php';

/**
 * processPaymentOxid
 *
 * @category   PayIntelligent
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
class processPayment extends processPaymentAbstract
{
    protected $_apiUrl;

    protected $_libBase;
    
    protected $_privateKey;
    
    protected $_paymentObj;
        
    public function __construct(paymill $paymill)
    {
        $this->_paymentObj = $paymill;
        parent::__construct();
    }
    
    protected function setApiUrl()
    {
        $this->_apiUrl = $this->_paymentObj->apiUrl;
    }

    protected function setLibBase()
    {
        $this->_libBase = dirname(dirname(dirname(__FILE__))) . '/lib/paymill/v2/lib/';
    }

    protected function setPrivateKey()
    {
        $this->_privateKey = $this->_paymentObj->privateKey;
    }

    protected function saveFastCheckoutData($userId, $newClient, $newPayment)
    {
        return false;
    }
    
    
    protected function log($message)
    {
        $logfile = dirname(dirname(dirname(__FILE__))) . '/log/log.txt';
        if (file_exists($logfile) && is_writable($logfile)) {
            $handle = fopen($logfile, 'a');
            fwrite($handle, "[" . date(DATE_RFC822) . "] " . $message . "\n");
            fclose($handle);
        } else {
            die('geht nicht Pfad: ' . $logfile);
        }
    }
}
