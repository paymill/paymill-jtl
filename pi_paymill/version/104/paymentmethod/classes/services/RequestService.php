<?php

require_once(dirname(__FILE__) . '/../helpers/Util.php');

class RequestService
{

    public function createClient($params, $apiKey, $endpoint)
    {
        try {
            $clientsObject = new Services_Paymill_Clients($apiKey, $endpoint);
            return $clientsObject->create($params);
        } catch (Services_Paymill_Exception $ex) {
            Util::paymillLog("Exception thrown from paymill wrapper: " . $ex->getMessage());
        }
    }

    public function createPayment($params, $apiKey, $endpoint)
    {
        try {
            $paymentsObject = new Services_Paymill_Payments($apiKey, $endpoint);
            return $paymentsObject->create($params);
        } catch (Services_Paymill_Exception $ex) {
            Util::paymillLog("Exception thrown from paymill wrapper: " . $ex->getMessage());
        }
    }

    /**
     * Create a paymill transaction
     * 
     * @param array $params
     * @param string $apiKey
     * @param string $endpoint url
     * @return array 
     */
    public function createTransaction($params, $apiKey, $endpoint)
    {
        try {
            $transactionsObject = new Services_Paymill_Transactions($apiKey, $endpoint);
            return $transactionsObject->create($params);
        } catch (Services_Paymill_Exception $ex) {
            Util::paymillLog('Exception thrown from paymill wrapper: ' . $ex->getMessage());
        }
    }

}