CREATE TABLE IF NOT EXISTS `paymill_fastcheckout` (
  `userID` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `clientID` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `paymentID_CC` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `paymentID_ELV` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`userID`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;