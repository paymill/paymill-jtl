<?php

class RequestService
{
 
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