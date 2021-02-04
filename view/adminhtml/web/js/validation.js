require(
    [
        'jquery',
        'mage/translate',
        'jquery/validate'
    ],
    function($) {
        $.validator.addMethod(
            'validate-cron', function (v) {
                return /((((\d+,)+\d+|(\d+(\/|-)\d+)|\d+|\*) ?){5,7})/.test(v);
            },
            $.mage.__('Not a valid CRON schedule format')
        );
    }
);