[{assign var="shop" value=$oEmailView->getShop()}]
[{assign var="oViewConf" value=$oEmailView->getViewConfig()}]
[{block name="unzer_email_plain_refund_intro"}]
[{if $isUnzerOwnerMail}]
[{oxmultilang ident="OSCUNZER_REFUND_MAIL_INTRO_OWNER"}]
[{else}]
[{oxmultilang ident="OSCUNZER_REFUND_MAIL_SALUTATION"}] [{$order->oxorder__oxbillfname->getRawValue()}] [{$order->oxorder__oxbilllname->getRawValue()}],

[{oxmultilang ident="OSCUNZER_REFUND_MAIL_INTRO"}]
[{/if}]
[{/block}]

[{block name="unzer_email_plain_refund_details"}]
[{oxmultilang ident="ORDER_NUMBER" suffix="COLON"}] [{$order->oxorder__oxordernr->value}]
[{oxmultilang ident="OSCUNZER_REFUND_MAIL_AMOUNT" suffix="COLON"}] [{oxprice price=$unzerRefundedAmount currency=$currency}]
[{oxmultilang ident="OSCUNZER_REFUND_MAIL_ORDER_TOTAL" suffix="COLON"}] [{oxprice price=$order->oxorder__oxtotalordersum->value currency=$currency}]
[{/block}]

[{block name="unzer_email_plain_refund_note"}]
[{if !$isUnzerOwnerMail}]
[{oxmultilang ident="OSCUNZER_REFUND_MAIL_NOTE"}]
[{/if}]
[{/block}]

[{$shop->oxshops__oxname->getRawValue()}]
[{$shop->oxshops__oxurl->value}]
