<?php

require_once('abstract/paymill.php');

class paymill_cc extends paymill
{
    function paymill_cc()
    {
        $this->code = 'paymill_cc';
        $this->version = '1.0.3';
        $this->title = 'Kreditkartenzahlung';
        $this->public_title = 'Kreditkartenzahlung';
        $this->sort_order = MODULE_PAYMENT_PAYMILL_CC_SORT_ORDER;
        $this->enabled = ((MODULE_PAYMENT_PAYMILL_CC_STATUS == 'True') ? true : false);
        $this->privateKey = MODULE_PAYMENT_PAYMILL_CC_PRIVATEKEY;
        $this->apiUrl = MODULE_PAYMENT_PAYMILL_CC_API_URL;
    }

    function selection()
    {
        global $order, $xtPrice;

        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
            $total = $order->info['total'] + $order->info['tax'];
        } else {
            $total = $order->info['total'];
        }

        if ($_SESSION['currency'] == $order->info['currency']) {
            $amount = round($total, $xtPrice->get_decimal_places($order->info['currency']));
        } else {
            $amount = round($xtPrice->xtcCalculateCurrEx($total, $order->info['currency']), $xtPrice->get_decimal_places($order->info['currency']));
        }

        $amount = $amount + $this->getShippingTaxAmount($order);
        
        for ($i = 1; $i < 13; $i++) {
            $expires_month[] = array(
                'id' => sprintf('%02d', $i),
                'text' => utf8_decode(strftime('%B', mktime(0, 0, 0, $i, 1, 2000)))
            );
        }

        $today = getdate();
        for ($i = $today['year']; $i < $today['year'] + 10; $i++) {//
            $expires_year[] = array(
                'id' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
                'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i))
            );
        }

        $months_string = '';
        foreach ($expires_month as $m) {
            $months_string .= '<option value="' . $m['id'] . '">' . $m['text'] . '</option>';
        }

        $years_string = '';
        foreach ($expires_year as $y) {
            $years_string .= '<option value="' . $y['id'] . '">' . $y['text'] . '</option>';
        }

        $formArray = array();

        $formArray[] = array(
            'title' => '',
            'field' => '<link rel="stylesheet" type="text/css" href="' . HTTP_SERVER . DIR_WS_CATALOG . 'css/paymill.css"/>'
        );

        $resourcesDir = HTTP_SERVER . DIR_WS_CATALOG . '/includes/modules/payment/resources/';
        $this->accepted = xtc_image($resourcesDir . 'icon_mastercard.png') . " " . xtc_image($resourcesDir . 'icon_visa.png');

        $formArray[] = array(
            'field' => $this->accepted
        );

        $formArray[] = array(
            'title' => 'Kreditkarten-Nummer',
            'field' => '<br/><input type="text" id="card-number" class="form-row-paymill"/>'
        );

        $formArray[] = array(
            'title' => 'G&uuml;ltigkeitsdatum',
            'field' => '<br/><span class="paymill-expiry"><select id="card-expiry-month">' . $months_string . '</select>'
                     . '&nbsp;'
                     . '<select id="card-expiry-year">' . $years_string . '</select></span>'
        );

        $formArray[] = array(
            'title' => 'CVC-Code',
            'field' => '<br/><span class="card-cvc-row"><input type="text" size="4" id="card-cvc" class="form-row-paymill"/></span>'
            . '<br/>'
            . '<a href="javascript:popupWindow(\'' . xtc_href_link(FILENAME_POPUP_CVV, '', 'SSL') . '\')">Info</a>'
        );

        $formArray[] = array(
        'field' =>
            '<div class="form-row">'
              . '<div class="paymill_powered">'
                   . '<div class="paymill_credits">'
                       . 'Sichere Kreditkartenzahlung powered by'
                      . ' <a href="http://www.paymill.de" target="_blank">Paymill</a>'
                   . '</div>'
               . '</div>'
           . '</div>'
        );

        $_SESSION['pi']['paymill_amount'] = $amount;
        
        $formArray[] = array(
            'title' => '',
            'field' => '<br/><input type="hidden" value="' . $amount * 100 . '" id="amount" name="amount"/>'
        );

        $formArray[] = array(
            'title' => '',
            'field' => '<br/><input type="hidden" value="' . strtoupper($order->info['currency']) . '" id="currency" name="currency"/>'
        );

        $script = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>'
                . '<script type="text/javascript">'
                    . 'var PAYMILL_PUBLIC_KEY = "' . MODULE_PAYMENT_PAYMILL_CC_PUBLICKEY . '";'
                . '</script>'
                . '<script type="text/javascript" src="' . MODULE_PAYMENT_PAYMILL_CC_BRIDGE_URL . '"></script>'
                . '<script type="text/javascript">'
                    . 'var cc_expiery_invalid = ' . utf8_decode('"Das Gültigkeitsdatum ihrer Kreditkarte ist ungültig. Bitte korrigieren Sie Ihre Angaben.";')
                    . 'var cc_card_number_invalid = ' . utf8_decode('"Die Kreditkarten-Nummer, die Sie angegeben haben, ist ungültig. Bitte korrigieren Sie Ihre Angaben.";')
                    . 'var cc_cvc_number_invalid = ' . utf8_decode('"Das Formularfeld Kontoinhaber ist ein Pflichfeld.";')
                    . file_get_contents(DIR_FS_CATALOG . 'javascript/paymill_cc_checkout.js')
                . '</script>';

        $formArray[] = array(
            'title' => "",
            'field' => $script
        );

        $selection = array(
            'id' => $this->code,
            'module' => $this->title,
            'fields' => $formArray,
            'description' => $this->info
        );

        return $selection;
    }

    function check()
    {
        if (!isset($this->_check)) {
            $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PAYMILL_CC_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function install()
    {
        if (xtc_db_num_rows(xtc_db_query("SELECT * from " . TABLE_ORDERS_STATUS . " where orders_status_name LIKE '%Paymill%'")) == 0) {
            //based on orders_status.php with action save new orders_status_id
            $next_id_query = xtc_db_query("select max(orders_status_id) as orders_status_id from " . TABLE_ORDERS_STATUS . "");
            $next_id = xtc_db_fetch_array($next_id_query);
            $orders_status_id = $next_id['orders_status_id'] + 1;
            //based on orders_status.php ends
            xtc_db_query("INSERT INTO " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) VALUES (" . $orders_status_id . ",1, 'Paymill Payment cancelled'),(" . $orders_status_id . ",2,'Paymill Bezahlung abgebrochen');");
        }
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_CC_STATUS', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_CC_ALLOWED', '', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_CC_SORT_ORDER', '0', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_CC_PUBLICKEY', '0', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_CC_PRIVATEKEY', '0', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_CC_BRIDGE_URL', 'https://bridge.paymill.de/', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_CC_API_URL', 'https://api.paymill.de/v2/', '6', '0', now())");
    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
    }

    function keys()
    {
        return array(
            'MODULE_PAYMENT_PAYMILL_CC_STATUS',
            'MODULE_PAYMENT_PAYMILL_CC_SORT_ORDER',
            'MODULE_PAYMENT_PAYMILL_CC_PUBLICKEY',
            'MODULE_PAYMENT_PAYMILL_CC_PRIVATEKEY',
            'MODULE_PAYMENT_PAYMILL_CC_ALLOWED',
            'MODULE_PAYMENT_PAYMILL_CC_BRIDGE_URL',
            'MODULE_PAYMENT_PAYMILL_CC_API_URL'
        );
    }

}
