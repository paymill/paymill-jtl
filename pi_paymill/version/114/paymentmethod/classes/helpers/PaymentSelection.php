<?php

require_once('Util.php');
require_once('FastCheckout.php');
require_once(dirname(__FILE__) . '/../lib/Services/Paymill/Payments.php');
require_once(dirname(__FILE__) . '/../payment/Paymill.php');

class PaymentSelection
{
    /**
     * Extends the paymill infos texts with the payment forms
     *
     * @param object $smarty
     * @param object $oPlugin
     * @param string $pluginPath
     */
    public static function setPaymillInfoTexts($smarty, $oPlugin, $pluginPath, $js)
    {
        foreach (self::getPayments($smarty) as $payment) {
            if (Util::isPaymillCc($payment->cName, $oPlugin)) {
                $payment->cHinweisText[$_SESSION['cISOSprache']] .= self::getPaymillPaymentForm('cc', $payment->kZahlungsart, $pluginPath, $oPlugin, $js);
            }

            if(Util::isPaymillElv($payment->cName, $oPlugin)) {
                $payment->cHinweisText[$_SESSION['cISOSprache']] .= self::getPaymillPaymentForm('elv', $payment->kZahlungsart, $pluginPath, $oPlugin, $js);
            }
        }
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
    public static function getPaymillPaymentForm($code, $paymentId, $pluginPath, $oPlugin, $js)
    {
        $methods = array(
            'paymill_cc' => 'cc',
            'paymill_elv' => 'elv'
        );
        
        $amountFloat = $_SESSION["Warenkorb"]->gibGesamtsummeWaren(true) + Util::getDifferentAmount($oPlugin);
        
        $amount = round((float) $amountFloat * 100);
        
        $currency = key($_SESSION["Warenkorb"]->PositionenArr[0]->cGesamtpreisLocalized[0]);
        
        $html = '';
        if ($methods[$_SESSION['pi_error']['method']] == $code) {
            $html = self::getPaymentError($html);
        }
        
        $html .= file_get_contents(dirname(__FILE__) . '/../../template/paymill_' . $code . '.tpl');
        
        if (self::canPamillFastCheckout($code, $oPlugin)) {
            $html = self::setFastCheckoutData($code, $html, $oPlugin);
        } else {
            $toReplace = array('{__cc_brand_logo__}', '{__cc_number__}', '{__cc_cvc__}', '{__cc_holder__}', '{__options_month__}', '{__options_year__}', '{__elv_number__}', '{__elv_bankcode__}', '{__elv_owner__}');
            $replace = array('', '', '', '', self::getMonthOptions(), self::getYearOptions(), '', '', '');
            $html = str_replace($toReplace, $replace, $html);
        }
        
        $html = str_replace('{__paymentId__}', $paymentId, $html);
        $html = str_replace('{__amount__}', $amount, $html);
        $html = str_replace('{__currency__}', $currency, $html);
        $html = str_replace('{__pluginPath__}', $pluginPath, $html);
        $html = str_replace('{__js__}', $js, $html);
        
        if ($oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_show_label'] == 1) {
            $html .= file_get_contents(dirname(__FILE__) . '/../../template/powered_by_' . $code . '.tpl');
        }

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
            
            $replace[0] = '************' . $payment['last4'];;
            $replace[1] = '***';
            $replace[2] = $payment['card_holder'];
            
            $html = str_replace('{__cc_brand_logo__}', '<img src="includes/plugins/pi_paymill/version/112/paymentmethod/img/32x20_' . $payment['card_type'] . '.png" >', $html);
            $html = str_replace('{__options_month__}', self::getMonthOptions($payment['expire_month']), $html);
            $html = str_replace('{__options_year__}', self::getYearOptions($payment['expire_year']), $html);
        }
        
        return str_replace($toReplace, $replace, $html);
    }
    
    private static function setElvFastCheckoutData($data, $html, $fastCheckoutHelper, $oPlugin)
    {
        $paymill = new Paymill();
        
        $toReplace = array('{__elv_number__}', '{__elv_bankcode__}', '{__elv_owner__}');
        $replace = array('', '', '', '', '');
        
        if ($fastCheckoutHelper->hasElvPaymentId($_SESSION['Kunde']->kKunde)) {
            $payments = new Services_Paymill_Payments(
                $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_private_key'],
                $paymill->apiUrl
            );
            
            $payment = $payments->getOne($data->paymentID_ELV);
            
            $replace[0] = $payment['account'];
            $replace[1] = $payment['code'];
            $replace[2] = $payment['holder'];
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
     * retrieve option html tags with the next 10 years from now
     *
     * @return string
     */
    private static function getYearOptions($selected = false)
    {
        $options = '';
        $start = (int) date("Y");
        $end = (int) date("Y") + 10;

        for ($i = $start; $i<=$end; $i++) {
            if ($selected == $i) {
                $options .= '<option selected="selected">' . $i . '</option>';
            } else {
                $options .= '<option>' . $i . '</option>';
            }
        }

        return $options;
    }
    
    private static function getMonthOptions($selected = false)
    {
        for ($i = 1; $i<=12; $i++) {
            if ($selected == $i) {
                $options .= '<option selected="selected">' . $i . '</option>';
            } else {
                $options .= '<option>' . $i . '</option>';
            }
        }
        
        return $options;
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
            'Paymill_Label_Direct_Debit'

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
    
    public static function canPamillFastCheckout($code)
    {
        $fastCheckoutHelper = new FastCheckout();
        if ($code === 'cc') {
            return $fastCheckoutHelper->canCustomerFastCheckoutCc($_SESSION['Kunde']->kKunde);
        } elseif ($code === 'elv') {
            return $fastCheckoutHelper->canCustomerFastCheckoutElv($_SESSION['Kunde']->kKunde);
        }
        
        return false;
    }
}