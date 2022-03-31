[{block name="checkout_installment_confirm_info"}]
    <div id="installmentconfirmPage">
        <h3 class="blockHead">[{$title}]</h3>
        <article class="cmsContent">
            [{$content}]
        </article>
    </div>
[{/block}]
[{block name="checkout_installment_confirm_summary_row"}]
    <div class="row">
        [{block name="checkout_installment_confirm_summary_table"}]
            <div class="col-xs-4">
                <table id="checkout_installment_confirm_table" class="table table-bordered table-striped">
                    <colgroup>
                        <col class="descCol">
                        <col class="totalCol">
                    </colgroup>
                    <tbody>
                        <tr>
                            <th scope="row">[{oxmultilang ident="OSCUNZER_INSTALLMENT_PURCHASE_AMOUNT" suffix="COLON"}]</th>
                            <td class="text-right">[{$fPruchaseAmount}] [{$uzrCurrency}]</td>
                        </tr>
                        <tr>
                            <th scope="row">[{oxmultilang ident="OSCUNZER_INSTALLMENT_INTEREST_AMOUNT" suffix="COLON"}] ([{$uzrRate}]%)</th>
                            <td class="text-right">[{$fInterestAmount}] [{$uzrCurrency}]</td>
                        </tr>
                        <tr>
                            <th scope="row">[{oxmultilang ident="OSCUNZER_INSTALLMENT_TOTAL" suffix="COLON"}]</th>
                            <td class="text-right">[{$fTotal}] [{$uzrCurrency}]</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        [{/block}]
        [{block name="checkout_installment_confirm_summary_form"}]
            <div class="col-xs-8">
                [{block name="checkout_installment_confirm_input"}]
                    <div class="card bg-light cart-buttons">
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-xs-6">
                                    [{block name="checkout_installment_confirm_button"}]
                                        <form action="[{$oViewConf->getSslSelfLink()}]" method="post" id="orderConfirmInstallmentBottom" class="form-horizontal">
                                            <div class="hidden">
                                                [{$oViewConf->getHiddenSid()}]
                                                [{$oViewConf->getNavFormParams()}]
                                                <input type="hidden" name="cl" value="unzer_installment" />
                                                <input type="hidden" name="fnc" value="[{$oView->getExecuteFnc()}]" />
                                                <input type="hidden" name="challenge" value="[{$challenge}]" />
                                            </div>
                                            <label>
                                                <input id="checkInsallConfirm" type="checkbox" name="ord_instconf" value="0" required />
                                                [{oxmultilang ident="OSCUNZER_INSTALLMENT_PDF" args=$sPdfLink}]
                                            </label>
                                            <button type="submit" class="btn btn-lg btn-primary submitButton nextStep largeButton">
                                                <i class="fa fa-check"></i>[{oxmultilang ident="OSCUNZER_INSTALLMENT_SUBMIT"}]
                                            </button>
                                        </form>
                                    [{/block}]
                                </div>
                                <div class="col-xs-6">
                                    [{block name="checkout_installment_confirm_cancel"}]
                                        <form action="[{$oViewConf->getSslSelfLink()}]" method="post" id="orderConfirmInstallmentCancel" class="form-horizontal">
                                            [{$oViewConf->getHiddenSid()}]
                                            [{$oViewConf->getNavFormParams()}]
                                            <input type="hidden" name="cl" value="unzer_installment">
                                            <input type="hidden" name="fnc" value="cancelInstallment">
                                            <input type="hidden" name="challenge" value="[{$challenge}]">
                                            <button class="btn btn-lg btn-primary submitButton nextStep largeButton float-right" onclick="this.closest('form').submit();return false;">
                                                <i class="fa fa-times"></i>[{oxmultilang ident="OSCUNZER_INSTALLMENT_CANCEL"}]
                                            </button>
                                        </form>
                                    [{/block}]
                                </div>
                            </div>
                        </div>
                    [{/block}]
                </div>
            </div>
        [{/block}]
    </div>
[{/block}]