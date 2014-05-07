<?php

require_once('Util.php');
require_once('FastCheckout.php');
require_once(dirname(__FILE__) . '/../lib/Services/Paymill/Payments.php');
require_once(dirname(__FILE__) . '/../payment/Paymill.php');

class PaymentSelection
{
    public static function getCcPaymentForm($pluginPath, $oPlugin)
    {
        return self::getPaymillPaymentForm('cc', $pluginPath, $oPlugin);
    }
    
    public static function getElvPaymentForm($pluginPath, $oPlugin)
    {
        return self::getPaymillPaymentForm('elv', $pluginPath, $oPlugin);
    }

    /**
     * Retrieve payment method smarty entry
     *
     * @param object $smarty
     * @return object
     */
    public static function getPayments($smarty)
    {
        return $smarty->_tpl_vars['Zahlungsarten'];
    }

    /**
     * Retrieve the html from the template files and replace the placeholders
     *
     * @param string $code
     * @param string $paymentId
     * @param string $pluginPath
     * @param object $oPlugin
     * @return string
     */
    public static function getPaymillPaymentForm($code, $pluginPath, $oPlugin)
    {
        $methods = array(
            'paymill_cc' => 'cc',
            'paymill_elv' => 'elv'
        );
        
        $amountFloat = $_SESSION["Warenkorb"]->gibGesamtsummeWaren(true) * $_SESSION['Waehrung']->fFaktor;
        
        $amount = round((float) $amountFloat * 100);
        
        $currency = key($_SESSION["Warenkorb"]->PositionenArr[0]->cGesamtpreisLocalized[0]);
        
        $html = '';
        if ($methods[$_SESSION['pi_error']['method']] == $code) {
            $html = self::getPaymentError($html);
        }
        
        if ($code === 'cc') {
            $html .= file_get_contents(dirname(dirname(dirname(__FILE__))) . '/template/paymill_' . $code . '.tpl');
        } else {
            if ($oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_sepa']) {
                $html .= file_get_contents(dirname(__FILE__) . '/../../template/paymill_' . $code . '_sepa.tpl');
            } else {
                $html .= file_get_contents(dirname(__FILE__) . '/../../template/paymill_' . $code . '_normal.tpl');
            }
        }
        
        if (self::canPamillFastCheckout($code, $oPlugin)) {
            $html = self::setFastCheckoutData($code, $html, $oPlugin);
        } else {
            $toReplace = array('{__cc_brand_logo__}', '{__cc_number__}', '{__cc_cvc__}', '{__cc_holder__}', '{__cc_expiry__}', '{__elv_number__}', '{__elv_bankcode__}', '{__elv_owner__}', '{__elv_iban__}', '{__elv_bic__}');
            $replace = array('', '', '', '', '', '', '', '', '', '');
            $html = str_replace($toReplace, $replace, $html);
        }
        
        $html = str_replace('{__amount__}', $amount, $html);
        $html = str_replace('{__currency__}', $currency, $html);
        $html = str_replace('{__pluginPath__}', $pluginPath, $html);

        if ($code == 'cc') {
            $html = self::addCcMultiLang($html, $oPlugin);
        } else {
            $html = self::addElvMultiLang($html, $oPlugin);
        }

        return $html;
    }
    
    private static function setFastCheckoutData($code, $html, $oPlugin)
    {
        $fastCheckoutHelper = new FastCheckout();
        $data = $fastCheckoutHelper->loadFastCheckoutData($_SESSION['Kunde']->kKunde);
        
        if ($code === 'cc') {
            $html = self::setCcFastCheckoutData($data, $html, $fastCheckoutHelper, $oPlugin);
        }
        
        if ($code === 'elv') {
            $html = self::setElvFastCheckoutData($data, $html, $fastCheckoutHelper, $oPlugin);
        }
        
        return $html;
    }
    
    private static function setCcFastCheckoutData($data, $html, $fastCheckoutHelper, $oPlugin)
    {
        $paymill = new Paymill();
        
        $toReplace = array('{__cc_number__}', '{__cc_cvc__}', '{__cc_holder__}');
        $replace = array('', '', '', '', '');
        
        if ($fastCheckoutHelper->hasCcPaymentId($_SESSION['Kunde']->kKunde)) {
            $payments = new Services_Paymill_Payments(
                $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_private_key'],
                $paymill->apiUrl
            );
            
            $payment = $payments->getOne($data->paymentID_CC);
            
            if (array_key_exists('last4', $payment)) {
                $replace[0] = '************' . $payment['last4'];
                $replace[1] = '***';
                $replace[2] = $payment['card_holder'];
                $html = str_replace('{__cc_expiry__}', $payment['expire_month'] . '/' .  $payment['expire_year'], $html);
                $brand = $payment['card_type'];
                if ($payment['card_type'] === 'american express') {
                    $brand = 'amex';
                }
                $html = str_replace('{__brand__}', 'paymill-card-number-' . $brand, $html);
            }
        }
        
        return str_replace($toReplace, $replace, $html);
    }
    
