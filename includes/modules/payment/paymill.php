<?php
/**
 * Paymill payment plugin
 */
class paymill {
    
	var $code, $title, $description = '', $enabled;

	function paymill() {

		$this->code = 'paymill';
        $this->title = 'Kreditkartenzahlung';
		$this->sort_order = MODULE_PAYMENT_PAYMILL_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_PAYMILL_STATUS == 'True') ? true : false);
	}

    function selection() {

		global $order;

        // build date select options
		for ($i = 1; $i < 13; $i ++) {
			$expires_month[] = array(
                'id' => sprintf('%02d', $i), 
                'text' => strftime('%B', mktime(0, 0, 0, $i, 1, 2000))
            );
		}
		$today = getdate();
		for ($i = $today['year']; $i < $today['year'] + 10; $i ++) {//
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

		$form_array = array();

		// CC Number
		$form_array[] = array(
            'title' => 'Kreditkarten-Nummer', 
            'field' => '<input type="text" id="card-number"/>'
        );

		// expire date
		$form_array[] = array(
            'title' => 'G&uuml;ltigkeitsdatum', 
            'field' => '<select id="card-expiry-month">' 
                . $months_string 
                . '</select>' 
                . '&nbsp;' 
                . '<select id="card-expiry-year">' . $years_string . '</select>' 
        );

		// CVV
		$form_array[] = array(
            'title' => 'CVC-Code' . ' ' 
                . '<a href="javascript:popupWindow(\''.xtc_href_link(FILENAME_POPUP_CVV, '', 'SSL').'\')">'
                . 'Info' . '</a>', 
            'field' => '<input type="text" size="4" id="card-cvc" />'
        );

		// Paymill token
		$form_array[] = array(
		    'title' => '', 
		    'field' => '<input type="hidden" name="paymill_token" id="paymill_token" />'
		);

		// paymill javascript
		$script ='<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>'
		. '<script type="text/javascript">' 
		. 'var PAYMILL_PUBLIC_KEY = "' . MODULE_PAYMENT_PAYMILL_PUBLICKEY . '";'
		. '</script>'
		. '<script type="text/javascript" src="' . MODULE_PAYMENT_PAYMILL_BRIDGE_URL . '"></script>' 
		. '<script type="text/javascript">' 
		. 'function paymillFormHanlder() { 
            if ($("input[name=\'payment\'][value=\'paymill\']").attr("checked") == "checked") {
				// javascript_validation 
				if (!paymill.validateExpiry($("#card-expiry-month option:selected").val(), $("#card-expiry-year option:selected").val())) {
					alert("Das Gültigkeitsdatum ihrer Kreditkarte ist ungültig. Bitte korrigieren Sie Ihre Angaben.");
					return false;
				}
				if (!paymill.validateCardNumber($("#card-number").val())) {
					alert("Die Kreditkarten-Nummer, die Sie angegeben haben, ist ungültig. Bitte korrigieren Sie Ihre Angaben.");
					return false;
				}
				if (!paymill.validateCvc($("#card-cvc").val())) {
					alert("Die angegebene CVC-Nummer ist ungültig.");
					return false;
				}
                if ($("input[name=\'conditions\']").attr("checked") != "checked") {
                    alert("Bitte bestätige die Allgmeinen Geschäftsbedingungen");
                    return false;
                }
				paymill.createToken({
            		number: $("#card-number").val(),
            		exp_month: $("#card-expiry-month option:selected").val(), 
            		exp_year: $("#card-expiry-year option:selected").val(), 
            		cvc: $("#card-cvc").val()
           		}, PaymillResponseHandler);
		 		return false; 
            }
		 } ' 
		. 'function PaymillResponseHandler(error, result) { 
			if (error) {
				console.log(error.apierror);
			} else {
				console.log(result.token);
				$("#paymill_token").val(result.token);
				$("#checkout_payment").attr("onSubmit", "");
				$("#checkout_payment").submit();
			}
		} '
		. '$("#checkout_payment").attr("onSubmit", "return paymillFormHanlder();")'
		. '</script>';

		$form_array[] = array(
            'title' => "", 
            'field' => $script
        );

		// cards
		$this->accepted .= xtc_image(DIR_WS_ICONS . 'cc_visa.jpg');
		$this->accepted .= xtc_image(DIR_WS_ICONS . 'cc_mastercard.jpg');
					
		$form_array[] = array(
            'title' => MODULE_PAYMENT_PAYMILL_ACCEPTED_CARDS,
            'field' => $this->accepted
        );

		$selection = array(
            'id' => $this->code, 
            'module' => $this->title, 
            'fields' => $form_array, 
            'description' => $this->info
        );

		return $selection;
	}

	function pre_confirmation_check() {
		$this->paymill_token = $_POST['paymill_token'];
		return false;
	}

	function confirmation() {
		return false;
	}

	function process_button() {
        global $order, $xtPrice;

        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
            $total = $order->info['total'] + $order->info['tax'];
            die($order->info['tax']);
        } else {
            $total = $order->info['total'];
        }
        if ($_SESSION['currency'] == $order->info['currency']) {
            $amount = round($total, $xtPrice->get_decimal_places($order->info['currency']));
        } else {
            $amount = round($xtPrice->xtcCalculateCurrEx($total, $order->info['currency']), $xtPrice->get_decimal_places($order->info['currency']));
        }

        $paymill_amount = $amount * 100;

		$process_button_string = xtc_draw_hidden_field('paymill_token', $this->paymill_token).xtc_draw_hidden_field('paymill_amount', $paymill_amount);

		return $process_button_string;
	}

	function before_process() {
		global $order, $xtPrice;
		
		// include paymill wrapper
		require_once dirname(__FILE__) . '/paymill/lib/Services/Paymill/Creditcards.php';
		require_once dirname(__FILE__) . '/paymill/lib/Services/Paymill/Transactions.php';
		require_once dirname(__FILE__) . '/paymill/lib/Services/Paymill/Clients.php';

		// read the token
		$paymill_token = $_POST['paymill_token'];
        $amount = $_POST['paymill_amount'];

		// setup client params
        $clientParams = array(
            'email' => $order->customer['email_address'],
            'description' => $order->customer['lastname'] . ', ' . $order->customer['firstname']
        );

        // setup credit card params
        $creditcardParams = array(
            'token' => $paymill_token
        );

        // setup transaction params

        $transactionParams = array(
            'amount' => $amount,
            'currency' => strtolower($order->info['currency']),
            'description' => $order->customer['lastname'] . ', ' . $order->customer['firstname']
        );

        // configuration
        $paymillPrivateApiKey = MODULE_PAYMENT_PAYMILL_PRIVATEKEY;
        $paymillApiEndpoint = MODULE_PAYMENT_PAYMILL_API_URL;

        // Access objects for the Paymill API
        $clientsObject = new Services_Paymill_Clients(
            $paymillPrivateApiKey, $paymillApiEndpoint
        );
        $creditcardsObject = new Services_Paymill_Creditcards(
            $paymillPrivateApiKey, $paymillApiEndpoint
        );
        $transactionsObject = new Services_Paymill_Transactions(
            $paymillPrivateApiKey, $paymillApiEndpoint
        );
		
		// perform conection to the Paymill API and trigger the payment
        try {
            // create card
            $creditcard = $creditcardsObject->create($creditcardParams);

            // create client
            $clientParams['creditcard'] = $creditcard['id'];
            $client = $clientsObject->create($clientParams);

            // create transaction
            $transactionParams['client'] = $client['id'];
            $transaction = $transactionsObject->create($transactionParams);

            // check if transaction was successful, otherwise redirect to payment page
            if (is_array($transaction) && array_key_exists('status', $transaction)) {
                if ($transaction['status'] == "closed") {
                    return true;
                } elseif ($transaction['status'] == "open") {
                	xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'step=step2&payment_error=paymill&error=100', 'SSL', true, false));
                } else {
                	xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'step=step2&payment_error=paymill&error=200', 'SSL', true, false));
                }
            } else {
            	xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'step=step2&payment_error=paymill&error=200', 'SSL', true, false));
            }
        } catch (Services_Paymill_Exception $ex) {
        	xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'step=step2&payment_error=paymill&error=200', 'SSL', true, false));
        } 
	}

    function get_error() {
        global $_GET, $language;
        $error = '';
    	if(isset($_GET['error'])) {
    		$error = urldecode($_GET['error']); 
        }
    	switch($error) {
		    case '100':
                $error_text['error'] = MODULE_PAYMENT_PAYMILL_TEXT_ERROR_100;
                break;
            case '200':
                $error_text['error'] = MODULE_PAYMENT_PAYMILL_TEXT_ERROR_200;
                break;
        }
        return $error_text;
    }

	function check() {
		if (!isset ($this->_check)) {
			$check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_PAYMILL_STATUS'");
			$this->_check = xtc_db_num_rows($check_query);
		}
		return $this->_check;
	}

	function install() {
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_PAYMILL_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'))', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYMILL_ALLOWED', '', '6', '0', now())");
		xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYMILL_SORT_ORDER', '0', '6', '0', now())");
        xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYMILL_PUBLICKEY', '0', '6', '0', now())");
        xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYMILL_PRIVATEKEY', '0', '6', '0', now())");
        xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYMILL_BRIDGE_URL', 'https://bridge.paymill.de/', '6', '0', now())");
        xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_PAYMILL_API_URL', 'https://api.paymill.de/v1/', '6', '0', now())");

	}

	function remove() {
		xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('".implode("', '", $this->keys())."')");
	}

	function keys() {
		return array('MODULE_PAYMENT_PAYMILL_STATUS',  'MODULE_PAYMENT_PAYMILL_SORT_ORDER', 'MODULE_PAYMENT_PAYMILL_PUBLICKEY', 'MODULE_PAYMENT_PAYMILL_PRIVATEKEY', 'MODULE_PAYMENT_PAYMILL_ALLOWED', 'MODULE_PAYMENT_PAYMILL_BRIDGE_URL', 'MODULE_PAYMENT_PAYMILL_API_URL');
	}

    function update_status() {
        global $order;//
    }

    function javascript_validation() {
        return '';
    }

    function after_process() {
    }
}
?>