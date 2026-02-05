define([
    'jquery',
    'ko',
    'mageUtils',
    'uiComponent',
    'uiLayout'
], function ($, ko, utils, Component, layout) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'PayPal_Subscription/customer/list/component/shipping-address',
            address: ko.observable('')
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            this.initChildren();
            this.address = this.getFormattedAddress();
            return this;
        },

        initChildren: function () {
            var addressModalTemplate = {
                parent: '${ $.$data.parentName }',
                name: '${ $.$data.name }',
                component:
                    'PayPal_Subscription/js/view/customer/addresses/address-modal',
                provider: 'subscriptionListProvider'
            };
            var addressModalComponent = utils.template(
                addressModalTemplate,
                {
                    parentName: this.name,
                    name: `address-modal-${this.subscription().subscriptionId}`
                }
            );

            utils.extend(addressModalComponent, {
                subscription: ko.observable(this.subscription())
            });

            layout([addressModalComponent]);
        },

        getFormattedAddress: function () {
            return this.subscription().shippingAddressHtml;
        }
    });
});
