<div class="container form">
    <fieldset>
        <div id="payment-errors-elv" class="payment-error"></div>
        </label>
        <br/>
        <label>
            <div class="form-row">
                <label>___IBAN___</label>
                <input class="iban" id="paymill-iban" type="text" size="20" value="{__elv_iban__}"/>
            </div>
            <div id="payment-error-elv-1" class="payment-error"></div>
        </label>
        <label>
            <div class="form-row">
                <label>___BIC___</label>
                <input class="bic" id="paymill-bic" type="text" size="20" value="{__elv_bic__}"/>
            </div>
            <div id="payment-error-elv-2" class="payment-error"></div>
        </label>
        <label>
            <div class="form-row">
                <label>___Account_Owner___</label>
                <input class="bank-owner-sepa" id="paymill-bank-owner-sepa" type="text" size="20" value="{__elv_owner__}"/>
                <div id="payment-error-elv-3" class="payment-error"></div>
            </div>
        </label>
    </fieldset>
</div>