define([
    'uiComponent',
    'jquery',
    'ko',
    'PayPal_Subscription/js/action/set-item-interval'
], function(
    Component,
    $,
    ko,
    setItemInterval
) {
    return Component.extend({

        items: {},

        /**
         * Initialise
         */
        initialize: function () {
            this._super();
        },

        /**
         * Alternate true / false for dropdown
         */
        setShowSubscriptionEdit: function (itemId) {

            if (!this.items.hasOwnProperty(itemId)) {

                // Add item to the object
                this.items[itemId] = ko.observable(false);

            } else {

                // Alternate true or false value
                this.items[itemId](!this.items[itemId]());
            }
        },

        /**
         * Update Interval Value with ajax
         * @param item
         * @param data
         * @param event
         */
        updateInterval: function(item) {

            // Get Updated Frequency from select
            var updatedFrequency = $("[data-item-select='" + item.item_id +"']").val();

            // Update Frequency
            $("[data-item-frequency='" + item.item_id +"']").text(updatedFrequency);

            // Set item frequency
            setItemInterval(item, updatedFrequency);

        },

        /**
         * Return the Options
         * @param item
         * @returns {Array}
         */
        setOptionValues: function (item) {

            var options = [];

            // Foreach option in frequency_options then set new option
            $.each(JSON.parse(item.frequency_options), function (key, value) {

                var newOption = {
                    value: value.interval,
                    label: value.name
                };

                options.push(newOption);

            });

            return options;
        },

        /**
         * Get Item Id
         * @param item
         * @returns {string}
         */
        getItemId: function (item) {
          return item.item_id;
        }
    });
});