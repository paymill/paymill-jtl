PAYMILL - JTL
====================

PAYMILL extension (credit card and direct debit) for JTL (Version 3.1.5).

# Installation

## Installation from this git repository

Use the following link to download the module:

    https://github.com/Paymill/Paymill-JTL/archive/master.zip

Extract the content of the zip file and merge the content of the folder "paymill-jtl-master" with your JTL plugins folder (<shop_root>/includes/plugins).

# Configuration

- In the main menu goto **Plugins -> Pluginverwaltung**
- Select module "PAYMILL" and choose **Installieren**
- In the main menu goto **Plugins -> Plugins -> PAYMILL**
- Enter your PAYMILL Test- or Livekeys and click on **Speichern**

# Activate PAYMILL Payment

To activate PAYMILL payment follow these steps:

- In the main menu goto **Kaufabwicklung > Zahlungsarten**
- In the main menu goto **Kaufabwicklung > Versandarten**
- Click on **ändern**
- Select the payment you want and press **Versandart ändern**

# Refund

To trigger a paymill refund you must cancel the order in the jtl wawi

# Error handling

In case of any errors turn on the debug mode in the PAYMILL payment method configuration. Open the javascript console in your browser and check what's being logged during the checkout process.

# Notes about the payment process

The payment is processed when an order is placed in the shop frontend.

Fast Checkout: Fast checkout can be enabled by selecting the option in the PAYMILL Basic Settings. If any customer completes a purchase while the option is active this customer will not be asked for data again. Instead a reference to the customer data will be saved allowing comfort during checkout.