define([
    'uiComponent',
    'jquery',
    'underscore',
    'PayPal_Braintree/js/view/payment/adapter',
    'mage/translate',
    'PayPal_Subscription/js/action/customer/payment/update-braintree-paypal-payment',
    'PayPal_Subscription/js/action/customer/payment/load-client-token',
    'ko'
], function(
    Component,
    $,
    _,
    braintree,
    $t,
    updatePayment,
    loadClientToken,
    ko
) {

    /**
     * braintree is not an instance of Component so we need to merge in our changes
     * and return an instance of Component with the final merged object.
     */
    var uiC = _.extend(braintree, {
        clientToken: null,
        uiConfig: null,
        paymentMethodNonce: null,

        /**
         * @inheritDoc
         */
        initialize: function (uiConfig) {
            this._super();
            this.uiConfig = uiConfig;
            this.merchantName = uiConfig.merchantName;
            this.locale = uiConfig.locale;
            this.currency = uiConfig.currency;
            this.orderAmount = uiConfig.orderAmount;
            this.updatePaymentUrl = uiConfig.updatePaymentUrl;
            this.subscriptionId = uiConfig.subscriptionId;
            this.message = ko.observable(false);
            var that = this;

            this.clientConfig = {

                additionalData: {},
                buttonId: 'paypal_container',

                /**
                 * Device data initialization
                 * @param {String} deviceData
                 */
                onDeviceDataRecieved: function (deviceData) {
                    this.additionalData['device_data'] = deviceData;
                },

                /**
                 * Triggers when widget is loaded
                 * @param {Object} context
                 */
                onReady: function (context) {
                    context.setupPaypal();
                },

                /**
                 * Triggers on any Braintree error
                 * @param {Object} response
                 */
                onError: function (response) {
                    that.message($t('Something went wrong'));
                    throw response.message;
                },

                /**
                 * Triggers when customer click "Cancel"
                 */
                onCancelled: function () {
                    that.message($t("The process has been cancelled"));
                },

                onPaymentMethodReceived: function (response) {
                    updatePayment(response.nonce, that.subscriptionId).success(function () {
                        location.reload();
                    }).error(function () {
                        that.message($t('Sorry, but something went wrong when taking the payment.'))
                    });
                },

                dataCollector: {
                    paypal: true
                },

                paypal: {
                    container: 'paypal_container',
                    flow: 'vault',
                    singleUse: false,
                    amount: that.orderAmount,
                    currency: that.currency,
                    locale: that.locale,
                    enableShippingAddress: false,
                    displayName: that.merchantName,

                    /**
                     * Triggers on any Braintree error
                     */
                    onError: function () {
                        this.paymentMethodNonce = null;
                    },

                    /**
                     * Triggers if browser doesn't support PayPal Checkout
                     */
                    onUnsupported: function () {
                        this.paymentMethodNonce = null;
                    }
                }
            };

            this.setConfig(this.clientConfig);
            this.loadClientToken();
        },

        /**
         * Retrieve client token from server
         */
        loadClientToken: function () {
            var that = this;

            // Get Client token
            loadClientToken().done(function(response) {
                that.clientToken = response.token;
                that.setup();
            }).error(function () {
                that.message($t('Sorry, but something went wrong when connecting to Braintree.'))
            });
        },

        /**
         * @inheritDoc
         */
        getClientToken: function () {
            return this.clientToken;
        },

        /**
         * @returns {String}
         */
        getColor: function () {
            return this.color;
        },

        /**
         * @returns {String}
         */
        getShape: function () {
            return this.shape;
        },
        /**
         * @returns {String}
         */
        getLayout: function () {
            return this.layout;
        },

        /**
         * @returns {String}
         */
        getSize: function () {
            return this.size;
        },

        /**
         * @returns {String}
         */
        getEnvironment: function () {
            return this.environment;
        },

        /**
         * @returns {String}
         */
        getDisabledFunding: function () {
            return this.disabledFunding;
        }

    });

    return Component.extend(uiC);
});