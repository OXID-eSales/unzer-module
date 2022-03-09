[{if $oModule->getInfo('id') eq "osc-unzer"}]
    [{if $var_group eq "unzermerchant" and $module_var eq "registeredWebhook"}]
        [{if $showWebhookButtons}]
            [{if $registeredwebhook}]
                <dl>
                    <dt>
                        <input size="128" type="text" value="[{$registeredwebhook}]" disabled>
                    </dt>
                    <dd>
                        [{oxmultilang ident="SHOP_MODULE_WEBHOOK"}]
                    </dd>
                    <div class="spacer"></div>
                </dl>
                <dl>
                    <dt>
                        <input type="submit" class="confinput" name="deletewebhook"
                               value="[{oxmultilang ident="SHOP_MODULE_DELETE_WEBHOOK"}]"
                               onClick="Javascript:document.module_configuration.fnc.value='deleteWebhook'" [{$readonly}]>
                    </dt>
                    <div class="spacer"></div>
                </dl>
            [{else}]
                <dl>
                    <dt>
                        <input type="submit" class="confinput" name="registerwebhook"
                               value="[{oxmultilang ident="SHOP_MODULE_REGISTER_WEBHOOK"}]"
                               onClick="Javascript:document.module_configuration.fnc.value='registerWebhook'" [{$readonly}]>
                    </dt>
                    <div class="spacer"></div>
                </dl>
            [{/if}]
        [{else}]
            <dl>
                <dt>
                    <input disabled type="submit"
                           value="[{oxmultilang ident="SHOP_MODULE_REGISTER_WEBHOOK"}]" [{$readonly}]>
                </dt>
                <dd>
                    [{oxmultilang ident="SHOP_MODULE_WEBHOOK_NO_UNZER"}]
                </dd>
                <div class="spacer"></div>
            </dl>
        [{/if}]
    [{elseif $var_group eq "unzerapplepay"}]
        [{if $module_var eq "applepay_payment_certs_processed"}]
            [{assign var="hidePaymentProcessingTextareas" value=false}]
            [{if $oView->getApplePayPaymentProcessingCertExists() && $oView->getApplePayPaymentProcessingKeyExists()}]
                [{assign var="hidePaymentProcessingTextareas" value=true}]
            [{/if}]
            <dl class="js-payment-cert-list" [{if $hidePaymentProcessingTextareas}]style="display: none"[{/if}]>
                <dt>
                        <textarea class="txt"
                                  style="width: 250px; height: 200px;border: 1px solid #ccc;border-radius: 4px;box-shadow: 0 1px 1px rgba(0, 0, 0, .075) inset;color: #555;min-height: 15px;line-height: 1.42857;padding: 3px;font-size: 12px;transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;background: #fff;"
                                  name="applePayPaymentProcessingCert"></textarea>
                </dt>
                <dd>
                    [{oxmultilang ident="SHOP_MODULE_APPLE_PAY_PAYMENT_PROCESSING_CERT"}]
                </dd>
                <div class="spacer"></div>
            </dl>
            <dl class="js-payment-cert-list" [{if $hidePaymentProcessingTextareas}]style="display: none"[{/if}]>
                <dt>
                        <textarea class="txt"
                                  style="width: 250px; height: 200px;border: 1px solid #ccc;border-radius: 4px;box-shadow: 0 1px 1px rgba(0, 0, 0, .075) inset;color: #555;min-height: 15px;line-height: 1.42857;padding: 3px;font-size: 12px;transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;background: #fff;"
                                  name="applePayPaymentProcessingCertKey"></textarea>
                </dt>
                <dd>
                    [{oxmultilang ident="SHOP_MODULE_APPLE_PAY_PAYMENT_PROCESSING_CERT_KEY"}]
                </dd>
                <div class="spacer"></div>
            </dl>
            <dl class="js-payment-cert-list" [{if $hidePaymentProcessingTextareas}]style="display: none;"[{/if}]>
                <dt>
                    <input type="submit" class="confinput" name="transferApplePayPaymentProcessingData"
                           value="[{oxmultilang ident="SHOP_MODULE_TRANSFER_APPLE_PAY_PAYMENT_DATA"}]"
                           style="margin-bottom: 25px;"
                           onClick="Javascript:document.module_configuration.fnc.value='transferApplePayPaymentProcessingData'" [{$readonly}]>
                </dt>
                <div class="spacer"></div>
            </dl>
            <dl class="js-show-payment-cert-list" style="[{if !$hidePaymentProcessingTextareas}]display: none;[{else}]margin-bottom: 25px;"[{/if}]>
                <dd style="color: green; padding-left: 5px;">
                    [{oxmultilang ident="SHOP_MODULE_APPLE_PAY_PAYMENT_CERTS_PROCESSED"}]
                </dd>
                <dt>
                    <input class="js-show-payment-certs confinput" type="submit" value="[{oxmultilang ident="SHOP_MODULE_RETRANSFER_APPLE_PAY_PAYMENT_DATA"}]">
                </dt>
                <div class="spacer"></div>
            </dl>
            <script>
                document.querySelector('.js-show-payment-certs').addEventListener('click', e => {
                    e.preventDefault();

                    document.querySelectorAll('.js-payment-cert-list').forEach(el => {
                        el.style.display = '';
                    })
                    document.querySelector('.js-show-payment-cert-list').style.display = 'none';
                })
            </script>
        [{elseif $module_var eq "applepay_merchant_cert_key" or $module_var eq "applepay_merchant_cert"}]
            <dl>
                <dt>
                    [{if $module_var eq "applepay_merchant_cert_key"}]
                        <textarea class="txt"
                                  style="width: 250px; height: 200px;border: 1px solid #ccc;border-radius: 4px;box-shadow: 0 1px 1px rgba(0, 0, 0, .075) inset;color: #555;min-height: 15px;line-height: 1.42857;padding: 3px;font-size: 12px;transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;background: #fff;"
                                  name="applePayMerchantCertKey" [{$readonly}]>[{$applePayMerchantCertKey}]</textarea>
                    [{else}]
                        <textarea class="txt"
                                  style="width: 250px; height: 200px;border: 1px solid #ccc;border-radius: 4px;box-shadow: 0 1px 1px rgba(0, 0, 0, .075) inset;color: #555;min-height: 15px;line-height: 1.42857;padding: 3px;font-size: 12px;transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;background: #fff;"
                                  name="applePayMerchantCert" [{$readonly}]>[{$applePayMerchantCert}]</textarea>
                    [{/if}]
                    [{oxinputhelp ident="HELP_SHOP_MODULE_`$module_var`"}]
                </dt>
                <dd>
                    [{oxmultilang ident="SHOP_MODULE_`$module_var`"}]
                </dd>
            </dl>
            <div class="spacer"></div>
        [{elseif $module_var eq "applepay_merchant_capabilities" or $module_var eq "applepay_networks"}]
            <dl>
                <dd>
                    [{oxmultilang ident="SHOP_MODULE_`$module_var`"}]
                    [{oxinputhelp ident="HELP_SHOP_MODULE_`$module_var`"}]
                </dd>
                [{if $module_var eq "applepay_merchant_capabilities"}]
                    <dd>
                        [{foreach from=$applePayMC item="setting" key="settingName"}]
                            <label>
                                <input type="hidden" name="applePayMC[[{$settingName}]]" value="0">
                                <input type="checkbox" name="applePayMC[[{$settingName}]]"
                                       value="1"[{if $setting}] checked="checked"[{/if}]>
                                [{oxmultilang ident="SHOP_MODULE_`$module_var`_`$settingName`"}]
                            </label>
                            <br>
                        [{/foreach}]
                    </dd>
                    <div class="spacer"></div>
                [{elseif $module_var eq "applepay_networks"}]
                    <dd>
                        [{foreach from=$applePayNetworks item="setting" key="settingName"}]
                            <label>
                                <input type="hidden" name="applePayNetworks[[{$settingName}]]" value="0">
                                <input type="checkbox" name="applePayNetworks[[{$settingName}]]"
                                       value="1"[{if $setting}] checked="checked"[{/if}]>
                                [{oxmultilang ident="SHOP_MODULE_`$module_var`_`$settingName`"}]
                            </label>
                            <br>
                        [{/foreach}]
                    </dd>
                [{/if}]
                <div class="spacer"></div>
            </dl>
        [{/if}]
    [{else}]
        [{$smarty.block.parent}]
    [{/if}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]