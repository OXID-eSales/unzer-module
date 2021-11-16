[{$smarty.block.parent}]
[{if $edit->oxpayments__oxid->value eq "oscunzer_card" || $edit->oxpayments__oxid->value eq "oscunzer_paypal"}]
    <tr>
        <td class="edittext" width="70">
            [{oxmultilang ident="OSCUNZER_PAYMENT_PROCEDURE"}]
        </td>
        <td class="edittext">
            <input type="hidden" name="editval[oxpayments__oxpaymentprocedure]" value="direct Capture">
            <input class="editinput" type="checkbox" name="editval[oxpayments__oxpaymentprocedure]" value='Authorize & later Capture'
                   [{if $edit->oxpayments__oxpaymentprocedure->rawValue eq "Authorize & later Capture"}]checked[{/if}] [{$readonly}]>
            [{oxinputhelp ident="HELP_OSCUNZER_PAYMENT_PROCEDURE"}]
        </td>
    </tr>
[{/if}]