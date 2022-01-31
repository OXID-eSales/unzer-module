[{capture append="oxidBlock_content"}]


    [{* ordering steps *}]
    [{include file="page/checkout/inc/steps.tpl" active=4}]

    [{oxifcontent ident="oscunzerinstallmentconfirmation" object="oCont"}]
    [{$oCont->oxcontents__oxcontent->value}]
    [{/oxifcontent}]

    [{block name="checkout_order_btn_confirm_pdf"}]
    <form action="[{$oViewConf->getSslSelfLink()}]" method="post" id="orderConfirmInstallmentBottom" class="form-horizontal">
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
                [{oxifcontent ident="oxrighttocancellegend" object="oContent"}]
                <label>
                    <input id="checkInsallConfirm" type="checkbox" name="ord_instconf" value="0">Ich bin mit dem <a href="[{$sPdfLink}]" target="_blank">Vertrag</a> einverstanden!
                </label>

                [{/oxifcontent}]
                <button type="submit" class="btn btn-lg btn-primary float-right submitButton nextStep largeButton">
                    <i class="fa fa-check"></i>[{oxmultilang ident="OSCUNZER_INSTALLMENT_SUBMIT"}]
                </button>
                [{/block}]

            </div>
        </div>
    </form>
    [{/block}]
    [{/capture}]

[{assign var="template_title" value="REVIEW_YOUR_ORDER"|oxmultilangassign}]
[{include file="layout/page.tpl" title=$template_title location=$template_title}]