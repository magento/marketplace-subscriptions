define([
    'jquery',
    'ko',
    './subscription',
    'underscore',
    'mageUtils',
    'uiComponent',
    'uiLayout',
    'Magento_Customer/js/customer-data',
    'PayPal_Subscription/js/action/customer/view/set-subscription-status',
    'PayPal_Subscription/js/model/customer/subscription-list'
], function (
    $,
    ko,
    SubscriptionComponent,
    _,
    utils,
    Component,
    layout,
    customerData,
    setSubscriptionStatus,
    subscriptionList
) {
    'use strict';

    var productRendererTemplate = {
        parent: '${ $.$data.parentName }',
        name: '${ $.$data.name }',
        component: 'PayPal_Subscription/js/view/customer/list/disabled-subscription/product',
        provider: 'subscriptionListProvider'
    };

    return SubscriptionComponent.extend({
        defaults: {
            template: 'PayPal_Subscription/customer/list/disabled-subscription'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            this.initChildren();
            return this;
        },

        initChildren: function () {
            var productComponent;
            productComponent = utils.template(
                productRendererTemplate,
                {
                    parentName: this.name,
                    name: `disabled.subscription.productComponent.${this.subscription().subscriptionId}`
                }
            );
            utils.extend(productComponent, {
                subscription: ko.observable(this.subscription())
            });
            layout([
                productComponent
            ]);
        },

        reactivateSubscription: function () {
            var that = this;
            $("body").trigger('processStart');
            setSubscriptionStatus(
                this.subscription().subscriptionId,
                1
            ).fail(function () {
                $("body").trigger('processStop');
                customerData.set('messages', {
                    messages: [{
                        text: 'Unable to reactivate subscription, please try again.',
                        type: 'error'
                    }]
                });
            }).done(function (subscription) {
                that.handleReActivationSubsriptionListChanges(subscription);
                $("body").trigger('processStop');
                customerData.set('messages', {
                    messages: [{
                        text: 'Subscription Order has been reactivated.',
                        type: 'success'
                    }]
                });
            });
        },

        handleReActivationSubsriptionListChanges: function (subscription) {
            var cancelledSubs = subscriptionList.getCancelledSubscriptions(),
                activeSubs = subscriptionList.getActiveSubscriptions(),
                that = this;
            this.subscription().status(subscription.status);
            this.subscription().updatedAt(subscription['updated_at']);
            activeSubs.push(this.subscription());
            $(cancelledSubs()).each(function (key, subscription) {
                if (subscription.subscriptionId == that.subscription().subscriptionId) {
                    cancelledSubs.splice(key, 1);
                }
            });
        }
    });
});
