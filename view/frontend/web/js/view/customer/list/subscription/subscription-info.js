/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'uiComponent',
    'uiLayout',
    'mageUtils'
], function (ko, Component, layout, utils) {
    'use strict';

    var shippingAddressRendererTemplate = {
            parent: '${ $.$data.parentName }',
            name: '${ $.$data.name }',
            component: 'PayPal_Subscription/js/view/customer/list/subscription/subscription-info/shipping-address',
            provider: 'subscriptionListProvider'
        },
        paymentDetailsRendererTemplate = {
            parent: '${ $.$data.parentName }',
            name: '${ $.$data.name }',
            component: 'PayPal_Subscription/js/view/customer/list/subscription/subscription-info/payment-details',
            provider: 'subscriptionListProvider'
        },
        totalsRendererTemplate = {
            parent: '${ $.$data.parentName }',
            name: '${ $.$data.name }',
            component: 'PayPal_Subscription/js/view/customer/list/subscription/subscription-info/totals',
            provider: 'subscriptionListProvider'
        };

    return Component.extend({
        defaults: {
            template: 'PayPal_Subscription/customer/list/component/subscription-info'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            this.initChildren();
            return this;
        },

        initChildren: function () {
            var shippingAddressComponent,
                paymentDetailsComponent,
                totalsComponent;
            shippingAddressComponent = utils.template(
                shippingAddressRendererTemplate,
                {
                    parentName: this.name,
                    name: `shippingAddressComponent-${this.subscription().subscriptionId}`
                }
            );
            utils.extend(shippingAddressComponent, {
                subscription: ko.observable(this.subscription())
            });
            paymentDetailsComponent = utils.template(
                paymentDetailsRendererTemplate,
                {
                    parentName: this.name,
                    name: `paymentDetailsComponent-${this.subscription().subscriptionId}`
                }
            );
            utils.extend(paymentDetailsComponent, {
                subscription: ko.observable(this.subscription())
            });
            totalsComponent = utils.template(
                totalsRendererTemplate,
                {
                    parentName: this.name,
                    name: `totalsComponent-${this.subscription().subscriptionId}`
                }
            );
            utils.extend(totalsComponent, {
                subscription: ko.observable(this.subscription())
            });
            layout([
                shippingAddressComponent,
                paymentDetailsComponent,
                totalsComponent
            ]);
        }
    });
});
