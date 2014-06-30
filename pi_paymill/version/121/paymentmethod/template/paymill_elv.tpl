<div class="container form">
    <fieldset>
        <legend>Zahlungsinformationen</legend>
        </label>
        <div id="payment-errors-elv" class="payment-error"></div>
        <div class="form-row">
            <label>___Account_Number___ / ___IBAN___</label>
            <input class="account-number form-row-big" id="paymill-account-number" type="text" size="20" value="{__elv_number_iban__}"/>
        </div>
        <div class="form-row">
            <label>___Sort_Code___ / ___BIC___</label>
            <input class="bank-code form-row-big" id="paymill-bank-code" type="text" size="20" value="{__elv_bankcode_bic__}"/>
        </div>
        <div class="form-row">
            <label>___Account_Owner___</label>
            <input class="bank-owner form-row-big" id="paymill-bank-owner" type="text" size="20" value="{__elv_owner__}"/>
        </div>
    </fieldset>
</div>