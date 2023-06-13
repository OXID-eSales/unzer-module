[{capture append="oxidBlock_content"}]

    [{* ordering steps *}]
    [{include file="page/checkout/inc/steps.tpl" active=4}]
    [{oxcontent ident="oscunzerinstallmentconfirmation" assign="content"}]
    [{oxcontent ident="oscunzerinstallmentconfirmation" field="oxtitle" assign="title"}]

    [{block name="checkout_installment_confirm_main"}]
        [{if $oViewConf->isFlowCompatibleTheme()}]
            [{include file='@osc-unzer/frontend/tpl/order/unzer_installment_confirm_flow'}]
        [{else}]
            [{include file='@osc-unzer/frontend/tpl/order/unzer_installment_confirm_wave'}]
        [{/if}]
    [{/block}]
[{/capture}]
[{include file="layout/page.tpl" title=$title location=$title}]