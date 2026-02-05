# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.5.0] - 2025-08-20
### Added
- PHP 8.4 Support
- Amended logic for handling whether card should be stored or not
- Fixed incorrect publish hash issue for existing vault token for Braintree PayPal (braintree_paypal).

## [3.4.0] - 2024-12-18
### Added
- Check and disable "Save for later" checkbox on payment step in checkout if cart contains any subscription items 
- Add message to tooltip for user
- Create plugin to prevent following integrity constraint violation during order placement when vaulted token has been created via customer account page

## [3.3.0] - 2024-04-16
### Added
- Fixes to prevent reminder email being duplicated per cron run
- Fixes to log error messaging and remove cron run flag on exception

## [3.2.0] - 2024-03-05
### Added
- Automatic price update feature:
  - Add config setting to enable automatic price updates on subscriptions
  - When enabled, subscription releases use latest product price information.
  - When disabled, subscription releases use the price associated to original subscription order.
  - If auto price update enabled and price has changed since last release, a price update email is sent to customer.

## [3.1.0] - 2024-03-05
### Added
- Fixes for subscription release order totals

## [3.0.0] - 2024-02-02
### Added
- Add subscription support for Bundle products that meet the following criteria:
  - Fixed price
  - Maximum of 1 product per option
  - Maximum of 0 products with "user defined" enabled
- Add subscription support for Virtual Product Type
