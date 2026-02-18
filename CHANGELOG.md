## [7.0.0] - 2026-02-18

* Bumped firebase/php-jwt from ^6.1.0 to ^7.0.0 to bring in security patches
* Removed support for PHP 7

## [6.2.0] - 2024-07-29

### Added

* Added fields related to cost data in response:
  * `is_cost_data_ready`: This field is true if cost data is ready, and false if it isn't (Boolean).
  * `cost_in_pounds`: Cost of the notification in pounds. The cost does not take free allowance into account (Float).
  * `cost_details.billable_sms_fragments`: Number of billable SMS fragments in your text message (SMS only) (Integer).
  * `cost_details.international_rate_multiplier`: For international SMS rate is multiplied by this value (SMS only) (Integer).
  * `cost_details.sms_rate`: Cost of 1 SMS fragment (SMS only) (Float).
  * `cost_details.billable_sheets_of_paper`: Number of sheets of paper in the letter you sent, that you will be charged for (letter only) (Integer).
  * `cost_details.postage`: Postage class of the notification sent (letter only) (String).

## [6.1.0] - 2024-5-10

* Adds `oneClickUnsubscribeURL` parameter to `sendEmail`

## [6.0.0] - 2023-12-27

* Removes the `is_csv` parameter from `prepareUpload`
* Adds a `filename` parameter to `prepareUpload` to set the filename of a document when it is downloaded. See the documentation for more information.

## [5.0.0] - 2022-11-25
### Removed

* Removed guzzle5-adapter and guzzle6-adapter documentation and support; this version now supports any PSR-7 compatible client library. Our documentation provides examples for using guzzle7.

## [4.2.0] - 2022-09-27
### Added

* Add support for new security features when sending a file by email:
  * `confirm_email_before_download` can be set to `true` to require the user to enter their email address before accessing the file.
  * `retention_period` can be set to `<1-78> weeks` to set how long the file should be made available.

## [4.1.0] - 2022-07-01
### Added

* Added PHP 8 to supported PHP versions in `composer.json`. Support for PHP >7.1 is maintained. No code changes or updates to non-dev dependencies are required.

### Changed

* Updated dev dependencies:
    * Updated `phpspec/phpspec` to ^7.2 (^5.0 permitted for PHP 7.1 users)
    * Updated `php-http/socket-client` to ^2.1.0

See https://github.com/alphagov/notifications-php-client/pull/115 for further context

## [4.0.1] - 2022-04-07
### Added

* Added explicit requirement for PHP versions < 7.1 to composer.json

See https://github.com/alphagov/notifications-php-client/pull/107 for further context

## [4.0.0] - 2022-04-06
### Removed

* Removed support for PHP versions < 7.1
* Bumped firebase/php-jwt from ^5.0.0 to ^v6.1.0 to bring in security patches

See https://github.com/alphagov/notifications-php-client/pull/107 for further context

## [3.2.0] - 2020-08-06
### Added

* Added `letter_contact_block` to the responses for `getTemplate`, `getTemplateVersion` and `listTemplates`.

## [3.1.0] - 2020-08-04
### Added

* Add support for an optional `is_csv` parameter in the `prepareUpload()` function. This fixes a bug when sending a CSV file by email. This ensures that the file is downloaded as a CSV rather than a TXT file.

## [3.0.1] - 2020-03-31
### Removed

* Bumped firebase/php-jwt from 3.0.0 to 5.0.0 to bring in latest bugfixes and features

## [3.0.0] - 2020-02-25
### Removed

* Dropped support for ^1.0 releases of HTTPlug (phphttp/httplug)
    * HTTP clients may now use the `Psr\Http\Client\ClientInterface` (found in
    ^2.0 releases of HTTPlug) rather than `Http\Client\HttpClient` interface
    (found in ^1.0 releases of HTTPlug).

## [2.1.2] - 2020-01-27

### Changed

* Refer to files, not documents in error messages.

## [2.1.1] - 2019-11-04

### Added

