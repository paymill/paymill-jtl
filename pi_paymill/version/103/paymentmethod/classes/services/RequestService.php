<?php

class RequestService
{   
    public function createClient($params, $apiKey, $endpoint)
    {
        $clientsObject = new Services_Paymill_Clients(
            $apiKey, $endpoint
        );
        
        return $clientsObject->create($params);
    }
    
    public function createPayment($params, $apiKey, $endpoint)
    {
        $paymentsObject = new Services_Paymill_Payments(
            $apiKey, $endpoint
        );
        
        return $paymentsObject->create($params);
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
        $transactionsObject = new Services_Paymill_Transactions(
            $apiKey, $endpoint
        );
        
        return $transactionsObject->create($params);
    }
}