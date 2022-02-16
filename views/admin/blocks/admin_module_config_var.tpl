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
[{elseif $oModule->getInfo('id') eq "osc-unzer" and $var_group eq "applePay" and $module_var != 'applepay_label'}]
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
[{else}]
    [{$smarty.block.parent}]
[{/if}]