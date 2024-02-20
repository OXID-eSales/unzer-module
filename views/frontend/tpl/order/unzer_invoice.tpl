[{include file="modules/osc/unzer/unzer_assets.tpl"}]
<script type="text/javascript" async
        src="https://h.online-metrix.net/fp/tags.js?org_id=363t8kgq&session_id=[{$unzerThreatMetrixSessionID}]">
</script>
[{assign var="invadr" value=$oView->getInvoiceAddress()}]
[{assign var="iBirthdayMonth" value=0}]
[{assign var="iBirthdayDay" value=0}]
[{assign var="iBirthdayYear" value=0}]
[{assign var="isCompany" value=false}]
[{assign var="isBoth" value=false}]
[{assign var="isB2B" value=false}]
[{assign var="isB2C" value=false}]
[{if isset( $invadr.oxuser__oxbirthdate.month )}]
    [{assign var="iBirthdayMonth" value=$invadr.oxuser__oxbirthdate.month}]
[{elseif $oxcmp_user->oxuser__oxbirthdate->value && $oxcmp_user->oxuser__oxbirthdate->value != "0000-00-00"}]
    [{assign var="iBirthdayMonth" value=$oxcmp_user->oxuser__oxbirthdate->value|regex_replace:"/^([0-9]{4})[-]/":""|regex_replace:"/[-]([0-9]{1,2})$/":""}]
[{/if}]

[{if isset( $invadr.oxuser__oxbirthdate.day )}]
    [{assign var="iBirthdayDay" value=$invadr.oxuser__oxbirthdate.day}]
[{elseif $oxcmp_user->oxuser__oxbirthdate->value && $oxcmp_user->oxuser__oxbirthdate->value != "0000-00-00"}]
    [{assign var="iBirthdayDay" value=$oxcmp_user->oxuser__oxbirthdate->value|regex_replace:"/^([0-9]{4})[-]([0-9]{1,2})[-]/":""}]
[{/if}]

[{if isset( $invadr.oxuser__oxbirthdate.year )}]
    [{assign var="iBirthdayYear" value=$invadr.oxuser__oxbirthdate.year}]
[{elseif $oxcmp_user->oxuser__oxbirthdate->value && $oxcmp_user->oxuser__oxbirthdate->value != "0000-00-00"}]
    [{assign var="iBirthdayYear" value=$oxcmp_user->oxuser__oxbirthdate->value|regex_replace:"/[-]([0-9]{1,2})[-]([0-9]{1,2})$/":""}]
[{/if}]
[{if ($oxcmp_user->oxuser__oxcompany->value || ($invadr && $invadr->oxaddress__oxcompany->value))}]
    [{assign var="isCompany" value=true}]
[{/if}]

[{if $oViewConf->isB2CInvoiceEligibility()}]
    [{assign var="isB2C" value=true}]
    [{/if}]
[{if $oViewConf->isB2BInvoiceEligibility()}]
    [{assign var="isB2B" value=true}]
    [{/if}]
[{if $isB2C && ($isB2B && $isCompany)}]
    [{assign var="isBoth" value=true}]
[{/if}]
[{if $isCompany}]
    [{assign var="customerType" value="B2B"}]
[{else}]
    [{assign var="customerType" value="B2C"}]
[{/if}]

