[{include file="modules/osc/unzer/unzer_assets.tpl"}]
[{assign var="invadr" value=$oView->getInvoiceAddress()}]
[{assign var="isCompany" value=false}]
[{assign var="invadr" value=$oView->getInvoiceAddress()}]
[{if isset( $invadr.oxuser__oxbirthdate.month )}]
    [{assign var="iBirthdayMonth" value=$invadr.oxuser__oxbirthdate.month}]
    [{elseif $oxcmp_user->oxuser__oxbirthdate->value && $oxcmp_user->oxuser__oxbirthdate->value != "0000-00-00"}]
    [{assign var="iBirthdayMonth" value=$oxcmp_user->oxuser__oxbirthdate->value|regex_replace:"/^([0-9]{4})[-]/":""|regex_replace:"/[-]([0-9]{1,2})$/":""}]
    [{else}]
    [{assign var="iBirthdayMonth" value=0}]
    [{/if}]

[{if isset( $invadr.oxuser__oxbirthdate.day )}]
    [{assign var="iBirthdayDay" value=$invadr.oxuser__oxbirthdate.day}]
    [{elseif $oxcmp_user->oxuser__oxbirthdate->value && $oxcmp_user->oxuser__oxbirthdate->value != "0000-00-00"}]
    [{assign var="iBirthdayDay" value=$oxcmp_user->oxuser__oxbirthdate->value|regex_replace:"/^([0-9]{4})[-]([0-9]{1,2})[-]/":""}]
    [{else}]
    [{assign var="iBirthdayDay" value=0}]
    [{/if}]

[{if isset( $invadr.oxuser__oxbirthdate.year )}]
    [{assign var="iBirthdayYear" value=$invadr.oxuser__oxbirthdate.year}]
    [{elseif $oxcmp_user->oxuser__oxbirthdate->value && $oxcmp_user->oxuser__oxbirthdate->value != "0000-00-00"}]
    [{assign var="iBirthdayYear" value=$oxcmp_user->oxuser__oxbirthdate->value|regex_replace:"/[-]([0-9]{1,2})[-]([0-9]{1,2})$/":""}]
    [{else}]
    [{assign var="iBirthdayYear" value=0}]
    [{/if}]

[{if ($oxcmp_user->oxuser__oxcompany->value || ($invadr && $invadr->oxaddress__oxcompany->value))}]
    [{assign var="isCompany" value=true}]
[{/if}]
[{if $isCompany}]
    [{assign var="customerType" value="B2B"}]
    [{else}]
    [{assign var="customerType" value="B2C"}]
[{/if}]
<form id="payment-form-installment" class="unzerUI form unzerUI-PaylaterInstallment__form" novalidate>
    <br />
    <div id="unzer-installment">
        <!-- The Installment Secured field UI Element will be inserted here -->
    </div>
    <div id="oxDateForInstallment" class="form-group row oxDate [{if !$iBirthdayMonth || !$iBirthdayDay || !$iBirthdayYear}]text-danger[{/if}]">
        <div class="col-12 col-lg-12">
            <label for="oxDay">[{oxmultilang ident="BIRTHDATE"}]</label>
        </div>

        <div class="col-3 col-lg-3 unzerUI input">
            <input id="birthdate_day" class="oxDay form-control" type="text" maxlength="2" value="[{if $iBirthdayDay > 0}][{$iBirthdayDay}][{/if}]"
                   placeholder="[{oxmultilang ident="DAY"}]" required>
        </div>
        <div class="col-6 col-lg-3">
            <select id="birthdate_month" class="oxMonth form-control selectpicker" required>
                <option value="" label="-">-</option>
                [{section name="month" start=1 loop=13}]
                <option value="[{$smarty.section.month.index}]" label="[{$smarty.section.month.index}]" [{if $iBirthdayMonth|intval == $smarty.section.month.index}] selected="selected" [{/if}]>
                    [{oxmultilang ident="MONTH_NAME_"|cat:$smarty.section.month.index}]
                </option>
                [{/section}]
            </select>
        </div>
        <div class="col-3 col-lg-3 unzerUI input">
            <input id="birthdate_year" class="oxYear form-control" type="text" maxlength="4" value="[{if $iBirthdayYear}][{$iBirthdayYear}][{/if}]"
                   placeholder="[{oxmultilang ident="YEAR"}]" required>
        </div>
        <div class="offset-lg-3 col-lg-9 col-12">
            <div class="help-block">
                <p class="text-danger [{if $iBirthdayMonth && $iBirthdayDay && $iBirthdayYear}]d-none hidden[{/if}]">[{oxmultilang ident="DD_FORM_VALIDATION_REQUIRED"}]</p>
            </div>
        </div>
    </div>
    <div class="field" id="error-holder" style="color: #9f3a38"> </div>
    <button id="continue-button" class="unzerUI primary button fluid" type="submit" style="display: none" disabled>
        [{oxmultilang ident="OSCUNZER_INSTALLMENT_CONTINUE"}]
    </button>
</form>
[{assign var="total" value=$oxcmp_basket->getPrice()}]

[{assign var=totalgross value=$oxcmp_basket->getPrice()}]
[{assign var=uzrcurrency value=$currency->name}]
[{if false}] <script>[{/if}]
[{capture assign="unzerInstallmentJS"}]

        // Create an Unzer instance with your public key
        let unzerInstance = new unzer('[{$unzerpub}]', {locale: "[{$unzerLocale}]"});

        let InstallmentSecured = unzerInstance.PaylaterInstallment();



        InstallmentSecured.create({
            containerId: 'unzer-installment',
            amount: [{$totalgross->getPrice()}],
            currency: '[{$uzrcurrency}]',
            country:'[{$oView->getUserCountryIso()}]',
            threatMetrixId:'[{$unzerThreatMetrixSessionID}]'

        });

        $( '#orderConfirmAgbBottom' ).submit(function( event ) {
            if(!$( '#orderConfirmAgbBottom' ).hasClass("submitable")){
                event.preventDefault();
                $( '#payment-form-installment' ).submit();
            }
        });

        // Handling payment form submission
        $( "#payment-form-installment" ).submit(function( event ) {
            event.preventDefault();
          //  if($('.unzerUI-installment-secured__selected-rate').length){
                InstallmentSecured.createResource()
                .then(function(data) {
                    let hiddenInput = $(document.createElement('input'))
                    .attr('type', 'hidden')
                    .attr('name', 'paymentData')
                    .val(JSON.stringify(data));
                $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput);
                let hiddenCustomerType = $(document.createElement('input'))
                    .attr('type', 'hidden')
                    .attr('name', 'unzer_customer_type')
                    .val('[{$customerType}]');
                $('#orderConfirmAgbBottom').find(".hidden").append(hiddenCustomerType);
                let hiddenInputBirthdate = $(document.createElement('input'))
                    .attr('type', 'hidden')
                    .attr('name', 'birthdate')
                    .val($('#birthdate_year').val()+'-'+$('#birthdate_month').val()+'-'+$('#birthdate_day').val());
                $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInputBirthdate);
                $( '#orderConfirmAgbBottom' ).addClass("submitable");
                $( "#orderConfirmAgbBottom" ).submit();
            })
            .catch(function(error) {
                $('#error-holder').html(error.message);
                $('html, body').animate({
                    scrollTop: $("#orderPayment").offset().top - 150
                    }, 350);
                });
            //}
        });
[{/capture}]
[{oxscript add=$unzerInstallmentJS}]
