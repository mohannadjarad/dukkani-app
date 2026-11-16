{{--
    Currency metadata for client-side formatting (Alpine helpers and charts).
    Runs before the deferred app.js module so window.DukkaniMoney is ready.
--}}
<script>
    window.DukkaniMoney = {
        symbol: @json(\App\Support\Money::symbol()),
        decimals: {{ \App\Support\Money::decimals() }},
        currency: @json(\App\Support\Money::currentCurrency()),
    };
</script>
