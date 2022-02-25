[{*[{$module_var}]*}]
[{if $oModule->getInfo('id') eq "osc-unzer" and $var_group eq "merchant" and $module_var eq "registeredWebhook"}]
    [{if $shobWebhookButtons}]
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
[{elseif $oModule->getInfo('id') eq "osc-unzer" and $var_group eq "applePay"}]
    [{if $module_var eq "applepay_merchant_capabilities" or $module_var eq "applepay_networks"}]
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
                    [{* TODO we need central point for activation *}]
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
    [{elseif $module_var eq "applepay_merchant_cert_key" or $module_var eq "applepay_merchant_cert"}]
        <dl>
            <dt>
                <textarea class="txt"
                          style="width: 250px; height: 200px;border: 1px solid #ccc;border-radius: 4px;box-shadow: 0 1px 1px rgba(0, 0, 0, .075) inset;color: #555;min-height: 15px;line-height: 1.42857;padding: 3px;font-size: 12px;transition: border-color 0.15s ease-in-out 0s, box-shadow 0.15s ease-in-out 0s;background: #fff;"
                          name="confstrs[[{$module_var}]]" [{$readonly}]>[{$confstrs.$module_var}]</textarea>
                [{oxinputhelp ident="HELP_SHOP_MODULE_`$module_var`"}]
            </dt>
            <dd>
                [{oxmultilang ident="SHOP_MODULE_`$module_var`"}]
            </dd>
        </dl>
        <div class="spacer"></div>
    [{else}]
        [{$smarty.block.parent}]
    [{/if}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]