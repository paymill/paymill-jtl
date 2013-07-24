
$(document).ready(function () 
{
    
    function paymillElvResponseHandler(error, result) 
    {
        if (flag) {
            paymillDebug('Paymill: Start response handler');
            if (error) {
                paymillDebug('An API error occured:' + error.apierror);
                $("#payment-errors-elv").text(error.apierror);
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
                $("#payment-errors-cc").text(error.apierror);
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
        
        if (false === paymill.validateCardNumber($('.card-number').val())) {
            $("#payment-error-cc-1").text(lang['card_number_invalid']);
            $("#payment-error-cc-1").css('display', 'block');
            ccErrorFlag = false;
        }

        if (false === paymill.validateExpiry($('.card-expiry-month').val(), $('.card-expiry-year').val())) {
            $("#payment-error-cc-4").text(lang['expiration_date_invalid']);
            $("#payment-error-cc-4").css('display', 'block');
            ccErrorFlag = false;
        }
        
        if (false === paymill.validateCvc($('.card-cvc').val())) {
            $("#payment-error-cc-2").text(lang['verfication_number_invalid']);
            $("#payment-error-cc-2").css('display', 'block');
            ccErrorFlag = false;
        }
        
        if ($('.card-holdername').val() === "") {
            $("#payment-error-cc-3").text(lang['card_holder_invalid']);
            $("#payment-error-cc-3").css('display', 'block');
            ccErrorFlag = false;
        }
        
        if (!ccErrorFlag) {
            return ccErrorFlag;
        }
        
        paymill.createToken({
            number : $('.card-number').val(),
            exp_month : $('.card-expiry-month').val(),
            exp_year : $('.card-expiry-year').val(),
            cvc : $('.card-cvc').val(),
            cardholdername : $('.card-holdername').val(),
            amount_int : $('#paymill_amount').val(),
            currency : $('#paymill_currency').val()
        }, paymillCcResponseHandler);
        
        return false;
    }
    
    $('.card-number').focus(function() {
        fastCheckoutCc = 'false';
        $('.card-number').val('');
    });
    
    $('.card-expiry-month').focus(function() {
        fastCheckoutCc = 'false';
    });
    
    $('.card-expiry-month').focus(function() {
        fastCheckoutCc = 'false';
    });
    
    $('.card-cvc').focus(function() {
        fastCheckoutCc = 'false';
        $('.card-cvc').val('');
    });
    
    $('.card-holdername').focus(function() {
        fastCheckoutCc = 'false';
        $('.card-holdername').val('');
    });
    
    function paymillElv()
    {
        paymillDebug('Paymill ELV: Start form validation');
        
        hideErrorBoxes('elv', 3);
        
        var elvErrorFlag = true;
        
        if (false === paymill.validateAccountNumber($('.account-number').val())) {
            $("#payment-error-elv-1").text(lang['account_number_invalid']);
            $("#payment-error-elv-1").css('display', 'block');
            elvErrorFlag = false;
        }
        
        if (false === paymill.validateBankCode($('.bank-code').val())) {
            $("#payment-error-elv-2").text(lang['sort_code_invalid']);
            $("#payment-error-elv-2").css('display', 'block');
            elvErrorFlag = false;
        }
        
        if ($('.bank-owner').val() === "") {
            $("#payment-error-elv-3").text(lang['account_owner_invalid']);
            $("#payment-error-elv-3").css('display', 'block');
            elvErrorFlag = false; 
        }
        
        if (!elvErrorFlag) {
            return elvErrorFlag;
        }
        
        paymill.createToken({
            number:        $('.account-number').val(),
            bank:          $('.bank-code').val(),
            accountholder: $('.bank-owner').val()
        }, paymillElvResponseHandler);
        
        return false;
    }
    
    $('.account-number').focus(function() {
        fastCheckoutElv = 'false';
        $('.account-number').val('');
    });
    
    
    $('.bank-code').focus(function() {
        fastCheckoutElv = 'false';
        $('.bank-code').val('');
    });
    
    
    $('.bank-owner').focus(function() {
        fastCheckoutElv = 'false';
        $('.bank-owner').val('');
    });
    
    $(".submit").click(function (event) {
        var form = $("#zahlung");
        var payment = $("input[name='Zahlungsart']:checked").val();
        if (payment === $("#paymill_cc").val()) {
            paymillDebug('Paymill Creditcard: Payment method triggered');
            if (fastCheckoutCc == 'false') {
                return paymillCc();
            } else {
                form.append("<input type='hidden' name='paymillToken' value='dummyToken'/>");
            }
        } else if(payment === $("#paymill_elv").val()) {
            paymillDebug('Paymill ELV: Payment method triggered');
            if (fastCheckoutElv == 'false') {
                return paymillElv();
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
