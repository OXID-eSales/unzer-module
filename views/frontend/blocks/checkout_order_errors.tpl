[{$smarty.block.parent}]
    [{assign var=sepaError value=$oView->isSepaMandateConfirmationError()}] dfddf
    [{if $sepaError == 1}]
         [{include file="message/error.tpl" statusMessage="ERROR_UNZER_SEPA_CONFIRMATION_MISSING"|oxmultilangassign}]
    [{/if}]
