# PayPal Subscription 1.0.0

Once a Braintree account has been created and the supporting Payments module installed, merchants can enable the subscription functionality on any of their products in Magento. Orders will be created within Magento on an automated basis with payments being processed though the Braintree PayPal or Card payment methods.

## Manual Release
Subscriptions can be manually release by admins (or those with the correct ACL).
Simply find the Subscription in the Subscription Listing Grid, click 'View/Edit' and click 'Release Subscription'.
A confirmation box will appear and if confirmed, the Subscription will be published to the Message Queue for
immediate release.

## Release via Console Command
A Subscription can be pushed to the message queue for immediate release, using the following command:

`bin/magento paypal:subscription:release --id <SUBSCRIPTION_ID>`
