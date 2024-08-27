[{assign var="template_title" value="MY_ACCOUNT"|oxmultilangassign}]
[{capture append="oxidBlock_content"}]
<h3>[{oxmultilang ident="OSCUNZER_SAVED_PAYMENTS"}]</h3>
[{if $unzerPaymentType != false }]
    [{foreach from=$unzerPaymentType item="setting" key="type"}]
    [{if $type != 'paypal' && $type != 'sepa'}]
    <table class="table">
        <thead>
        <tr>
            <th scope="col">[{oxmultilang ident="OSCUNZER_CARD_NUMBER"}]</th>
            <th scope="col">[{oxmultilang ident="OSCUNZER_EXPIRY_DATE"}]</th>
            <th scope="col">[{oxmultilang ident="OSCUNZER_BRAND"}]</th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>
        [{assign var="counter" value=0}]

        [{foreach from=$setting item="paymentType" key=savedPaymentUserId }]
            <tr>
                <th scope="row">[{$paymentType.number}]</th>
                <td>[{$paymentType.expiryDate}]</td>
                <td>[{if $type == 'invalid_payment_method'}][{oxmultilang ident="OSCUNZER_INVALID_PAYMENT_METHOD"}][{else}][{$type}][{/if}]</td>

                <td style="text-align: right;">
                    <form name="uzr" id="uzr_collect" action="[{$oViewConf->getSelfLink()}]" method="post">
                        <input type="hidden" name="cl" value="unzer_saved_payments">
                        <input type="hidden" name="fnc" value="deletePayment">
                        <input type="hidden" name="savedPaymentUserId" value="[{$savedPaymentUserId}]">
                        <button type="submit" class="btn btn-danger delete-cc" name="deletePayment">[{oxmultilang ident="DD_DELETE"}]</button>
                    </form>
                </td>
            </tr>
            [{/foreach}]


        </tbody>
    </table>
    [{/if}]
    [{if $type == 'paypal'}]
    <table class="table">
        <thead>
        <tr>
            <th scope="col">[{oxmultilang ident="EMAIL"}]</th>
            <th scope="col">[{oxmultilang ident="OSCUNZER_BRAND"}]</th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>
        [{assign var="counter" value=0}]

        [{foreach from=$setting item="paymentType" key=savedPaymentUserId }]
            <tr>
                <th scope="row">[{$paymentType.email}]</th>
                <td>[{$type}]</td>
                <td style="text-align: right;">
                    <form name="uzr" id="uzr_collect" action="[{$oViewConf->getSelfLink()}]" method="post">
                        <input type="hidden" name="cl" value="unzer_saved_payments">
                        <input type="hidden" name="fnc" value="deletePayment">
                        <input type="hidden" name="savedPaymentUserId" value="[{$savedPaymentUserId}]">
                        <button type="submit" class="btn btn-danger delete-paypal">[{oxmultilang ident="DD_DELETE"}]</button>
                    </form>
                </td>
            </tr>
            [{/foreach}]


        </tbody>
    </table>
    [{/if}]
    [{if $type == 'sepa'}]
    <table class="table">
        <thead>
        <tr>
            <th scope="col">[{oxmultilang ident="OSCUNZER_HOLDER"}]</th>
            <th scope="col">[{oxmultilang ident="OSCUNZER_IBAN"}]</th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>

        [{foreach from=$setting item="paymentType" key=savedPaymentUserId }]
            <tr>
                <th scope="row">[{$paymentType.holder}]</th>
                <td>[{$paymentType.iban}]</td>
                <td style="text-align: right;">
                    <form name="uzr" id="uzr_collect" action="[{$oViewConf->getSelfLink()}]" method="post">
                        <input type="hidden" name="cl" value="unzer_saved_payments">
                        <input type="hidden" name="fnc" value="deletePayment">
                        <input type="hidden" name="savedPaymentUserId" value="[{$savedPaymentUserId}]">
                        <button type="submit" class="btn btn-danger delete-sepa">[{oxmultilang ident="DD_DELETE"}]</button>
                    </form>
                </td>
            </tr>
            [{/foreach}]


        </tbody>
    </table>
    [{/if}]
    [{/foreach}]
    [{else}]
    <p>[{oxmultilang ident="OSCUNZER_SAVE_PAYMENT_NO_PAYMENTS"}]</p>

    [{/if}]
    [{/capture}]
[{capture append="oxidBlock_sidebar"}]
    [{include file="page/account/inc/account_menu.tpl"}]
    [{/capture}]
[{include file="layout/page.tpl" sidebar="Left"}]
