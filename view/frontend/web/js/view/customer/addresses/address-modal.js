/* eslint-disable*/
define([
    'jquery',
    'uiComponent',
    'uiLayout',
    'ko',
    'mageUtils',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/modal',
    'PayPal_Subscription/js/action/address/validate-form',
    'PayPal_Subscription/js/action/address/post-address-form',
    'PayPal_Subscription/js/model/address-builder'
], function (
    $,
    Component,
    layout,
    ko,
    utils,
    customerData,
    modal,
    validateForm,
    postAddressForm,
    addressBuilder
) {
    'use strict';

    return Component.extend({
        defaults: {
            selectedAddress: ko.observable(),
            message: ko.observable(),
            modalContainer: false,
            template: 'PayPal_Subscription/customer/addresses/address-modal'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            this.initChildren();
            this.newForm = ko.observable();
            return this;
        },

        initChildren: function () {
            var existingAddressTemplate = {
                parent: '${ $.$data.parentName }',
                name: '${ $.$data.name }',
                component:
                    'PayPal_Subscription/js/view/customer/addresses/existing-address',
                provider: 'subscriptionListProvider'
            };
            var existingAddressComponent = utils.template(
                existingAddressTemplate,
                {
                    parentName: this.name,
                    name: `active.subscription.existing.address.${this.subscription().subscriptionId}`
                }
            );

            utils.extend(existingAddressComponent, {
                addresses: this.addresses,
                template:
                    'PayPal_Subscription/customer/addresses/existing-addresses-select'
            });
            layout([existingAddressComponent]);
        },

        initModal: function (element, ui) {
            var that = ui;
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: '',
                opened: function () {
                    that.newForm(window.subscriptionNewAddressForm);
                    that.modalContainer.find('.ps-add-address-form').trigger('contentUpdated');
                },
                closed: function () {
                    var newAddress = that.modalContainer.find('.ps-add-address-form__container');
                    var existingAddresses = that.modalContainer.find('.ps-existing-addresses');

                    newAddress.hide();
                    existingAddresses.show();
                    that.newForm('');
                }
            };
            this.modalContainer = $(element);

            modal(options, this.modalContainer);
        },

        /**
         * @return {exports}
         */
        initialize: function () {
            this._super();
        },

        showAddressModal: function () {
            this.modalContainer.modal('openModal');
        },

        getExistingAddresses: function () {
            return window.subscriptionAddresses;
        },

        removeUnusedValues: function (addressResponseObject) {
            delete addressResponseObject['customer_id'];
            delete addressResponseObject['id'];
            delete addressResponseObject['region_id'];
            delete addressResponseObject['region'];
            for (var key in addressResponseObject) {
                if (addressResponseObject.hasOwnProperty(key)) {
                    if (addressResponseObject[key] == null) {
                        delete addressResponseObject[key];
                    }
                }
            }
            return addressResponseObject;
        },

        handleFormSubmit: function (addressId = false) {
            var that = this;
            var subscriptionId = this.subscription().subscriptionId;

            $('body').trigger('processStart');

            postAddressForm('.ps-new-address-modal #form-validate', subscriptionId, addressId)
                .done(function (response) {
                    var address = that.removeUnusedValues(response);
                    addressBuilder.shippingAddress(Object.values(address));
                    // Add address to the default addresses
                    that.subscription().shippingAddressHtml(that.formatAddress(address));
                    customerData.set('messages', {
                        messages: [
                            {
                                text: 'Your address has been updated.',
                                type: 'success'
                            }
                        ]
                    });
                })
                .fail(function () {
                    customerData.set('messages', {
                        messages: [
                            {
                                text: 'Unable to update your address. Please try again.',
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

        saveNewAddress: function () {
            if (!validateForm('.ps-new-address-modal #form-validate')) {
                return;
            }

            this.handleFormSubmit();
        },

        setShippingAddress: function (element, addressId) {
            this.handleFormSubmit(addressId);
        },

        /**
         * Format address
         * @param element
         * @returns {*}
         */
        formatAddress: function(address) {
            // Return formatted addresses
            let sortedAddressData = [
                address['firstname'] + ' ' + address['lastname'],
                address['street'].join(',<br/>'),
                address['city'],
                address['postcode'],
                address['country_id'],
                address['telephone']
            ];
            return sortedAddressData.join(',<br/>');
        },

        showNewAddress: function (element) {
            var modalContainer = element.containers[0].modalContainer;
            var newAddress = modalContainer.find('.ps-add-address-form__container');
            var existingAddresses = modalContainer.find('.ps-existing-addresses');

            newAddress.show();
            existingAddresses.hide();
        },

        showExistingAddress: function () {
            var newAddress = this.modalContainer.find('.ps-add-address-form__container');
            var existingAddresses = this.modalContainer.find('.ps-existing-addresses');

            newAddress.hide();
            existingAddresses.show();
        }
    });
});
