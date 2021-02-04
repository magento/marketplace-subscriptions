/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/insert-form'
], function (Insert) {
    'use strict';

    return Insert.extend({
        defaults: {
            listens: {
                responseData: 'onResponse'
            },
            modules: {
                addressListing: '${ $.addressListingProvider }',
                addressModal: '${ $.addressModalProvider }'
            }
        },

        /**
         * Close modal, reload customer address listing and save customer address
         *
         * @param {Object} responseData
         */
        onResponse: function (responseData) {
            var data;

            if (!responseData.error) {
                this.addressModal().closeModal();
                data = this.externalSource().get('data');
                this.saveAddress(responseData, data);
            }
        },

        /**
         * Save customer address to customer form data source
         *
         * @param {Object} responseData
         * @param {Object} data - customer address
         */
        saveAddress: function (responseData, data) {
            data['entity_id'] = responseData.data['entity_id'];

            location.reload();
        },

        /**
         * Event method that closes "Edit customer address" modal and refreshes grid after customer address
         * was removed through "Delete" button on the "Edit customer address" modal
         *
         * @param {String} id - customer address ID to delete
         */
        onAddressDelete: function (id) {
            this.addressModal().closeModal();
            this.addressListing().reload({
                refresh: true
            });
            this.addressListing()._delete([parseFloat(id)]);
        }
    });
});
