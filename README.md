Paymill-xtCommerce-3
====================

xtCommerce (Version 3.x) Plugin for Paymill credit card and elv payments

# Installation

Important: Use the following command to clone the complete repository including the submodules:
    
    git clone --recursive https://github.com/Paymill/Paymill-xtCommerce-3.git

# Configuration

Afterwards enable Paymill in your shop backend and insert your test or live keys. In the configuration set API-URL to https://api.paymill.de/v2/.

# In case of errors

Make sure the logfile (includes/modules/payment/paymill/log.txt) is writable. In case of any errors check this files contents and contact the Paymill support (support@paymill.de).

# Notes about the payment process

The payment is processed when an order is placed in the shop frontend.