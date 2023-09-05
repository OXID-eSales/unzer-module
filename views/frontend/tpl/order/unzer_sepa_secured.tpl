[{include file="modules/osc/unzer/unzer_assets.tpl"}]
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

<form id="payment-form-sepa-secured" class="js-oxValidate form-horizontal" novalidate="novalidate">
    <br />
    <div class="form-group row oxDate [{if !$iBirthdayMonth || !$iBirthdayDay || !$iBirthdayYear}]text-danger[{/if}]">
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
                <p class="text-danger [{if $iBirthdayMonth && $iBirthdayDay && $iBirthdayYear}]d-none hidden[{/if}]">[{oxmultilang ident="DD_FORM_VALIDATION_REQUIRED"}]</p>
            </div>
        </div>
    </div>
    <div id="sepa-secured-IBAN" class="field">
        <!-- The IBAN field UI Element will be inserted here -->
    </div>
    <br />
    <div id="payment-sepa-confirm">
        <div class="sepaagreement" id="sepaagree_unzer">
            <input id="oscunzersepaagreement" type="checkbox" name="oscunzersepaagreement" value="0">
            <label for="oscunzersepaagreement">
                [{oxifcontent ident="oscunzersepamandateconfirmation" object="oCont"}]
                [{$oCont->oxcontents__oxcontent->value}]
                [{/oxifcontent}]
            </label>
        </div>
    </div>

    <div class="field" id="error-holder" style="color: #9f3a38"> </div>

</form>

[{capture assign="unzerSepaDirectSecurredJS"}]
    // Create an Unzer instance with your public key
    let unzerInstance = new unzer('[{$unzerpub}]', {locale: "[{$unzerLocale}]"});

    // Create a SEPA Direct Debit Secured instance and render the form
    let SepaDirectDebitSecured = unzerInstance.SepaDirectDebitSecured();
    SepaDirectDebitSecured.create('sepa-direct-debit-secured', {
        containerId: 'sepa-secured-IBAN'
    });

    // Handling payment form submission
    $( "#payment-form-sepa-secured" ).submit(function( event ) {
        event.preventDefault();

        if(!$( '.oxDate' ).hasClass("text-danger") &&
           $('#oscunzersepaagreement').is(':checked')
        ) {
            // Creating a SEPA resource
            SepaDirectDebitSecured.createResource()
            .then(function(result) {

                let hiddenInput = $(document.createElement('input'))
                .attr('type', 'hidden')
                .attr('name', 'paymentData')
                .val(JSON.stringify(result));
                $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput);

                let hiddenInput2 = $(document.createElement('input'))
                .attr('type', 'hidden')
                .attr('name', 'sepaConfirmation')
                .val($('#oscunzersepaagreement').is(':checked') ? '1' : '0');
                $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInput2);

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
            })
        }else{
            $('html, body').animate({
                scrollTop: $("#orderPayment").offset().top - 150
            }, 350);
        }
    });

    $( '#orderConfirmAgbBottom' ).submit(function( event ) {
        if(!$( '#orderConfirmAgbBottom' ).hasClass("submitable")){
            event.preventDefault();
            $( "#payment-form-sepa-secured" ).submit();
        }
        $( '#orderConfirmAgbBottom .submitButton' ).prop('disabled', true);
    });
    [{/capture}]
[{oxscript add=$unzerSepaDirectSecurredJS}]