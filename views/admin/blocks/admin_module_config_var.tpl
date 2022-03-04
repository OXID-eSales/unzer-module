[{if $oModule->getInfo('id') eq "osc-unzer" and $var_group eq "unzermerchant" and $module_var eq "registeredWebhook"}]
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
                    <input type="submit" class="confinput" name="deletewebhook" value="[{oxmultilang ident="SHOP_MODULE_DELETE_WEBHOOK"}]" onClick="Javascript:document.module_configuration.fnc.value='deleteWebhook'" [{$readonly}]>
                </dt>
                <div class="spacer"></div>
            </dl>
        [{else}]
            <dl>
                <dt>
                    <input type="submit" class="confinput" name="registerwebhook" value="[{oxmultilang ident="SHOP_MODULE_REGISTER_WEBHOOK"}]" onClick="Javascript:document.module_configuration.fnc.value='registerWebhook'" [{$readonly}]>
                </dt>
                <div class="spacer"></div>
            </dl>
        [{/if}]
    [{else}]
        <dl>
            <dt>
                <input disabled type="submit" value="[{oxmultilang ident="SHOP_MODULE_REGISTER_WEBHOOK"}]" [{$readonly}]>
            </dt>
            <dd>
                [{oxmultilang ident="SHOP_MODULE_WEBHOOK_NO_UNZER"}]
            </dd>
            <div class="spacer"></div>
        </dl>
    [{/if}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]