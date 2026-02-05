var config = {
    config: {
        mixins: {
            'PayPal_Braintree/js/view/payment/method-renderer/hosted-fields': {
                'PayPal_Subscription/js/view/payment/method-renderer/hosted-fields-mixin': true
            }
        }
    },
    map: {
        '*': {
            customerSubscriptionList: 'PayPal_Subscription/js/view/customer/subscription-list',
            customerDisabledSubscriptionList: 'PayPal_Subscription/js/view/customer/disabled-subscription-list',
        }
    }
};