    private static function setElvFastCheckoutData($data, $html, $fastCheckoutHelper, $oPlugin)
    {
        $paymill = new Paymill();
        
        if (!$oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_sepa']) {
            $toReplace = array('{__elv_number__}', '{__elv_bankcode__}', '{__elv_owner__}');
            $replace = array('', '', '');
        } else {
            $toReplace = array('{__elv_iban__}', '{__elv_bic__}', '{__elv_owner__}');
            $replace = array('', '', '');
        }
        
        if ($fastCheckoutHelper->hasElvPaymentId($_SESSION['Kunde']->kKunde)) {
            $payments = new Services_Paymill_Payments(
                $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_private_key'],
                $paymill->apiUrl
            );
            
            $payment = $payments->getOne($data->paymentID_ELV);
            
            if (!$oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_sepa']) {
                if (array_key_exists('account', $payment)) {
                    $replace[0] = $payment['account'];
                    $replace[1] = $payment['code'];
                    $replace[2] = $payment['holder'];
                }
            } else {
                if (array_key_exists('iban', $payment)) {
                    $replace[0] = $payment['iban'];
                    $replace[1] = $payment['bic'];
                    $replace[2] = $payment['holder'];
                }
            }
        }
        
        return str_replace($toReplace, $replace, $html);
    }
    
    private static function getPaymentError($html)
    {
        if (array_key_exists('pi_error', $_SESSION) && array_key_exists('error', $_SESSION['pi_error'])) {
            $html .= '<div class="payment-error payment-error-checkout">' . $_SESSION['pi_error']['error'] . '</div>';
            unset($_SESSION['pi_error']);
        }
        
        return $html;
    }

    /**
     * Add the lang placeholders for credit card
     *
     * @param string $html
     * @param object $oPlugin
     * @return string
     */
    private static function addCcMultiLang($html, $oPlugin)
    {
        $entrys = $oPlugin->oPluginSprachvariableAssoc_arr;
        $placeholders = array(
            '___Credit_Card_Number___',
            '___Card_Verification_Number___',
            '___Expiration_Date___',
            '___Credit_Card_Holder___',
            '__CVC_TOOLTIP__',
            'Paymill_Label_Credit_Card'

        );

        return self::replace($placeholders, $entrys, $html);
    }

    /**
     * Add the lang placeholders for direct debit
     *
     * @param string $html
     * @param object $oPlugin
     * @return string
     */
    private static function addElvMultiLang($html, $oPlugin)
    {
        $entrys = $oPlugin->oPluginSprachvariableAssoc_arr;
        $placeholders = array(
            '___Account_Owner___',
            '___Account_Number___',
            '___Sort_Code___',
            'Paymill_Label_Direct_Debit',
            '___IBAN___',
            '___BIC___'

        );

        return self::replace($placeholders, $entrys, $html);
    }

    /**
     * Replace all given lang placeholders
     *
     * @param array $placeholders
     * @param array $entrys
     * @param string $html
     * @return string
     */
    private static function replace($placeholders, $entrys , $html)
    {
        foreach ($placeholders as $placeholder) {
            $html = str_replace($placeholder, $entrys[$placeholder], $html);
        }

        return $html;
    }
    
    public static function canPamillFastCheckout($code, $oPlugin)
    {
        $paymill  = new Paymill();
        $payments = new Services_Paymill_Payments(
            $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_private_key'],
            $paymill->apiUrl
        );
        
        $fastCheckoutHelper = new FastCheckout();
        $data = $fastCheckoutHelper->loadFastCheckoutData($_SESSION['Kunde']->kKunde);
        
        if ($code === 'cc' && $fastCheckoutHelper->canCustomerFastCheckoutCc($_SESSION['Kunde']->kKunde)) {            
            $payment = $payments->getOne($data->paymentID_CC);
            return array_key_exists('last4', $payment);
        } elseif ($code === 'elv' && $fastCheckoutHelper->canCustomerFastCheckoutElv($_SESSION['Kunde']->kKunde)) {
            $payment = $payments->getOne($data->paymentID_ELV);
            return array_key_exists('account', $payment);
        }
        
        return false;
    }
}