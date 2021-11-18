[{$smarty.block.parent}]
[{if $payment}]
    [{if $payment->getId() === 'oscunzer_sepa'}]
    [{assign var=sMerchantName value=$oView->getShopCompanyName()}]
    [{oxifcontent ident="oscunzersepamandatetext" object="oContent"}]

            <label>
                <input id="oscunzersepaagreement" type="checkbox" name="oscunzersepaagreement" value="1">[{$oContent->oxcontents__oxcontent->value}]
            </label>
    [{oxscript add="$('#oscunzersepaagreement').click(function(){ $('input[name=oscunzersepaagreement]').val($(this).is(':checked') ? '1' : '0');});"}]
    [{/oxifcontent}]
    [{/if}]
    [{/if}]
