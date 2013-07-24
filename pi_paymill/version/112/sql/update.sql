CREATE TABLE `xplugin_pi_paymill_tfastcheckout` (
  `userID` varchar(100),
  `clientID` varchar(100),
  `paymentID_CC` varchar(100),
  `paymentID_ELV` varchar(100),
  PRIMARY KEY (`userID`)
);

DELETE FROM xplugin_pi_paymill_tfastcheckout;