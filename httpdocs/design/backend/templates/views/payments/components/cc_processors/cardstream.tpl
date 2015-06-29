<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="access_code">{__("preshared_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][passphrase]" id="access_code" value="{$processor_params.passphrase}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][currencycode]" id="currency" value="{$processor_params.currencycode}" class="input-text" size="60" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="transaction_type">{__("country")}:</label>
    <div class="controls">
       <input type="text" name="payment_data[processor_params][countrycode]" id="vendor" value="{$processor_params.countrycode}" class="input-text" size="60" />
    </div>
</div>