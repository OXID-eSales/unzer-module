[{capture append="oxidBlock_content"}]


[{* ordering steps *}]
[{include file="page/checkout/inc/steps.tpl" active=4}]

[{oxifcontent ident="uzrpdfconf" object="oCont"}]
    [{$oCont->oxcontents__oxcontent->value}]
[{else}]
   Bestätigen...
    <br>Gesamtbetrag [{$fPruchaseAmount}] [{$uzrCurrency}]
    <br>Zinsen [{$fInterestAmount}] [{$uzrCurrency}] ([{$uzrRate}] %)
    <br>
    <a href="[{$sPdfLink}]" target="_blank">Pdf-Details</a>
[{/oxifcontent}]

[{block name="checkout_order_btn_confirm_pdf"}]
    <form action="[{$oViewConf->getSslSelfLink()}]" method="post" id="orderConfirmAgbBottom" class="form-horizontal">
        <div class="hidden">
            [{$oViewConf->getHiddenSid()}]
            [{$oViewConf->getNavFormParams()}]
            <input type="hidden" name="cl" value="unzer_installment">
            <input type="hidden" name="fnc" value="[{$oView->getExecuteFnc()}]">
            <input type="hidden" name="challenge" value="[{$challenge}]">
        </div>

        <div class="card bg-light cart-buttons">
            <div class="card-body">

                [{block name="checkout_order_btn_confirm_pdf_bottom"}]
                <button type="submit" class="btn btn-lg btn-primary float-right submitButton nextStep largeButton">
                    <i class="fa fa-check"></i> Einverstanden
                </button>
                [{/block}]

            </div>
        </div>
    </form>
    [{/block}]
    [{/capture}]

[{assign var="template_title" value="REVIEW_YOUR_ORDER"|oxmultilangassign}]
[{include file="layout/page.tpl" title=$template_title location=$template_title}]