<?php

class PaymentCheck
{
    /**
     * Check if token exist
     * 
     * @param string $token
     * @return boolean 
     */
    public static function checkToken($token)
    {
        return !empty($token);
    }
    
    /**
     * Retrieve payment from session
     * 
     * @return object 
     */
    public static function getPayment()
    {
        return $_SESSION['Zahlungsart'];
    }
    
    /**
     * Retrieve token from session
     * 
     * @param string $token 
     */
    public static function setToken($token)
    {
        $_SESSION['pi']['paymillToken'] = $token;
    }
}