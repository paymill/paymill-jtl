<div class="container form">
    <fieldset>
        <input type='hidden' name='paymill_amount' id='paymill_amount' value='{__amount__}'/>
        <input type='hidden' name='paymill_currency' id='paymill_currency' value='{__currency__}'/>
        <div id="payment-errors-cc" class="payment-error"></div>
        </label>
        <br/>
        <label>
            <div class="form-row">
                <label>___Credit_Card_Number___</label>
                <input class="card-number {__brand__}" id="paymill-card-number" type="text" size="20" value="{__cc_number__}"/>
            </div>
            <div id="payment-error-cc-1" class="payment-error"></div>
        </label>
        <label>
            <div class="form-row">
                <label>___Card_Verification_Number___<span class="tooltip" title="__CVC_TOOLTIP__">?</span></label>
                <input class="card-cvc" id="paymill-card-cvc" type="text" size="20" value="{__cc_cvc__}"/>
            </div>
            <div id="payment-error-cc-2" class="payment-error"></div>
        </label>
        <label>
            <div class="form-row">
                <label>___Credit_Card_Holder___</label>
                <input class="card-holdername"  id="paymill-card-holdername" type="text" size="20" value="{__cc_holder__}"/>
            </div>
            <div id="payment-error-cc-3" class="payment-error"></div>
        </label>

        <div class="form-row">
            <label>___Expiration_Date___</label>
            <br/>
            <select class="card-expiry-month" id="paymill-card-expiry-month">
                {__options_month__}
            </select>
            <span> / </span>
            <select class="card-expiry-year" id="paymill-card-expiry-year">
                {__options_year__}
            </select>
            <div id="payment-error-cc-4" class="payment-error"></div>
        </div>
    </fieldset>
</div>
