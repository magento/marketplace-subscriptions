/* eslint-disable*/
define([
    'jquery',
    'uiComponent',
    'uiLayout',
    'ko',
    'mageUtils',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/modal',
    'PayPal_Subscription/js/action/customer/payment/set-existing-method'
], function (
    $,
    Component,
    layout,
    ko,
    utils,
    customerData,
    modal,
    setPaymentMethod
) {
    'use strict';

    return Component.extend({
        defaults: {
            selectedPaymentMethod: ko.observable(),
            message: ko.observable(),
            modalContainer: false,
            template: 'PayPal_Subscription/customer/payment/edit-payment-modal'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            this.initChildren();
            if (this.subscription().paymentData() != null) {
                this.selectedPaymentMethod(
                    this.subscription().paymentData()['public_hash']
                );
            }
            return this;
        },

        initChildren: function () {
            var existingPaymentMethodTemplate = {
                parent: '${ $.$data.parentName }',
                name: '${ $.$data.name }',
                component:
                    'PayPal_Subscription/js/view/customer/payment/existing-methods',
                provider: 'subscriptionListProvider'
            };
            var existingPaymentMethodComponent = utils.template(
                existingPaymentMethodTemplate,
                {
                    parentName: this.name,
                    name: 'active.subscription.existing.payment.method'
                }
            );

            utils.extend(existingPaymentMethodComponent, {
                methods: this.methods,
                template:
                    'PayPal_Subscription/customer/payment/existing-method-select'
            });
            layout([existingPaymentMethodComponent]);
        },

        initModal: function () {
            var that = this;
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: '',
                closed: function () {
                    var existingMethods = that.modalContainer.find('.ps-existing-payment-methods');
                    existingMethods.show();
                }
            };
            this.modalContainer = $('div[data-edit-payment-modal]');
            modal(options, this.modalContainer);
        },

        showEditPaymentModal: function () {
            this.modalContainer.modal('openModal');
        },

        getExistingMethods: function () {
            return window.subscriptionsData.paymentMethods;
        },

        handleFormSubmit: function (paymentMethodHash = false) {
            var that = this;
            var subscriptionId = this.subscription().subscriptionId;
            $('body').trigger('processStart');
            setPaymentMethod(subscriptionId, paymentMethodHash)
                .done(function (response) {
                    // Add address to the default addresses
                    customerData.set('messages', {
                        messages: [
                            {
                                text: 'Your payment method has been updated.',
                                type: 'success'
                            }
                        ]
                    });
                })
                .fail(function () {
                    customerData.set('messages', {
                        messages: [
                            {
                                text: 'Unable to update your payment method. Please try again.',
                                type: 'error'
                            }
                        ]
                    });
                })
                .always(function () {
                    $('body').trigger('processStop');
                    that.modalContainer.modal('closeModal');
                });
        },

        setExistingPaymentMethod: function (element, paymentPublicHash) {
            this.handleFormSubmit(paymentPublicHash);
            this.updateSubscriptionPaymentMethod(paymentPublicHash);
        },

        updateSubscriptionPaymentMethod: function (paymentPublicHash) {
            var paymentData = [];
            $.each(this.getExistingMethods(), function (i, method) {
                if (method['public_hash'] == paymentPublicHash) {
                    paymentData = method;
                }
            });
            if (paymentData != []) {
                this.subscription().paymentDetails.masked_card_number(paymentData.details.maskedCC);
                this.subscription().paymentDetails.expiry(paymentData.details.expirationDate);
                this.subscription().paymentDetails.card_type(paymentData.details.type);
                this.subscription().paymentDetails.type(paymentData.type);
            }
        }
    });
});
