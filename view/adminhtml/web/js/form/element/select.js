define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
], function ($, _, uiRegistry, select) {
    'use strict';

    return select.extend({
        onUpdate: function (value) {
            $('#save_and_continue').click();
        }
    });
});