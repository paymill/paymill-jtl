<?php

require_once('abstract/paymill.php');

class paymill_elv extends paymill
{

    function paymill_elv()
    {
        $this->code = 'paymill_elv';
        $this->version = '1.0.3';
        $this->title = 'Elektronisches Lastschriftverfahren';
        $this->public_title = 'Elektronisches Lastschriftverfahren';
        $this->sort_order = MODULE_PAYMENT_PAYMILL_ELV_SORT_ORDER;
        $this->enabled = ((MODULE_PAYMENT_PAYMILL_ELV_STATUS == 'True') ? true : false);
        $this->privateKey = MODULE_PAYMENT_PAYMILL_ELV_PRIVATEKEY;
        $this->apiUrl = MODULE_PAYMENT_PAYMILL_ELV_API_URL;
    }

    function selection()
    {
        $resourcesDir = HTTP_SERVER . DIR_WS_CATALOG . '/includes/modules/payment/resources/';

        $formArray = array();

        $formArray[] = array(
            'title' => '',
            'field' => '<link rel="stylesheet" type="text/css" href="' . HTTP_SERVER . DIR_WS_CATALOG . 'css/paymill.css"/>'
        );

        $formArray[] =  array(
            'title' => '',
            'field' => xtc_image($resourcesDir . 'icon_elv.png')
        );

        $formArray[] = array(
            'title' => 'Kontonummer',
            'field' => '<br/><input type="text" id="account-number" class="form-row-paymill"/>'
        );

        $formArray[] = array(
            'title' => 'Bankleitzahl',
            'field' => '<br/><input type="text" id="bank-code" class="form-row-paymill"/>'
        );

        $formArray[] = array(
            'title' => 'Kontoinhaber',
            'field' => '<br/><input type="text" id="bank-owner" class="form-row-paymill"/>'
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

        $script = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>'
                . '<script type="text/javascript">'
                    . 'var PAYMILL_PUBLIC_KEY = "' . MODULE_PAYMENT_PAYMILL_ELV_PUBLICKEY . '";'
                . '</script>'
                . '<script type="text/javascript" src="' . MODULE_PAYMENT_PAYMILL_ELV_BRIDGE_URL . '"></script>'
                . '<script type="text/javascript">'
                    . 'var elv_account_number_invalid = ' . utf8_decode('"Die Kontonummer, die Sie angegeben haben, ist ungültig. Bitte korrigieren Sie Ihre Angaben.";')
                    . 'var elv_bank_code_invalid = ' . utf8_decode('"Die Bankleitzahl, die Sie angegeben haben, ist ungültig. Bitte korrigieren Sie Ihre Angaben.";')
                    . 'var elv_bank_owner_invalid = ' . utf8_decode('"Die Kontonummer, die Sie angegeben haben, ist ungültig. Bitte korrigieren Sie Ihre Angaben.";')
                    . file_get_contents(DIR_FS_CATALOG . 'javascript/paymill_elv_checkout.js')
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
            $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PAYMILL_ELV_STATUS'");
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
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_ELV_STATUS', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_ELV_ALLOWED', '', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_ELV_SORT_ORDER', '0', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_ELV_PUBLICKEY', '0', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_ELV_PRIVATEKEY', '0', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_ELV_BRIDGE_URL', 'https://bridge.paymill.de/', '6', '0', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_PAYMILL_ELV_API_URL', 'https://api.paymill.de/v2/', '6', '0', now())");
    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
    }

    function keys()
    {
        return array(
            'MODULE_PAYMENT_PAYMILL_ELV_STATUS',
            'MODULE_PAYMENT_PAYMILL_ELV_SORT_ORDER',
            'MODULE_PAYMENT_PAYMILL_ELV_PUBLICKEY',
            'MODULE_PAYMENT_PAYMILL_ELV_PRIVATEKEY',
            'MODULE_PAYMENT_PAYMILL_ELV_ALLOWED',
            'MODULE_PAYMENT_PAYMILL_ELV_BRIDGE_URL',
            'MODULE_PAYMENT_PAYMILL_ELV_API_URL'
        );
    }

}
