CREATE TABLE `xplugin_pi_paymill_transaction` (
    `order_id` varchar(100),
    `transaction_id` varchar(100),
    `amount` varchar(100),
    `payment_code` varchar(100),
    PRIMARY KEY (`order_id`)
);