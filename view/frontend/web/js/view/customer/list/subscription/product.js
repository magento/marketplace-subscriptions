define([
    'ko',
    'uiComponent',
    'mageUtils',
    'uiLayout'
], function (ko, Component, utils, layout) {
    'use strict';

    var productControlsRendererTemplate = {
        parent: '${ $.$data.parentName }',
        name: '${ $.$data.name }',
        component: 'PayPal_Subscription/js/view/customer/list/subscription/product-controls',
        provider: 'subscriptionListProvider'
    };

    return Component.extend({
        defaults: {
            template: 'PayPal_Subscription/customer/list/component/product'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            this.imageUrl = ko.observable(this.subscription().product.image);
            this.imageAlt = ko.observable(this.subscription().product.name);
            this.frequencyLabel = ko.observable(this.subscription().frequencyLabel);
            this.initChildren();
            return this;
        },

        initChildren: function () {
            var productControlsComponent;
            productControlsComponent = utils.template(
                productControlsRendererTemplate,
                {
                    parentName: this.name,
                    name: `productControlsComponent-${this.subscription().subscriptionId}`
                }
            );
            utils.extend(productControlsComponent, {
                subscription: ko.observable(this.subscription())
            });
            layout([productControlsComponent]);
        },

        getSubscriptionOptions: function() {
            var bundleOptions = this.subscription().product.bundle_options;
            return Object.keys(bundleOptions).map(function(option) {
                return {
                    label: option,
                    items: bundleOptions[option]
                };
            });
        }
    });
});
