define([
    'underscore',
    'ko',
    'mageUtils',
    'jquery',
    'uiComponent',
    'uiLayout',
    'PayPal_Subscription/js/model/customer/subscription-list',
    'uiRegistry'
], function (_, ko, utils, $, Component, layout, subscriptionList, uiRegistry) {
    'use strict';

    var subscriptionRendererTemplate = {
        parent: '${ $.$data.parentName }',
        name: '${ $.$data.name }',
        component: 'PayPal_Subscription/js/view/customer/list/subscription',
        provider: 'subscriptionListProvider'
    };

    return Component.extend({
        defaults: {
            template: 'PayPal_Subscription/customer/subscription-list',
            visible: ko.observable(false),
            rendererTemplates: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            var that = this,
                activeSubscriptions = subscriptionList.getActiveSubscriptions();
            this.activeSubscriptions = subscriptionList.getActiveSubscriptions();
            this.visible = ko.observable(activeSubscriptions().length > 0);
            activeSubscriptions.subscribe(function (changes) {
                that.handleActiveSubscriptionChanges(changes);
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
                this.activeSubscriptions(),
                this.createRendererComponent,
                this
            );
            return this;
        },

        handleActiveSubscriptionChanges: function (activeSubscriptions) {
            var activeSubscriptionIds = [],
                renderedSubscriptionIds = [];
            $(activeSubscriptions).each(function (i, activeSubscription) {
                activeSubscriptionIds.push(activeSubscription.subscriptionId);
            });
            if (this.elems().length > 0) {
                $(this.elems()).each(function (i, subscriptionComponent) {
                    var componentSub = subscriptionComponent.subscription(),
                        componentSubId = componentSub ? componentSub.subscriptionId : null;
                    if (componentSubId !== null ) {
                        renderedSubscriptionIds.push(componentSubId);
                    }
                });
            }
            var subscriptionIdsToRemove = this.getSubscriptionsToRemove(
                activeSubscriptionIds,
                renderedSubscriptionIds
            );
            this.renderSubscriptions(
                activeSubscriptions,
                subscriptionIdsToRemove
            );
        },

        renderSubscriptions: function (
            activeSubscriptions,
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
            $(activeSubscriptions).each(function (i, subscription) {
                this.createRendererComponent(subscription);
            }.bind(this));
        },

        cleanUpChildren: function (subscriptionComponent) {
            subscriptionComponent._clearRefs();
            if (subscriptionComponent.elems().length > 0) {
                $(subscriptionComponent.elems()).each(function (index, childComponent) {
                    this.cleanUpChildren(childComponent);
                }.bind(this));
            }
        },

        getSubscriptionsToRemove: function (activeSubscriptionIds, currentSubscriptionIds) {
            return currentSubscriptionIds.filter(x => activeSubscriptionIds.indexOf(x) === -1);
        },

        /**
         * Create new component that will render given subscription in the subscription list
         *
         * @param {Object} address
         */
        createRendererComponent: function (subscription) {
            var status = parseInt(subscription.status(), 10);

            if (status !== 1) {
                return;
            }
            var templateData,
                rendererComponent;
            if (`subscription_${subscription.subscriptionId}` in this.rendererComponents) {
                this.rendererComponents[`subscription_${subscription.subscriptionId}`].subscription(subscription);
            } else {
                templateData = {
                    parentName: this.name,
                    name: `subscription.${subscription.subscriptionId}`
                };
                rendererComponent = utils.template(
                    subscriptionRendererTemplate,
                    templateData
                );
                utils.extend(rendererComponent, {
                    subscription: ko.observable(subscription)
                });
                layout([rendererComponent], this, false);
                this.rendererComponents[`subscription_${subscription.subscriptionId}`] = rendererComponent;
            }
        }
    });
});
