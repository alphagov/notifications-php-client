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
