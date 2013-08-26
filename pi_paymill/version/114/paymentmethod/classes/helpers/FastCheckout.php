<?php

class FastCheckout
{
    public function canCustomerFastCheckoutCc($userId)
    {
        return $this->hasCcPaymentId($userId);
    }
    
    public function canCustomerFastCheckoutElv($userId)
    {
        return $this->hasElvPaymentId($userId);
    }
    
    public function saveCcIds($userId, $newClientId, $newPaymentId)
    {
        $data = $this->loadFastCheckoutData($userId);
        if (!empty($data)) {
            $sql = "UPDATE `xplugin_pi_paymill_tfastcheckout`SET `paymentID_CC` = '$newPaymentId' WHERE `userID` = '$userId'";
        } else {
            $sql = "INSERT INTO `xplugin_pi_paymill_tfastcheckout` (`userID`, `clientID`, `paymentID_CC`) VALUES ('$userId', '$newClientId', '$newPaymentId')";
        }
        
        $GLOBALS['DB']->executeQuery($sql);
    }
    
    public function saveElvIds($userId, $newClientId, $newPaymentId)
    {   
        $data = $this->loadFastCheckoutData($userId);
        if (!empty($data)) {
            $sql = "UPDATE `xplugin_pi_paymill_tfastcheckout`SET `paymentID_ELV` = '$newPaymentId' WHERE `userID` = '$userId'";
        } else {
            $sql = "INSERT INTO `xplugin_pi_paymill_tfastcheckout` (`userID`, `clientID`, `paymentID_ELV`) VALUES ('$userId', '$newClientId', '$newPaymentId')";
        }
        
        $GLOBALS['DB']->executeQuery($sql);
    }
    
    public function loadFastCheckoutData($userId)
    {
        $sql = "SELECT * FROM `xplugin_pi_paymill_tfastcheckout` WHERE `userID` = '$userId'";
        
        return $GLOBALS['DB']->executeQuery($sql, true);
    }
    
    public function hasElvPaymentId($userId)
    {
        $data = $this->loadFastCheckoutData($userId);
        return !empty($data) && !empty($data->paymentID_ELV);
    }
    
    public function hasCcPaymentId($userId)
    {
        $data = $this->loadFastCheckoutData($userId);
        
        return !empty($data) && !empty($data->paymentID_CC);
    }
}