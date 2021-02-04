/* eslint-disable*/
define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'PayPal_Subscription/js/action/address/add-billing-address',
    'PayPal_Subscription/js/action/address/add-shipping-address',
    'mage/validation',
    'PayPal_Subscription/js/model/address-builder'
], function (
    $,
    Component,
    ko,
    $t,
    modal,
    addBillingAddress,
    addShippingAddress,
    validation,
    addressBuilder
) {
    'use strict';

    return Component.extend({

        defaults: {
            subscriptionId: ko.observable(),
            message: ko.observable(),
            modalContainer: $('[data-edit-address-modal]')
        },

        /**
         * @return {exports}
         */
        initialize: function () {
            this._super();
        },

        /**
         * Post Address Form
         */
        postForm: function () {

            var values = [],
                success = true,
                that = this;

            if (!this.validateForm('#form-validate')) {
                success = false;
                return;
            }

            $.each($('#form-validate').serializeArray(), function (i, field) {
                values[field.name] = field.value;
            });

            var address = {
                "address": {
                    "company": values.company,
                    "street": [
                        values.street_1,
                        values.street_2,
                        values.street_3
                    ],
                    "city": values.city,
                    "postcode": values.postcode,
                    "country_id": values.country_id,
                    "firstname": values.firstname,
                    "lastname": values.lastname,
                    "telephone": values.telephone,
                    "region": values.region
                }
            };

            // Set as Billing Address
            if (values.default_billing) {
                addBillingAddress(this.subscriptionId, address).success(function (response) {

                    // Add address to the default addresses
                    var address = Object.values(JSON.parse(response.billing_address));
                    addressBuilder.billingAddress(address);

                }).error(function () {
                    success = false;
                })
            }

            // Set as Shipping Address
            if (values.default_shipping) {
                addShippingAddress(this.subscriptionId, address).success(function (response) {

                    // Add address to the default addresses
                    var address  = Object.values(JSON.parse(response.shipping_address));
                    addressBuilder.shippingAddress(address);

                }).error(function () {
                    success = false;
                })
            }

            if (!success) {
                that.message($t('Unable to update your address, please try again.'));
            } else {
                this.modalContainer.modal('closeModal');
            }
        },

        /**
         * Open address form
         */
        openAddressForm: function () {

            var modalContainer = this.modalContainer,
                options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    modalClass: 'ps-add-address-form-modal',
                    title: $t('Add a new address'),
                    buttons: []
                };

            modal(options, modalContainer);
            modalContainer.modal('openModal');
        },

        /**
         * Validate Form
         * @param form
         * @returns {jQuery}
         */
        validateForm: function (form) {
            return $(form).validation() && $(form).validation('isValid');
        }

    })
});