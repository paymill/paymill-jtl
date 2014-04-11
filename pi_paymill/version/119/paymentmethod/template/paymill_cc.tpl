<div class="container form">
    <fieldset>
        <legend>Zahlungsinformationen</legend>
        <input type='hidden' name='paymill_amount' id='paymill_amount' value='{__amount__}'/>
        <input type='hidden' name='paymill_currency' id='paymill_currency' value='{__currency__}'/>
        <div id="payment-errors-cc" class="payment-error"></div>
        </label>
        <br/>
        <div class="form-row">
            <div style="float: left; padding-right: 10px;">
                <span class="form-row-label-big">___Credit_Card_Number___</span>
                <br/>
                <input class="card-number {__brand__} form-row-big" id="paymill-card-number" type="text" size="20" value="{__cc_number__}"/>
            </div>
            <span class="form-row-label-small">___Card_Verification_Number___<span class="tooltip" title="__CVC_TOOLTIP__">?</span></span>
            <br/>
            <input class="card-cvc form-row-small" id="paymill-card-cvc" type="text" size="20" value="{__cc_cvc__}"/>
        </div>
        <div class="form-row">    
            <div style="float: left; padding-right: 10px;">
                <span class="form-row-label-big">___Credit_Card_Holder___</span>
                <br/>
                <input class="card-holdername form-row-big"  id="paymill-card-holdername" type="text" size="20" value="{__cc_holder__}"/>
            </div>
            <span class="form-row-label-small">___Expiration_Date___</span>
            <br/>
            <input class="card-expiry form-row-small"  id="paymill-card-expiry" type="text" size="5" maxlength="5" value="{__cc_expiry__}"/>
        </div>
    </fieldset>
</div>
