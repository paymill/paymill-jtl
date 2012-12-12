$(document).ready(function () {

    function paymillResponseHandler(error, result) 
    {
        paymillDebug('Paymill: Start response handler');
        if (error) {
            paymillDebug('An API error occured:' + error.apierror);
            var payment  = 'elv';
            if ($("input[name='Zahlungsart']:checked").val() == $("#paymill_cc").val()) payment = 'cc';
            $("#payment-errors-" + payment).text(error.apierror);
            $("#payment-errors-" + payment).css('display', 'block');
        } else {
            $("#payment-errors-cc").text("");
            $("#payment-errors-elv").text("");
            $("#payment-errors-cc").css('display', 'none');
            $("#payment-errors-elv").css('display', 'none');
            var form = $("#zahlung");
            var token = result.token;
            paymillDebug('Received a token: ' + token);
            form.append("<input type='hidden' name='paymillToken' value='" + token + "'/>");
            form.get(0).submit();
        }
    }
    
    function paymillCc()
    {
        paymillDebug('Paymill Creditcard: Start form validation');
        
        if (false == paymill.validateCardNumber($('.card-number').val())) {
            $("#payment-errors-cc").text(lang['card_number_invalid']);
            $("#payment-errors-cc").css('display', 'block');
            return false;
        }

        if (false == paymill.validateExpiry($('.card-expiry-month').val(), $('.card-expiry-year').val())) {
            $("#payment-errors-cc").text(lang['expiration_date_invalid']);
            $("#payment-errors-cc").css('display', 'block');
            return false;
        }
        
        if (false == paymill.validateCvc($('.card-cvc').val())) {
            $("#payment-errors-cc").text(lang['verfication_number_invalid']);
            $("#payment-errors-cc").css('display', 'block');
            return false;
        }
        
        if ($('.card-holdername').val() == "") {
            $("#payment-errors-cc").text(lang['card_holder_invalid']);
            $("#payment-errors-cc").css('display', 'block');
            return false;
        }
        
        paymill.createToken({
            number : $('.card-number').val(),
            exp_month : $('.card-expiry-month').val(),
            exp_year : $('.card-expiry-year').val(),
            cvc : $('.card-cvc').val(),
            cardholdername : $('.card-holdername').val(),
            amount : $('#paymill_amount').val(),
            currency : $('#paymill_currency').val()
        }, paymillResponseHandler);
        
        return false;
    }
    
    function paymillElv()
    {
        paymillDebug('Paymill ELV: Start form validation');
        if (false == paymill.validateAccountNumber($('.account-number').val())) {
            $("#payment-errors-elv").text(lang['account_number_invalid']);
            $("#payment-errors-elv").css('display', 'block');
            return false;
        }
        
        if (false == paymill.validateBankCode($('.bank-code').val())) {
            $("#payment-errors-elv").text(lang['sort_code_invalid']);
            $("#payment-errors-elv").css('display', 'block');
            return false;
        }
        
        if ($('.bank-owner').val() == "") {
            $("#payment-errors-elv").text(lang['account_owner_invalid']);
            $("#payment-errors-elv").css('display', 'block');
            return false; 
        }
        
        paymill.createToken({
            number:        $('.account-number').val(),
            bank:          $('.bank-code').val(),
            accountholder: $('.bank-owner').val()
        }, paymillResponseHandler);
        
        return false;
    }
    
    $(".submit").click(function (event) {
        var payment = $("input[name='Zahlungsart']:checked").val();
        if (payment == $("#paymill_cc").val()) {
            paymillDebug('Paymill Creditcard: Payment method triggered');
            return paymillCc();
        } else if(payment == $("#paymill_elv").val()) {
            paymillDebug('Paymill ELV: Payment method triggered');
            return paymillElv();
        } else {
            $("#zahlung").get(0).submit();
        }
        
    });
    
    function paymillDebug(message)
    {
        if (debug) {
            console.log(message);
        }
    }

});
