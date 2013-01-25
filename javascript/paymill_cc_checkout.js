var isCcSubmitted = false;
$(document).ready(function () {
    $("#checkout_payment").submit(function () {
        if (!isCcSubmitted) {
            if ($("input[name=\'payment\'][value=\'paymill_cc\']").prop("checked")) {
                if (!paymill.validateExpiry($("#card-expiry-month option:selected").val(), $("#card-expiry-year option:selected").val())) {
                    alert(cc_expiery_invalid);
                    return false;
                }

                if (!paymill.validateCardNumber($("#card-number").val())) {
                    alert(cc_card_number_invalid);
                    return false;
                }

                if (!paymill.validateCvc($("#card-cvc").val())) {
                    alert(cc_cvc_number_invalid);
                    return false;
                }
            
                paymill.createToken({
                    number: $("#card-number").val(),
                    exp_month: $("#card-expiry-month option:selected").val(), 
                    exp_year: $("#card-expiry-year option:selected").val(), 
                    cvc: $("#card-cvc").val(),
                    amount_int: $("#amount").val(),
                    currency: $("#currency").val()
                }, PaymillCcResponseHandler);
                
                return false; 
            }
        }
    });


    function PaymillCcResponseHandler(error, result) 
    { 
        isCcSubmitted = true;
        if (error) {
            console.log("An API error occured: " + error.apierror);
            return false;
        } else {
            console.log(result.token);
            
            $("#checkout_payment").append("<input type='hidden' name='paymill_token' value='" + result.token + "'/>");
            $("#checkout_payment").submit();
            return false;
        }
    }

});