* Add new method `getPdfForLetter`
    * requires $notificationId, returns a string with the pdf data for that letter

## [2.0.0] - 2019-10-01
### Removed

* Dropped official support for PHP versions older than 7.1.

### Changed

* Removed unused dependencies from `composer.json`

## [1.9.0] - 2019-01-28
### Changed

* Added a way to set postage when sending precompiled letters:
  * set (optional) `$postage` argument on `sendPrecompiledLetter`

## [1.8.0] - 2018-11-01
### Changed

* Added support for httplug 2.0

## [1.7.0] - 2018-09-11
### Changed

* Added support for document uploads in sendEmailNotification.
    * Call `$this->prepareUpload`, and add the return value to your `$personalisation` array.
* The client can now send PDF files which conform to the Notify printing template
    * call the `SendLetterResponse sendLetter( $reference, $pdf_data )` function
    * `$reference` must be provided to identify the document
    * `$pdf_data` is a string, as returned from `file_get_contents` for example

## [1.6.2] - 2017-12-05
### Changed

* Moved dependency on client to require-dev - `php-http/guzzle6-adapter`

## [1.6.1] - 2017-12-05
### Changed

* Removed versions from `composer.json` to make it easier to install for users.

## [1.6.0] - 2017-11-27
### Changed

* Added `$this->listReceivedTexts()`
    * an optional `older_than` argument can be specified to retrieve the next 250 received text messages older than the given
    received text id. If omitted 250 of the most recent received text messages are returned.

## [1.5.0] - 2017-11-07
### Changed

* sendLetter added to Client.php
    * `SendLetterResponse sendLetter( $templateId, array $personalisation = array(), $reference = '' )`
    * personalisation map is required, and must contain the recipient's address details.
    * as with sms and email, reference is optional.

## [1.4.0] - 2017-11-03
### Changed

* Update to `$this->sendSms()`
    * added `smsSenderId`: an optional sms_sender_id specified when adding SMS senders under service settings. If this is not provided, the SMS sender will be the service default SMS sender. `smsSenderId` can be omitted.

## [1.3.0] - 2017-11-06
### Changed

* Update to `Alphagov\Notifications\Exception\ApiException` - added two new methods
    * added `getErrors()` to retrieve the original errors array from the json response.
    * added `getErrorMessage()` to retrieve a nicely formatted message

## [1.2.0] - 2017-10-25
### Changed

* Update to `$this->sendEmail()`
    * added `emailReplyToId`: an optional email_reply_to_id specified when adding Email reply to addresses under service settings, if this is not provided the reply to email will be the service default reply to email. `emailReplyToId` can be omitted.

## [1.1.0] - 2017-05-10
### Changed

* Added new methods for managing templates:
    * `$this->getTemplate` - retrieve a single template
    * `$this->getTemplateVersion` - retrieve a specific version for a desired template
    * `$this->listTemplates` - retrieve all templates (can filter by type)
    * `$this->previewTemplate` - preview a template with personalisation applied

## [1.0.0] - 2016-12-16
### Changed
* Using v2 of the notification-api.

* Update to `$this->sendSms()`:
    * Added `reference`: an optional identifier you generate if you don’t want to use Notify’s `id`. It can be used to identify a single notification or a batch of notifications.
    * Updated method signature:

 ```php
public function sendSms( $phoneNumber, $templateId, array $personalisation = array(), $reference = '' )
```
     * Where `$personalisation` and `$reference` can be omitted.

* Update to `$this->sendEmail()`:
    * Added `reference`: an optional identifier you generate if you don’t want to use Notify’s `id`. It can be used to identify a single notification or a batch of notifications.
    * Updated method signature:

 ```php
public function sendEmail( $emailAddress, $templateId, array $personalisation = array(), $reference = '' )
```
     * Where `$personalisation` and `$reference` can be omitted.
* Updated `$this->listNotifications()`
    * Notifications can now be filtered by `reference` and `older_than`, see the README for details.

# Prior versions

Changelog not recorded - please see pull requests on github.
