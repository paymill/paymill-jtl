var isElvSubmitted = false;
$(document).ready(function () {
    $("#checkout_payment").submit(function () {
        if (!isElvSubmitted) {
            if ($("input[name=\'payment\'][value=\'paymill_elv\']").prop("checked")) {
                if (false === paymill.validateAccountNumber($('#account-number').val())) {
                    alert(elv_account_number_invalid);
                    return false;
                }

                if (false === paymill.validateBankCode($('#bank-code').val())) {
                    alert(elv_bank_code_invalid);
                    return false;
                }

                if ($('#bank-owner').val() === "") {
                    alert(elv_bank_owner_invalid);
                    return false; 
                }
                
                paymill.createToken({
                    number:        $('#account-number').val(),
                    bank:          $('#bank-code').val(),
                    accountholder: $('#bank-owner').val()
                }, PaymillElvResponseHandler);
                
                return false;
            }
        }
    });


    function PaymillElvResponseHandler(error, result) 
    { 
        isElvSubmitted = true;
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