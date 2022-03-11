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

[{block name="unzer_inv_secured_birthdate"}]
    <form id="inv_secured_birthdate" class="js-oxValidate form-horizontal" novalidate="novalidate">
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
    </form>
    [{capture assign="unzerInvSecuredJS"}]
        $('#orderConfirmAgbBottom').submit(function( event ) {
            if(!$('#orderConfirmAgbBottom').hasClass("submitable")){
                event.preventDefault();
                $("#inv_secured_birthdate").submit();
            }
        });

        $( "#inv_secured_birthdate" ).submit(function( event ) {
            event.preventDefault();
                setTimeout(function(){
                if(!$( '.oxDate' ).hasClass("text-danger")){

                    let hiddenInputBirthdate = $(document.createElement('input'))
                    .attr('type', 'hidden')
                    .attr('name', 'birthdate')
                    .val($('#birthdate_year').val()+'-'+$('#birthdate_month').val()+'-'+$('#birthdate_day').val());
                    $('#orderConfirmAgbBottom').find(".hidden").append(hiddenInputBirthdate);


                    $( '#orderConfirmAgbBottom' ).addClass("submitable");
                    $( "#orderConfirmAgbBottom" ).submit();

                }else{
                    $('html, body').animate({
                        scrollTop: $("#orderPayment").offset().top - 150
                    }, 350);
                }
            }, 100);
        });
    [{/capture}]
    [{oxscript add=$unzerInvSecuredJS}]
[{/block}]