[{block name="unzer_inv_secured_birthdate"}]
    <form id="payment-form" class="js-oxValidate form-horizontal unzerUI form" novalidate="novalidate">
        [{if false && $isBoth}]
            <div class="form-group row unzerConsumer text-danger">
                <div class="col-12 col-lg-9 col-lg-offset-3">
                    <select id="unzer_select_consumer" class="form-control selectpicker" required>
                        <option value="" label="[{oxmultilang ident="OSCUNZER_CONSUMER_TARGET"}]" for="unzer_select_consumer">[{oxmultilang ident="OSCUNZER_CONSUMER_TARGET"}]</option>
                        <option value="B2B" label="[{oxmultilang ident="OSCUNZER_CONSUMER_TARGET_B2B"}]">[{oxmultilang ident="OSCUNZER_CONSUMER_TARGET_B2B"}]</option>
                        <option value="B2C" label="[{oxmultilang ident="OSCUNZER_CONSUMER_TARGET_B2C"}]">[{oxmultilang ident="OSCUNZER_CONSUMER_TARGET_B2C"}]</option>
                    </select>
                </div>
            </div>
        [{else}]
            <input id="unzer_select_consumer" type="hidden" value="[{if $isB2B}]B2B[{else}]B2C[{/if}]" />
        [{/if}]

        <div id="paylater-invoice">
            <!-- ... The Payment form UI element (opt-in text and checkbox) will be inserted here -->
        </div>
        <div id="error-holder" class="field" style="color: #9f3a38">
            <!-- Errors will be inserted here -->
        </div>

        <div id="consumer_common" class="form-group row oxDate [{if !$iBirthdayMonth || !$iBirthdayDay || !$iBirthdayYear}]text-normal help-block[{/if}]">
            <label class="col-12 col-lg-3 req" for="oxDay">[{oxmultilang ident="BIRTHDATE"}]</label>
            <div class="col-3 col-lg-3">
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
            <div class="col-3 col-lg-3">
                <input id="birthdate_year" class="oxYear form-control" type="text" maxlength="4" value="[{if $iBirthdayYear}][{$iBirthdayYear}][{/if}]"
                       placeholder="[{oxmultilang ident="YEAR"}]" required>
            </div>
            <div class="offset-lg-3 col-lg-9 col-12">
                <div class="help-block">
                    <p class="text-normal [{if $iBirthdayMonth && $iBirthdayDay && $iBirthdayYear}]d-none hidden[{/if}]">[{oxmultilang ident="OSCUNZER_COMPANY_FORM_birthday" }]</p>
                </div>
            </div>
        </div>
        [{if $isCompany}]
        <div id="consumer_b2b" class="form-group row unzerCompanyForm text-danger">
            <label class="col-12 col-lg-3  req" for="unzer_company_form">[{oxmultilang ident="OSCUNZER_COMPANY_FORM"}]</label>
            <div class="col-12 col-lg-9">
                <select id="unzer_company_form" class="form-control selectpicker" required>
                    <option value="" label="-">-</option>
                    [{foreach from=$oView->getUnzerCompanyTypes() item=title key=value}]
                    <option value="[{$value}]" label="[{$title}]">[{$title}]</option>
                    [{/foreach}]
                </select>
            </div>
        </div>
        [{/if}]
    </form>
    [{capture assign="unzerInvoiceJS"}]
    let showB2B = function() {
        $('#consumer_b2c').collapse('hide');
        $('#consumer_b2b').collapse('show');
    };
    let showB2C = function showB2C() {
        $('#consumer_b2c').collapse('show');
        $('#consumer_b2b').collapse('hide');
    };
    [{if $isBoth}]
        $('#unzer_select_consumer').change(function() {
            opt = $(this).val();
            if (opt=="B2B") {
                $('#consumer_b2c').collapse('hide');
                $('#consumer_b2b').collapse('show');
            }
            else if (opt == "B2C") {
                $('#consumer_b2c').collapse('show');
                $('#consumer_b2b').collapse('hide');
            }
            else {
                $('#consumer_b2c').collapse('hide');
                $('#consumer_b2b').collapse('hide');
            }
        });
    [{/if}]

[{if $isCompany}]
    showB2B();
    let unzerInstance = new unzer('[{$oViewConf->getUnzerB2BPubKey()}]', {locale: "[{$unzerLocale}]"});
[{else}]
    showB2C();
    let unzerInstance = new unzer('[{$oViewConf->getUnzerB2CPubKey()}]', {locale: "[{$unzerLocale}]"});
[{/if}]
    let paylaterInvoice = unzerInstance.PaylaterInvoice();
    paylaterInvoice.create({
        containerId: 'paylater-invoice',
        customerType: '[{$customerType}]', // B2C or B2B
        errorHolderId: 'error-holder',
    })

    // Handle payment form submission.
    $( "#payment-form" ).submit(function( event ) {
        event.preventDefault();
        paylaterInvoice.createResource()
        .then(function(result) {
        let typeId = result.id;
        // submit the payment type ID to your server-side integration
        let selectConsumer = '[{$customerType}]';
        if(
            (
            !$( '.oxDate' ).hasClass("text-danger") && selectConsumer == 'B2C'
            )
        [{if $isCompany}]
            || (
            !$( '.unzerCompanyForm' ).hasClass("text-danger") && selectConsumer == 'B2B'
            )
        [{/if}]
        ) {
        [{if $isCompany}]
            let hiddenInputCompanyForm = $(document.createElement('input'))
                .attr('type', 'hidden')
                .attr('name', 'unzer_company_form')
                .val($('#unzer_company_form').val());
            $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInputCompanyForm);
        [{/if}]
            let hiddenInputBirthdate = $(document.createElement('input'))
                .attr('type', 'hidden')
                .attr('name', 'birthdate')
                .val($('#birthdate_year').val()+'-'+$('#birthdate_month').val()+'-'+$('#birthdate_day').val());
            $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInputBirthdate);

            let hiddenCustomerType = $(document.createElement('input'))
                .attr('type', 'hidden')
                .attr('name', 'unzer_customer_type')
                .val('[{$customerType}]');
            $('#orderConfirmAgbBottom').find(".hidden").append(hiddenCustomerType);

            let hiddenTypeId = $(document.createElement('input'))
                .attr('type', 'hidden')
                .attr('name', 'unzer_type_id')
                .val(typeId);
            $('#orderConfirmAgbBottom').find(".hidden").append(hiddenTypeId);

            $('#orderConfirmAgbBottom' ).addClass("submitable");
            $("#orderConfirmAgbBottom" ).submit();
        } else {
            $('html, body').animate({
                scrollTop: $("#orderPayment").offset().top - 150
            }, 350);
        }
        })
        .catch(function(error) {
        document.getElementById('error-holder').innerText = error.customerMessage || error.message || 'Error'
        })

    });

    $('#orderConfirmAgbBottom').submit(function( event ) {
        if(!$('#orderConfirmAgbBottom').hasClass("submitable")){
            event.preventDefault();
            $("#payment-form").submit();
        }
        $( '#orderConfirmAgbBottom .submitButton' ).prop('disabled', true);
    });

    [{/capture}]
    [{oxscript add=$unzerInvoiceJS}]
[{/block}]
