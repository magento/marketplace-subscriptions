/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'mage/translate',
    'mageUtils',
    'uiLayout'
], function ($, ko, Component, $t, utils, layout) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayPal_Subscription/customer/list/component/payment-details',
            method: ko.observable('')
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            this.initChildren();
            return this;
        },

        initChildren: function () {
            var editPaymentMethodModalTemplate = {
                parent: '${ $.$data.parentName }',
                name: '${ $.$data.name }',
                component:
                    'PayPal_Subscription/js/view/customer/payment/edit-payment-modal',
                provider: 'subscriptionListProvider'
            };
            var editPaymentMethodModalComponent = utils.template(
                editPaymentMethodModalTemplate,
                {
                    parentName: this.name,
                    name: `address-modal-${this.subscription().subscriptionId}`
                }
            );

            utils.extend(editPaymentMethodModalComponent, {
                subscription: ko.observable(this.subscription())
            });

            layout([editPaymentMethodModalComponent]);
        }
    });
});
