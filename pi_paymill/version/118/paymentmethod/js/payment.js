
$(document).ready(function()
{
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
        if (flag) {
            paymillDebug('Paymill: Start response handler');
            if (error) {
                paymillDebug('An API error occured:' + error.apierror);
                $("#payment-errors-elv").text(lang[error.apierror]);
                $("#payment-errors-elv").css('display', 'block');
            } else {
                $("#payment-errors-elv").text("");
                $("#payment-errors-elv").css('display', 'none');
                var form = $("#zahlung");
                var token = result.token;
                paymillDebug('Received a token: ' + token);
                form.append("<input type='hidden' name='paymillToken' value='" + token + "'/>");
                flag = false;
                form.get(0).submit();
            }
        }
    }

    function paymillCcResponseHandler(error, result)
    {
        if (flag) {
            paymillDebug('Paymill: Start response handler');
            if (error) {
                paymillDebug('An API error occured:' + error.apierror);
                $("#payment-errors-cc").text(lang[error.apierror]);
                $("#payment-errors-cc").css('display', 'block');
            } else {
                $("#payment-errors-cc").text("");
                $("#payment-errors-cc").css('display', 'none');
                var form = $("#zahlung");
                var token = result.token;
                paymillDebug('Received a token: ' + token);
                form.append("<input type='hidden' name='paymillToken' value='" + token + "'/>");
                flag = false;
                form.get(0).submit();
            }
        }
    }

    function hideErrorBoxes(payment, limit)
    {
        for (i = 0; i <= limit; i++) {
            $("#payment-error-" + payment + "-" + i).css('display', 'none');
        }
    }

    function paymillCc()
    {
        paymillDebug('Paymill Creditcard: Start form validation');

        hideErrorBoxes('cc', 4);

        var ccErrorFlag = true;

        if (!paymill.validateCardNumber($('#paymill-card-number').val())) {
            $("#payment-error-cc-1").text(lang['card_number_invalid']);
            $("#payment-error-cc-1").css('display', 'block');
            ccErrorFlag = false;
        }

        if (!paymill.validateExpiry($('#paymill-card-expiry-month').val(), $('#paymill-card-expiry-year').val())) {
            $("#payment-error-cc-4").text(lang['expiration_date_invalid']);
            $("#payment-error-cc-4").css('display', 'block');
            ccErrorFlag = false;
        }

        if (!paymill.validateCvc($('#paymill-card-cvc').val())) {
            if (paymill.cardType($('#paymill-card-number').val()).toLowerCase() !== 'maestro') {
                $("#payment-error-cc-2").text(lang['verfication_number_invalid']);
                $("#payment-error-cc-2").css('display', 'block');
                ccErrorFlag = false;
            }
        }

        if (!paymill.validateHolder($('#paymill-card-holdername').val())) {
            $("#payment-error-cc-3").text(lang['card_holder_invalid']);
            $("#payment-error-cc-3").css('display', 'block');
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

        hideErrorBoxes('elv', 3);

        var elvErrorFlag = true;

        if ($('#paymill-iban').val() === '') {
            $("#payment-error-elv-1").text(lang['iban_invalid']);
            $("#payment-error-elv-1").css('display', 'block');
            elvErrorFlag = false;
        }

        if ($('#paymill-bic').val() === '') {
            $("#payment-error-elv-2").text(lang['bic_invalid']);
            $("#payment-error-elv-2").css('display', 'block');
            elvErrorFlag = false;
        }

        if (!elvErrorFlag) {
            return elvErrorFlag;
        }

        paymill.createToken({
            iban: $('#paymill-iban').val(),
            bic: $('#paymill-bic').val(),
            accountholder: $('#paymill-bank-owner-sepa').val()
        }, paymillElvResponseHandler);

        return false;
    }

    function paymillElv()
    {
        paymillDebug('Paymill ELV: Start form validation');

        hideErrorBoxes('elv', 3);

        var elvErrorFlag = true;

        if (false === paymill.validateAccountNumber($('#paymill-account-number').val())) {
            $("#payment-error-elv-1").text(lang['account_number_invalid']);
            $("#payment-error-elv-1").css('display', 'block');
            elvErrorFlag = false;
        }

        if (false === paymill.validateBankCode($('#paymill-bank-code').val())) {
            $("#payment-error-elv-2").text(lang['sort_code_invalid']);
            $("#payment-error-elv-2").css('display', 'block');
            elvErrorFlag = false;
        }

        if ($('#paymill-bank-owner').val() === "") {
            $("#payment-error-elv-3").text(lang['account_owner_invalid']);
            $("#payment-error-elv-3").css('display', 'block');
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

    $(".submit").click(function(event) {
        var form = $("#zahlung");
        var payment = $("input[name='Zahlungsart']:checked").val();
        if (payment === $("#paymill_cc").val()) {
            paymillDebug('Paymill Creditcard: Payment method triggered');
            if (fastCheckoutCc === 'false') {
                return paymillCc();
            } else {
                form.append("<input type='hidden' name='paymillToken' value='dummyToken'/>");
            }
        } else if (payment === $("#paymill_elv").val()) {
            paymillDebug('Paymill ELV: Payment method triggered');
            if (fastCheckoutElv === 'false') {
				if ($('#paymill-account-number').length) {
					return paymillElv();
				} else {
					return paymillElvSepa();
				}
            } else {
                form.append("<input type='hidden' name='paymillToken' value='dummyToken'/>");
            }
        }

        $("#zahlung").get(0).submit();
    });

    function paymillDebug(message)
    {
        if (debug) {
            console.log(message);
        }
    }

});
