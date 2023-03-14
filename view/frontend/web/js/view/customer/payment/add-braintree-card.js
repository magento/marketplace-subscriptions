define([
    'uiComponent',
    'jquery',
    'underscore',
    'PayPal_Braintree/js/view/payment/adapter',
    'mage/translate',
    'PayPal_Subscription/js/model/url-builder',
    'PayPal_Subscription/js/action/customer/payment/load-client-token',
    'ko'
], function(
    Component,
    $,
    _,
    braintree,
    $t,
    urlBuilder,
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
        additionalData: {},

        /**
         * @inheritDoc
         */
        initialize: function (uiConfig) {
            this._super();

            this.uiConfig = uiConfig;
            this.subscriptionId = uiConfig.subscriptionId;
            this.message = ko.observable(false);

            this.numberSelector = ko.observable('paypal-subscriptions-cc-number');
            this.expirationMonth = ko.observable('paypal-subscriptions-expiration-mm');
            this.expirationYear = ko.observable('paypal-subscriptions-expiration-yy');
            this.cvv = ko.observable('paypal-subscriptions-cvv');


            var that = this;

            this.clientConfig = {
                additionalData: {},

                /**
                 * Device data initialization
                 * @param {String} deviceData
                 */
                onDeviceDataRecieved: function (deviceData) {
                    this.additionalData['device_data'] = deviceData;
                },

                /**
                 * Triggers on any Braintree error
                 * @param {Object} response
                 */
                onError: function (response) {
                    that.message($t('Please enter a valid card number, expiry date and CVV Number.'));
                },

                /**
                 * Triggers when customer click "Cancel"
                 */
                onCancelled: function () {
                    that.message($t("The process has been cancelled"));
                },

                onReady: function (context) {
                    context.setupHostedFields();
                },

                /**
                 * After Braintree instance initialization
                 */
                onInstanceReady: function () {},

                id: 'co-transparent-form-braintree',
                hostedFields: {
                    number: {
                        selector: '#' + this.numberSelector(),
                        placeholder: $t('4111 1111 1111 1111')
                    },
                    expirationMonth: {
                        selector: '#' + this.expirationMonth(),
                        placeholder: $t('MM')
                    },
                    expirationYear: {
                        selector: '#' + this.expirationYear(),
                        placeholder: $t('YY')
                    },
                    cvv: {
                        selector: '#' + this.cvv(),
                        placeholder: $t('123')
                    }
                },

                onPaymentMethodReceived: function (response) {
                    $.ajax({
                        method: "PUT",
                        url: '/rest/V1/subscription/mine/payment/creditcard/' + that.subscriptionId,
                        data: JSON.stringify({nonce: response.nonce}),
                        contentType: "application/json",
                        dataType: "json"
                    })
                    .done(function(response) {
                        location.reload();
                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        that.message($t('Sorry, but something went wrong when taking the payment.'))
                    });
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
            $.ajax({
                method: "GET",
                url: '/rest/V1/subscription/braintree/token/client'
            })
            .done(function(response) {
                that.clientToken = response.token;
                that.setup();
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                that.message($t('Sorry, but something went wrong when connecting to Braintree.'))
            });
        },

        /**
         * Get Client Token
         * @returns {null}
         */
        getClientToken: function () {
            return this.clientToken;
        },

        /**
         * TokenizeHostedFields Response
         */
        tokenizeHostedFields: function () {
            var that = this;
            $('body').trigger('processStart');
            // Reset Message
            that.message();

            // Tokenize
            this.hostedFieldsInstance.tokenize({}, function (tokenizeErr, payload) {
                if (tokenizeErr) {
                    $('body').trigger('processStop');
                    switch (tokenizeErr.code) {
                        case 'HOSTED_FIELDS_FIELDS_EMPTY':
                            that.message($t('All fields are empty! Please fill out the form.'));
                            break;
                        case 'HOSTED_FIELDS_FIELDS_INVALID':
                            that.message($t('Some fields are invalid'));
                            break;
                        case 'HOSTED_FIELDS_TOKENIZATION_FAIL_ON_DUPLICATE':
                            that.message($t('This payment method already exists in your vault.'));
                            break;
                        case 'HOSTED_FIELDS_TOKENIZATION_CVV_VERIFICATION_FAILED':
                            that.message($t('CVV did not pass verification'));
                            break;
                        default:
                            that.message($t('Something went wrong.'));
                    }
                } else {
                    $('body').trigger('processStop');
                    this.config.onPaymentMethodReceived(payload);
                }
            }.bind(this));
        }

    });

    return Component.extend(uiC);
});