define([
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'uiLayout',
    'PayPal_Subscription/js/model/customer/subscription-list',
    'jquery',
    'uiRegistry'
], function (_, ko, utils, Component, layout, subscriptionList, $, uiRegistry) {
    'use strict';

    var disabledSubscriptionComponent = {
        parent: '${ $.$data.parentName }',
        name: '${ $.$data.name }',
        component: 'PayPal_Subscription/js/view/customer/list/disabled-subscription',
        provider: 'subscriptionListProvider'
    };

    return Component.extend({
        defaults: {
            template: 'PayPal_Subscription/customer/disabled-subscription-list',
            visible: ko.observable(false)
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            var cancelledSubscriptions = subscriptionList.getCancelledSubscriptions(),
                that = this;
            this.cancelledSubscriptions = subscriptionList.getCancelledSubscriptions();
            this.visible = ko.observable(cancelledSubscriptions().length > 0);
            cancelledSubscriptions.subscribe(function (changes) {
                that.handleInActiveSubscriptionChanges(changes);
            });
            this.initChildren();
            return this;
        },

        /** @inheritdoc */
        initConfig: function () {
            this._super();
            this.rendererComponents = [];
            return this;
        },

        /** @inheritdoc */
        initChildren: function () {
            _.each(
                this.cancelledSubscriptions(),
                this.createRendererComponent,
                this
            );
            return this;
        },

        handleInActiveSubscriptionChanges: function (inActiveSubscriptions) {
            var inActiveSubscriptionIds = [],
                renderedInactiveSubscriptionIds = [];
            $(inActiveSubscriptions).each(function (i, inActiveSubscription) {
                inActiveSubscriptionIds.push(inActiveSubscription.subscriptionId);
            });
            if (this.elems().length > 0) {
                $(this.elems()).each(function (i, inActivesubscriptionComponent) {
                    var componentSub = inActivesubscriptionComponent.subscription(),
                        componentSubId = componentSub ? componentSub.subscriptionId : null;
                    if (componentSubId !== null ) {
                        renderedInactiveSubscriptionIds.push(componentSubId);
                    }
                });
            }
            var inActiveSubscriptionIdsToRemove = this.getSubscriptionsToRemove(
                inActiveSubscriptionIds,
                renderedInactiveSubscriptionIds
            );
            this.renderInActiveSubscriptions(
                inActiveSubscriptions,
                inActiveSubscriptionIdsToRemove
            );
        },

        renderInActiveSubscriptions: function (
            inActiveSubscriptions,
            subscriptionIdsToRemove
        ) {
            if (subscriptionIdsToRemove.length > 0) {
                for (var k in this.rendererComponents) {
                    var rendererComponent = this.rendererComponents[k];
                    if (rendererComponent) {
                        var componentSub = rendererComponent.subscription(),
                            componentSubId = componentSub ? componentSub.subscriptionId : null;
                        if (componentSubId !== null && subscriptionIdsToRemove.includes(componentSubId)) {
                            delete this.rendererComponents[k];
                        }
                    }
                }
                $(this.elems()).each(function (i, subscriptionComponent) {
                    var componentSub = subscriptionComponent.subscription(),
                        componentSubId = componentSub ? componentSub.subscriptionId : null;
                    if (componentSubId !== null && subscriptionIdsToRemove.includes(componentSubId)) {
                        this.removeChild(subscriptionComponent, false);
                        this.cleanUpChildren(subscriptionComponent);
                    }
                }.bind(this));
            }
            $(inActiveSubscriptions).each(function (i, subscription) {
                this.createRendererComponent(subscription);
            }.bind(this));
        },

        getSubscriptionsToRemove: function (inActiveSubscriptionIds, currentInActiveSubscriptionIds) {
            return currentInActiveSubscriptionIds.filter(x => inActiveSubscriptionIds.indexOf(x) === -1);
        },

        cleanUpChildren: function (subscriptionComponent) {
            subscriptionComponent._clearRefs();
            if (subscriptionComponent.elems().length > 0) {
                $(subscriptionComponent.elems()).each(function (index, childComponent) {
                    this.cleanUpChildren(childComponent);
                }.bind(this));
            }
        },

        /**
         * Create new component that will render given subscription in the subscription list
         *
         * @param {Object} subscription
         */
        createRendererComponent: function (subscription) {
            if (subscription.status() !== 1) {
                var templateData,
                    rendererComponent;
                if (`subscription_${subscription.subscriptionId}` in this.rendererComponents) {
                    this.rendererComponents[`subscription_${subscription.subscriptionId}`].subscription(subscription);
                } else {
                    templateData = {
                        parentName: this.name,
                        name: `disabled-subscription-${subscription.subscriptionId}`
                    };
                    rendererComponent = utils.template(
                        disabledSubscriptionComponent,
                        templateData
                    );
                    utils.extend(rendererComponent, {
                        subscription: ko.observable(subscription)
                    });
                    layout([rendererComponent], this, false);
                    this.rendererComponents[`subscription_${subscription.subscriptionId}`] = rendererComponent;
                }
            }
        }
    });
});

