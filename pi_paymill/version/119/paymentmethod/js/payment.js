$(document).ready(function()
{
	var paymentSubmitted = false;
	
    $('#paymill-card-number').keyup(function() {
        var detector = new PaymillBrandDetection();
        var brand = detector.detect($('#paymill-card-number').val());
		brand = brand.toLowerCase();
		$("#paymill-card-number")[0].className = $("#paymill-card-number")[0].className.replace(/paymill-card-number-.*/g, '');
		if (brand !== 'unknown') {
            $('#paymill-card-number').addClass("paymill-card-number-" + brand);
            if (!detector.validate($('#paymill-card-number').val())) {
                $('#paymill-card-number').addClass("paymill-card-number-grayscale");
            }
		}
    });

    function paymillElvResponseHandler(error, result)
    {
		paymillDebug('Paymill: Start response handler');
		if (error) {
			paymillDebug('An API error occured:' + error.apierror);
			$("#payment-errors-elv").text(lang[error.apierror]);
			$("#payment-errors-elv").css('display', 'block');
		} else {
			paymentSubmitted = true;
			$("#payment-errors-elv").text("");
			$("#payment-errors-elv").css('display', 'none');
			var form = $("#complete_order");
			var token = result.token;
			paymillDebug('Received a token: ' + token);
			form.append("<input type='hidden' name='paymillToken' value='" + token + "'/>");
			flag = false;
			form.submit();
		}
    }

    function paymillCcResponseHandler(error, result)
    {
		paymillDebug('Paymill: Start response handler');
		if (error) {
			paymillDebug('An API error occured:' + error.apierror);
			$("#payment-errors-cc").text(lang[error.apierror]);
			$("#payment-errors-cc").css('display', 'block');
		} else {
			paymentSubmitted = true;
			$("#payment-errors-cc").text("");
			$("#payment-errors-cc").css('display', 'none');
			var form = $("#complete_order");
			var token = result.token;
			paymillDebug('Received a token: ' + token);
			form.append("<input type='hidden' name='paymillToken' value='" + token + "'/>");
			flag = false;
			form.submit();
		}
    }
	
    function paymillCc()
    {
        paymillDebug('Paymill Creditcard: Start form validation');

		$("#payment-errors-cc").css('display', 'none');
		$('#paymill-card-expiry').removeClass('field-error');
		$('#paymill-card-number').removeClass('field-error');
		$('#paymill-card-cvc').removeClass('field-error');
		$('#paymill-card-holdername').removeClass('field-error');

        var ccErrorFlag = true;

        if (!paymill.validateCardNumber($('#paymill-card-number').val())) {
			$('#paymill-card-number').addClass('field-error');
            $("#payment-errors-cc").append('<div>* ' + lang['card_number_invalid'] + '</div>');
            $("#payment-errors-cc").css('display', 'block');
            ccErrorFlag = false;
        }

        if (!paymill.validateExpiry($('#paymill-card-expiry-month').val(), $('#paymill-card-expiry-year').val())) {
			$('#paymill-card-expiry').addClass('field-error');
            $("#payment-errors-cc").append('<div>* ' + lang['expiration_date_invalid'] + '</div>');
            $("#payment-errors-cc").css('display', 'block');
            ccErrorFlag = false;
        }

        if (!paymill.validateCvc($('#paymill-card-cvc').val())) {
            if (paymill.cardType($('#paymill-card-number').val()).toLowerCase() !== 'maestro') {
				$('#paymill-card-cvc').addClass('field-error');
                $("#payment-errors-cc").append('<div>* ' + lang['verfication_number_invalid'] + '</div>');
                $("#payment-errors-cc").css('display', 'block');
                ccErrorFlag = false;
            }
        }

        if (!paymill.validateHolder($('#paymill-card-holdername').val())) {
			$('#paymill-card-holdername').addClass('field-error');
            $("#payment-errors-cc").append('<div>* ' + lang['card_holder_invalid'] + '</div>');
            $("#payment-errors-cc").css('display', 'block');
            ccErrorFlag = false;
        }

        if (!ccErrorFlag) {
            return ccErrorFlag;
        }

        var cvc = '000';

        if ($('#paymill-card-cvc').val() !== '') {
            cvc = $('#paymill-card-cvc').val();
        }

        paymill.createToken({
            number: $('#paymill-card-number').val(),
            exp_month: $('#paymill-card-expiry-month').val(),
            exp_year: $('#paymill-card-expiry-year').val(),
            cvc: cvc,
            cardholder: $('#paymill-card-holdername').val(),
            amount_int: $('#paymill_amount').val(),
            currency: $('#paymill_currency').val()
        }, paymillCcResponseHandler);

        return false;
    }

    $('#paymill-card-number').focus(function() {
        fastCheckoutCc = 'false';
    });

    $('#paymill-card-expiry-month').focus(function() {
        fastCheckoutCc = 'false';
    });

    $('#paymill-card-expiry-month').focus(function() {
        fastCheckoutCc = 'false';
    });

    $('#paymill-card-cvc').focus(function() {
        fastCheckoutCc = 'false';
    });

    $('#paymill-card-holdername').focus(function() {
        fastCheckoutCc = 'false';
    });

    function paymillElvSepa()
    {
        paymillDebug('Paymill ELV SEPA: Start form validation');

		$("#payment-errors-elv").css('display', 'none');
		$('#paymill-iban').removeClass('field-error');
		$('#paymill-bic').removeClass('field-error');
		$('#paymill-bank-owner').removeClass('field-error');

        var elvErrorFlag = true;

        ibanWithoutSpaces = $('#paymill-iban').val();
        ibanWithoutSpaces = ibanWithoutSpaces.replace(/\s+/g, "");
        ibanValidator = new PaymillIban();

        if (!ibanValidator.validate(ibanWithoutSpaces)) {
			$('#paymill-iban').addClass('field-error');
            $("#payment-errors-elv").text('<div>* ' + lang['iban_invalid'] + '</div>');
            $("#payment-errors-elv").css('display', 'block');
            elvErrorFlag = false;
        }

        if (!($('#paymill-bic').val().length === 8 || $('#paymill-bic').val().length === 11)) {
			$('#paymill-bic').addClass('field-error');
            $("#payment-errors-elv").text('<div>* ' + lang['bic_invalid'] + '</div>');
            $("#payment-errors-elv").css('display', 'block');
            elvErrorFlag = false;
        }

        if ($('#paymill-bank-owner').val() === "") {
			$('#paymill-bank-owner').addClass('field-error');
            $("#payment-errors-elv").append('<div>* ' + lang['account_owner_invalid'] + '</div>');
            $("#payment-errors-elv").css('display', 'block');
            elvErrorFlag = false;
        }
		
        if (!elvErrorFlag) {
            return elvErrorFlag;
        }

        paymill.createToken({
            iban: ibanWithoutSpaces,
            bic: $('#paymill-bic').val(),
            accountholder: $('#paymill-bank-owner-sepa').val()
        }, paymillElvResponseHandler);

        return false;
    }

    function paymillElv()
    {
        paymillDebug('Paymill ELV: Start form validation');

		$("#payment-errors-elv").css('display', 'none');
		$('#paymill-account-number').removeClass('field-error');
		$('#paymill-bank-code').removeClass('field-error');
		$('#paymill-bank-owner').removeClass('field-error');
		
        var elvErrorFlag = true;

        if (!paymill.validateAccountNumber($('#paymill-account-number').val())) {
			$('#paymill-account-number').addClass('field-error');
            $("#payment-errors-elv").append('<div>* ' + lang['account_number_invalid'] + '</div>');
            $("#payment-errors-elv").css('display', 'block');
            elvErrorFlag = false;
        }

        if (!paymill.validateBankCode($('#paymill-bank-code').val())) {
			$('#paymill-bank-code').addClass('field-error');
            $("#payment-errors-elv").append('<div>* ' + lang['sort_code_invalid'] + '</div>');
            $("#payment-errors-elv").css('display', 'block');
            elvErrorFlag = false;
        }

        if ($('#paymill-bank-owner').val() === "") {
			$('#paymill-bank-owner').addClass('field-error');
            $("#payment-errors-elv").append('<div>* ' + lang['account_owner_invalid'] + '</div>');
            $("#payment-errors-elv").css('display', 'block');
            elvErrorFlag = false;
        }

        if (!elvErrorFlag) {
            return elvErrorFlag;
        }

        paymill.createToken({
            number: $('#paymill-account-number').val(),
            bank: $('#paymill-bank-code').val(),
            accountholder: $('#paymill-bank-owner').val()
        }, paymillElvResponseHandler);

        return false;
    }

    $('#paymill-iban').focus(function() {
        fastCheckoutElv = 'false';
    });

    $('#paymill-bic').focus(function() {
        fastCheckoutElv = 'false';
    });

    $('#paymill-account-number').focus(function() {
        fastCheckoutElv = 'false';
    });

    $('#paymill-bank-code').focus(function() {
        fastCheckoutElv = 'false';
    });

    $('#paymill-bank-owner').focus(function() {
        fastCheckoutElv = 'false';
    });

    $("#complete_order").submit(function(event) {
		if (!paymentSubmitted) {
			event.preventDefault();
			var form = $("#complete_order");
			if (cc) {
				paymillDebug('Paymill Creditcard: Payment method triggered');
				if (fastCheckoutCc === 'false') {
					return paymillCc();
				} else {
					form.append("<input type='hidden' name='paymillToken' value='dummyToken'/>");
					form.submit();
				}
			} else if (elv) {
				paymillDebug('Paymill ELV: Payment method triggered');
				if (fastCheckoutElv === 'false') {
					if ($('#paymill-account-number').length) {
						return paymillElv();
					} else {
						return paymillElvSepa();
					}
				} else {
					form.append("<input type='hidden' name='paymillToken' value='dummyToken'/>");
					form.submit();
				}
			}
		}
    });

    function paymillDebug(message)
    {
        if (debug) {
            console.log(message);
        }
    }

});
