# Helcim Payment Plugin for Drupal 8 Community Edition

You can sign up for a Helcim account at https://www.helcim.com/

## Requirements

Requires Drupal 8.x installation: https://www.drupal.org/download
Requires Ubercart 4.x installation: https://www.drupal.org/project/ubercart

## Helcim Commerce Setup

In order to utilize the Helcim Commerce API, you must enter your Helcim Commerce account ID as well as an API token in the Drupal plugin setup. For instructions on generating an API token with the correct permissions, please visit: https://www.helcim.com/support/article/627-helcim-commerce-api-enabling-api-access/

## Drupal Plugin Installation

- Copy and paste the Helcim folder in the <your Drupal8 install dir>/sites/all/modules/ubercart/payment Directory.

## Drupal Setup

- Login to your Drupal8 website
- When Ubercart is installed, in the upper-left corner click Manage -> Extend
- Enable the following plugins:
	- Under Ubercart - Core check the following:
 		- Cart, Country, Order, Product, Store
	- Under Ubercart - Core(Optional) check the following:
		- Payment, Shipping Quotes, Tax
	- Under Ubercart - Payment check the following:
		- Helcim Credit Card
	- At the bottom of the page click the blue Install button
- Manage -> Store
- Configuration -> Payment methods
- Choose Helcim gateway under ADD PAYMENT METHOD
- Label your payment method
- Enter your Helcim JS Token
- Enter your Account Id
- Enter your Helcim Commerce API Token
- Enter your Terminal Id
- Choose whether Test Mode is on or off
- Save

## Testing

Please visit https://www.helcim.com/ to create a developer sandbox account.

## SSL/TLS

Please note that the Helcim Commerce platforms requires Transport Layer Security (TLS) version 1.2 to process payments. Any older versions (TLS1.1 / TLS1.0 / SSLv3) will have connections rejected.

For more information on Helcim's API, visit: https://www.helcim.com/support/article/625-helcim-commerce-api-api-overview/