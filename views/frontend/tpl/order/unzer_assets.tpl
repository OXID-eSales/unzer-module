[{block name="unzer_jquery"}]
    [{* we use original script tag instead of oxscript because of the additional params *}]
    [{if $oViewConf|method_exists:'getjQueryImport' && $oViewConf->getjQueryImport()}]
        <script
            src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
            crossorigin="anonymous"></script>
    [{/if}]
[{/block}]
[{block name="unzer_js"}]
    [{oxscript include="https://static.unzer.com/v1/unzer.js"}]
[{/block}]
[{block name="unzer_css"}]
    [{oxstyle include="https://static.unzer.com/v1/unzer.css"}]
[{/block}]
