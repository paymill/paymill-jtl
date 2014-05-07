<div class="container form">
    <fieldset>
        <legend>Zahlungsinformationen</legend>
        </label>
        <div id="payment-errors-elv" class="payment-error"></div>
        <div class="form-row">
            <label>___IBAN___</label>
            <input class="iban form-row-big" id="paymill-iban" type="text" size="20" value="{__elv_iban__}"/>
        </div>
        <div class="form-row">
            <label>___BIC___</label>
            <input class="bic form-row-big" id="paymill-bic" type="text" size="20" value="{__elv_bic__}"/>
        </div>
        <div class="form-row">
            <label>___Account_Owner___</label>
            <input class="bank-owner-sepa form-row-big" id="paymill-bank-owner-sepa" type="text" size="20" value="{__elv_owner__}"/>
        </div>
    </fieldset>
</div>