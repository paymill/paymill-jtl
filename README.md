Paymill-JTL
====================

Paymill extension (credit card and direct debit) for JTL (Version 3.1.5).

![Paymill creditcard payment form](https://raw.github.com/Paymill/Paymill-JTL/master/Paymill-JTL-Module/paymill_cc_form_de.png)
![Paymill creditcard payment form](https://raw.github.com/Paymill/Paymill-JTL/master/Paymill-JTL-Module/paymill_elv_form_de.png)

# Installation

## Installation from this git repository

Use the following link to download the module:

    https://github.com/Paymill/Paymill-JTL/archive/master.zip

Afterwards merge the contents of the Paymill-JTL-Module directory with your JTL plugins folder (<shop_root>/includes/plugins).

# Configuration

- In the main menu goto **Plugins -> Pluginverwaltung**
- Select module "Paymill" and choose **Installieren**
- In the main menu goto **Plugins -> Plugins -> Paymill**
- Enter your Paymill Test- or Livekeys and click on **Speichern**
- The field **Paymill API URL** should contain https://api.paymill.com/v2/
- The field **Paymill Bridge URL** should contain https://bridge.paymill.com/

# Activate Paymill Payment

To activate Paymill payment follow these steps:

- In the main menu goto **Kaufabwicklung > Zahlungsarten**
- In the main menu goto **Kaufabwicklung > Versandarten**
- Click on **ändern**
- Select the payment you want and press **Versandart ändern**

# Error handling

In case of any errors turn on the debug mode in the Paymill payment method configuration. Open the javascript console in your browser and check what's being logged during the checkout process.

# Notes about the payment process

The payment is processed when an order is placed in the shop frontend.