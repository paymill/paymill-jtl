<?php

require_once('Util.php');

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
        $amount = round((float) $_SESSION["Warenkorb"]->gibGesamtsummeWaren(true) * 100);
        $_SESSION['PigmbhPaymill']['authorizedAmount'] = $amount;
        $currency = key($_SESSION["Warenkorb"]->PositionenArr[0]->cGesamtpreisLocalized[0]);
        if (self::canPamillFastCheckout($code, $oPlugin)) {
            $html = file_get_contents(dirname(__FILE__) . '/../../template/paymill_' . $code . '.tpl');
        }
        $html = str_replace('{__paymentId__}', $paymentId, $html);
        $html = str_replace('{__amount__}', $amount, $html);
        $html = str_replace('{__currency__}', $currency, $html);
        $html = str_replace('{__pluginPath__}', $pluginPath, $html);
        $html = str_replace('{__options__}', self::getYearOptions(), $html);
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

    /**
     * retrieve option html tags with the next 10 years from now
     *
     * @return string
     */
    private static function getYearOptions()
    {
        $options = '';
        $start = (int) date("Y");
        $end = (int) date("Y") + 10;

        for ($i = $start; $i<=$end; $i++) {
            $options .= '<option>' . $i . '</option>';
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
    
    public static function canPamillFastCheckout($code, $oPlugin)
    {
        if ($code === 'cc') {
            return self::canPaymillCcFastCheckout($oPlugin);
        } elseif ($code === 'elv') {
            return self::canPaymillElvFastCheckout($oPlugin);
        }
        
        return false;
    }
    
    /**
     * Is fast checkout for paymill cc available
     * 
     * @return boolean
     */
    public static function canPaymillCcFastCheckout($oPlugin)
    {
        print_r($_SESSION['Kunde']->kKunde);
        exit;
        return $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_fast_checkout'] && false;
    }
    
    /**
     * Is fast checkout for paymill elv available
     * 
     * @return boolean
     */
    public static function canPaymillElvFastCheckout($oPlugin)
    {
        return $oPlugin->oPluginEinstellungAssoc_arr['pi_paymill_fast_checkout'] && false;
    }
}