[{block name="unzer_inv_secured_birthdate"}]
    [{if $oxcmp_user->oxuser__oxbirthdate->value == "0000-00-00"}]
        <form id="inv_secured_birthdate" class="js-oxValidate form-horizontal" novalidate="novalidate">
            <div class="form-group row oxDate">
                <label class="col-12 col-lg-3 req" for="oxDay">[{oxmultilang ident="BIRTHDATE"}]</label>
                <div class="col-3 col-lg-3">
                    <input id="birthdate_day" class="oxDay form-control" type="text" maxlength="2" value=""
                           placeholder="[{oxmultilang ident="DAY"}]" required autocomplete="bday-day">
                </div>
                <div class="col-6 col-lg-3">
                    <select id="birthdate_month" class="oxMonth form-control" required autocomplete="bday-month">
                        <option value="" label="-">-</option>
                        [{section name="month" start=1 loop=13}]
                            <option value="[{$smarty.section.month.index}]" label="[{$smarty.section.month.index}]">
                                [{oxmultilang ident="MONTH_NAME_"|cat:$smarty.section.month.index}]
                            </option>
                        [{/section}]
                    </select>
                </div>
                <div class="col-3 col-lg-3">
                    <input id="birthdate_year" class="oxYear form-control" type="text" maxlength="4" value=""
                           placeholder="[{oxmultilang ident="YEAR"}]" required autocomplete="bday-year">
                </div>
                <div class="offset-lg-3 col-lg-9 col-12">
                    <div class="help-block"></div>
                </div>
            </div>
        </form>
        [{capture assign="unzerInvSecuredJS"}]
            $( '#orderConfirmAgbBottom' ).submit(function( event ) {
                if(!$( '#orderConfirmAgbBottom' ).hasClass("submitable")){
                    event.preventDefault();
                    $( "#inv_secured_birthdate" ).submit();
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
    [{/if}]
[{/block}]
