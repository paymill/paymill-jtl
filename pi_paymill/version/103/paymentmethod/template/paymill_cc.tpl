<input type='hidden' name='paymill_cc' id='paymill_cc' value='{__paymentId__}'/>
<input type='hidden' name='paymill_amount' id='paymill_amount' value='{__amount__}'/>
<input type='hidden' name='paymill_currency' id='paymill_currency' value='{__currency__}'/>

<div>
    <img src="{__pluginPath__}img/icon_mastercard.png" alt="Mastercard"/>
    <img src="{__pluginPath__}img/icon_visa.png" alt="Mastercard"/>
</div>

<div id="payment-errors-cc" class="payment-error"></div>

<div class="form-row">
    <label>___Credit_Card_Number___</label>
    <input class="card-number" type="text" size="20" value=""/>
</div>

<div class="form-row">
    <label>___Card_Verification_Number___</label>
    <input class="card-cvc" type="text" size="20" value=""/>
</div>

<div class="form-row">
    <label>___Credit_Card_Holder___</label>
    <input class="card-holdername" type="text" size="20" value=""/>
</div>

<div class="form-row">
    <label>___Expiration_Date___</label>
    <select class="card-expiry-month">
        <option>01</option>
        <option>02</option>
        <option>03</option>
        <option>04</option>
        <option>05</option>
        <option>06</option>
        <option>07</option>
        <option>08</option>
        <option>09</option>
        <option>10</option>
        <option>11</option>
        <option>12</option>
    </select>
    <span> / </span>
    <select class="card-expiry-year">
        {__options__}
    </select>
</div>

