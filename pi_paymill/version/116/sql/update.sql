CREATE TABLE `xplugin_pi_paymill_log` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`identifier` text NOT NULL,
`debug` text NOT NULL,
`message` text NOT NULL,
`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`id`)
) AUTO_INCREMENT=1;