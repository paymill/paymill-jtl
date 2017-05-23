## v1.3.1
* Remove CURLOPT_CAINFO - path to Certificate Authority (CA) bundle
* Remove paymill.crt
* Change user agent info to "Paymill-JTL/1.3.1"

## v1.3.0
 * add paymill refund via wawi "storno"

## v1.2.1
 * set description limit to 128 cahrs
 * automcopletition false for payment forms

## v1.2.0
 * merge elv/sepa to one payment form
 * selective brand detection via payment config
 * prenotification information in the order email

## v1.1.9
 * move payment form to confirm page
 * redesign payment form

## v1.1.8
 * improved early creditcard validation with greyscale logos
 * added Iban Validation
 * added language Support for German, English, Spanish, French, Italian

## v1.1.7
 * add bridge error translations

## v1.1.6
 * fast checkout guest checkout bugfix

## v1.1.5
 * remove paymill label
 * show cc icons inside the input field
 * don't show paymill if one or both keys are empty
 * better error messages
 * add validateHolder() for cardholder validation
 * fastcheckout only with existing payments
 * update readme

## v1.1.4
 * fixed not filled payment form when fastcheckout is deactivated

## v1.1.3
 * fixed Maestro Label
 * fixed different amount, now dot and comma allowed
 * card-holder name changed to cardholder

## v1.1.2
 * fix double table creation
 * add dynamic cc logos
 * add unique id's for the payment form instead of css classes

## v1.1.1
 * call "baueBestellnummer()" just one time

## v1.1.0
 * new paymill logo
 * add fast checkout
 * fix install.sql
 * fix bridge error message box
 * add trim to the keys

## v1.0.9
 * remove css and js from the html header and place in the payment section
 * fix wrong error message

## v1.0.8
 * rework 3DSecure fallback
 * update readme

## v1.0.7
 * php lib update
 * log response code

## v1.0.6
 * fix worng field amount

## v1.0.5

## v1.0.4
 * add logging and debug

## v1.0.3
 *  add 3DSecure fallback
 *  add shipping tax to token

## v1.0.2
 * remove windows line endings
 * update PHP lib

## v1.0.1
 * change path to php lib
 * fix ssl problem
 * fix agb problem

## v1.0.0
 * initial release
