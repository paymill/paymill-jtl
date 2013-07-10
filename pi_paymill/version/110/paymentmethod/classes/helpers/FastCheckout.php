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
            $sql = "UPDATE `paymill_fastcheckout`SET `paymentID_CC` = '$newPaymentId' WHERE `userID` = '$userId'";
        } else {
            $sql = "INSERT INTO `paymill_fastcheckout` (`userID`, `clientID`, `paymentID_CC`) VALUES ('$userId', '$newClientId', '$newPaymentId')";
        }
        
        $GLOBALS['DB']->executeQuery($sql);
    }
    
    public function saveElvIds($userId, $newClientId, $newPaymentId)
    {   
        $data = $this->loadFastCheckoutData($userId);
        if (!empty($data)) {
            $sql = "UPDATE `paymill_fastcheckout`SET `paymentID_ELV` = '$newPaymentId' WHERE `userID` = '$userId'";
        } else {
            $sql = "INSERT INTO `paymill_fastcheckout` (`userID`, `clientID`, `paymentID_ELV`) VALUES ('$userId', '$newClientId', '$newPaymentId')";
        }
        
        $GLOBALS['DB']->executeQuery($sql);
    }
    
    public function loadFastCheckoutData($userId)
    {
        $sql = "SELECT * FROM `paymill_fastcheckout` WHERE `userID` = '$userId'";
        
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