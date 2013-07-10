{__js__}
    <input type='hidden' name='paymill_elv' id="paymill_elv" value='{__paymentId__}'/>

    <div class="elv-logo">
        <img src="{__pluginPath__}/img/icon_elv.png" alt="Elektronisches Lastschriftverfahren"/>
    </div>
</label>
<label>
    <div class="form-row">
        <label>___Account_Number___</label>
        <input class="account-number" type="text" size="20" value=""/>
    </div>
    <div id="payment-error-elv-1" class="payment-error"></div>
</label>
<label>
    <div class="form-row">
        <label>___Sort_Code___</label>
        <input class="bank-code" type="text" size="20" value=""/>
    </div>
    <div id="payment-error-elv-2" class="payment-error"></div>
</label>
<label>
    <div class="form-row">
        <label>___Account_Owner___</label>
        <input class="bank-owner" type="text" size="20" value=""/>
        <div id="payment-error-elv-3" class="payment-error"></div>
    </div>
</label>
