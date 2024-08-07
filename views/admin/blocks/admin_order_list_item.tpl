[{assign var="isUnzerPayment" value=$listitem->oxorder__oxpaymenttype->value|stripos:"oscunzer"}]
[{if $isUnzerPayment !== false}]
    [{if $listitem->oxorder__oxstorno->value == 1}]
        [{assign var="listclass" value=listitem3}]
    [{else}]
        [{if $listitem->blacklist == 1}]
            [{assign var="listclass" value=listitem3}]
        [{else}]
            [{assign var="listclass" value=listitem$blWhite}]
        [{/if}]
    [{/if}]
    [{if $listitem->getId() == $oxid}]
        [{assign var="listclass" value=listitem4}]
    [{/if}]
    <td valign="top" class="[{$listclass}] order_time" height="15"><div class="listitemfloating">&nbsp;<a href="Javascript:top.oxid.admin.editThis('[{$listitem->oxorder__oxid->value}]');" class="[{$listclass}]">[{$listitem->oxorder__oxorderdate|oxformdate:'datetime':true}]</a></div></td>
    <td valign="top" class="[{$listclass}] payment_date" height="15"><div class="listitemfloating"><a href="Javascript:top.oxid.admin.editThis('[{$listitem->oxorder__oxid->value}]');" class="[{$listclass}]">[{$listitem->oxorder__oxpaid|oxformdate}]</a></div></td>
    <td valign="top" class="[{$listclass}] order_no" height="15">
        <div class="listitemfloating"><a href="Javascript:top.oxid.admin.editThis('[{$listitem->oxorder__oxid->value}]');" class="[{$listclass}]">
            [{$listitem->oxorder__oxordernr->value}] [{if $listitem->oxorder__oxunzerordernr->value}] ([{oxmultilang ident='OSCUNZER_TRANSACTION_ORDERNR' suffix='COLON'}] [{$listitem->oxorder__oxunzerordernr->value}]) [{/if}]
        </a></div>
    </td>
    <td valign="top" class="[{$listclass}] first_name" height="15"><div class="listitemfloating"><a href="Javascript:top.oxid.admin.editThis('[{$listitem->oxorder__oxid->value}]');" class="[{$listclass}]">[{$listitem->oxorder__oxbillfname->value}]</a></div></td>
    <td valign="top" class="[{$listclass}] last_name" height="15"><div class="listitemfloating"><a href="Javascript:top.oxid.admin.editThis('[{$listitem->oxorder__oxid->value}]');" class="[{$listclass}]">[{$listitem->oxorder__oxbilllname->value}]</a></div></td>
    <td class="[{$listclass}]">
        [{if !$readonly}]
            <a href="Javascript:top.oxid.admin.deleteThis('[{$listitem->oxorder__oxid->value}]');" class="delete" id="del.[{$_cnt}]" [{include file="help.tpl" helpid=item_delete}]></a>
            <a href="Javascript:StornoThisArticle('[{$listitem->oxorder__oxid->value}]');" class="pause" id="pau.[{$_cnt}]" [{include file="help.tpl" helpid=item_storno}]></a>
        [{/if}]
    </td>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
