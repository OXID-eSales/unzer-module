<li class="list-group-item[{if $active_link == "unzer_saved_payments"}] active[{/if}]">
    <a href="[{oxgetseourl ident=$oViewConf->getSslSelfLink()|cat:"cl=unzer_saved_payments"}]" title="[{oxmultilang ident="OSCUNZER_SAVED_PAYMENTS"}]">[{oxmultilang ident="OSCUNZER_SAVED_PAYMENTS"}]</a>
</li>
[{$smarty.block.parent}]

