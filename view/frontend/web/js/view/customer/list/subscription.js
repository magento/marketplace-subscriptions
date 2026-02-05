define([
    'jquery',
    'ko',
    'mageUtils',
    'uiComponent',
    'underscore',
    'uiLayout',
    'mage/translate',
    'Magento_Customer/js/customer-data',
    'PayPal_Subscription/js/action/customer/view/set-subscription-status',
    'PayPal_Subscription/js/action/customer/view/skip-subscription-order',
    'PayPal_Subscription/js/action/customer/view/send-order-now',
    'PayPal_Subscription/js/model/date-formatter',
    'PayPal_Subscription/js/model/customer/subscription-list'
], function (
    $,
    ko,
    utils,
    Component,
    _,
    layout,
    $t,
    customerData,
    setSubscriptionStatus,
    skipSubscriptionOrder,
    sendOrderNow,
    dateFormatter,
    subscriptionList
) {
    'use strict';

    var productRendererTemplate = {
            parent: '${ $.$data.parentName }',
            name: '${ $.$data.name }',
            component: 'PayPal_Subscription/js/view/customer/list/subscription/product',
            provider: 'subscriptionListProvider'
        },
        subscriptionInfoRendererTemplate = {
            parent: '${ $.$data.parentName }',
            name: '${ $.$data.name }',
            component: 'PayPal_Subscription/js/view/customer/list/subscription/subscription-info',
            provider: 'subscriptionListProvider'
        };

    return Component.extend({
        defaults: {
            template: 'PayPal_Subscription/customer/list/subscription'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            this.subscriptionIdText = $t('Subscription ID: %1').replace('%1', this.subscription().subscriptionId);
            this.lastDispatched = this.subscription().previousReleaseDate() == null ?
                $t('N/A') :
                $t(' on %1').replace('%1', this.subscription().previousReleaseDate());
            this.initChildren();
            return this;
        },

        initChildren: function () {
            var productComponent,
                subscriptionInfoComponent;
            productComponent = utils.template(
                productRendererTemplate,
                {
                    parentName: this.name,
                    name: `active.subscription.productComponent.${this.subscription().subscriptionId}`
                }
            );
            utils.extend(productComponent, {
                subscription: ko.observable(this.subscription())
            });
            subscriptionInfoComponent = utils.template(
                subscriptionInfoRendererTemplate,
                {
                    parentName: this.name,
                    name: `active.subscription.subscriptionInfoComponent.${this.subscription().subscriptionId}`
                }
            );
            utils.extend(subscriptionInfoComponent, {
                subscription: ko.observable(this.subscription())
            });
            layout([
                productComponent,
                subscriptionInfoComponent
            ]);
        },

        sendNow: function () {
            var that = this;
            $("body").trigger('processStart');

            sendOrderNow(this.subscription().subscriptionId)
                .fail(function () {
                    $("body").trigger('processStop');
                    customerData.set('messages', {
                        messages: [{
                            text: 'Unable to send subscription now, please try again.',
                            type: 'error'
                        }]
                    });
                })
                .done(function (response) {
                    var nextReleaseDate = dateFormatter(response['next_release_date']),
                        previousReleaseDate = response['previous_release_date'] || null;
                    that.subscription().nextReleaseDate(nextReleaseDate);

                    if (previousReleaseDate !== null) {
                        previousReleaseDate = dateFormatter(previousReleaseDate);
                        that.subscription().previousReleaseDate(previousReleaseDate);
                    }

                    $("body").trigger('processStop');
                    customerData.set('messages', {
                        messages: [{
                            text: 'Subscription has been sent.',
                            type: 'success'
                        }]
                    });
                });
        },

        skipOrder: function () {
            var that = this;
            $("body").trigger('processStart');
            skipSubscriptionOrder(
                this.subscription().subscriptionId
            ).fail(function () {
                $("body").trigger('processStop');
                customerData.set('messages', {
                    messages: [{
                        text: 'Unable to update skip subscription order, please try again.',
                        type: 'error'
                    }]
                });
            }).done(function (response) {
                var nextReleaseDate = dateFormatter(response['next_release_date']);
                that.subscription().nextReleaseDate(nextReleaseDate);
                $("body").trigger('processStop');
                customerData.set('messages', {
                    messages: [{
                        text: 'Subscription Order has been skipped.',
                        type: 'success'
                    }]
                });
            });
        },

        cancelSubscription: function () {
            var that = this;
            $("body").trigger('processStart');
            setSubscriptionStatus(
                this.subscription().subscriptionId,
                3
            ).fail(function () {
                $("body").trigger('processStop');
                customerData.set('messages', {
                    messages: [{
                        text: 'Unable to cancel subscription, please try again.',
                        type: 'error'
                    }]
                });
            }).done(function (subscription) {
                that.handleCancelledSubsriptionListChanges(subscription);
                $("body").trigger('processStop');
                customerData.set('messages', {
                    messages: [{
                        text: 'Subscription Order has been cancelled.',
                        type: 'success'
                    }]
                });
            });
        },

        handleCancelledSubsriptionListChanges: function (subscription) {
            var cancelledSubs = subscriptionList.getCancelledSubscriptions(),
                activeSubs = subscriptionList.getActiveSubscriptions(),
                that = this;
            this.subscription().status(subscription.status);
            this.subscription().updatedAt(dateFormatter(subscription['updated_at']));
            cancelledSubs.push(this.subscription());
            $(activeSubs()).each(function (key, subscription) {
                if (subscription.subscriptionId == that.subscription().subscriptionId) {
                    activeSubs.splice(key, 1);
                }
            });
        }
    });
});
