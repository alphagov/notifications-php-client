# PHP client documentation

This documentation is for developers interested in using the GOV.UK Notify PHP client to send emails, text messages or letters.

# Set up the client

The Notify PHP Client is based on a [PSR-7 HTTP model](https://www.php-fig.org/psr/psr-7/) [external link]. To install it, you must select your preferred HTTP client. You can follow these instructions to use [Guzzle v6 and v5](http://docs.guzzlephp.org/en/stable/) and [cURL](http://php.net/manual/en/book.curl.php) [external links].

## Guzzle v6

1. Use [Composer](https://getcomposer.org/)  [external link] to install the GOV.UK Notify PHP client. Run the following in the command line:

    ```sh
    composer require php-http/guzzle6-adapter alphagov/notifications-php-client
    ```

    You can now use the [autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading) [external link] to download the GOV.UK Notify PHP client.

1. Add the following code to your application to create a new instance of the client:

    ```
    $notifyClient = new \Alphagov\Notifications\Client([
        'apiKey' => '{your api key}',
        'httpClient' => new \Http\Adapter\Guzzle6\Client
    ]);
    ```

To get an API key, [sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and go to the __API integration__ page. You can find more information in the [API keys](#api-keys) section of this documentation.

## Guzzle v5

1. Use [Composer](https://getcomposer.org/)  [external link] to install the GOV.UK Notify PHP client. Run the following in the command line:

    ```sh
    composer require php-http/guzzle5-adapter php-http/message alphagov/notifications-php-client
    ```

    You can now use the [autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading) [external link] to download the GOV.UK Notify PHP client.

1. Add the following code to your application to create a new instance of the client:

    ```
    $notifyClient = new \Alphagov\Notifications\Client([
        'apiKey'        => '{your api key}',
        'httpClient'    => new \Http\Adapter\Guzzle5\Client(
            new \GuzzleHttp\Client,
            new \Http\Message\MessageFactory\GuzzleMessageFactory
        ),
    ]);
    ```

1. Run `$notifyClient` to access the GOV.UK Notify API.

To get an API key, [sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and go to the __API integration__ page. You can find more information in the [API keys](#api-keys) section of this documentation.

## cURL

1. Use [Composer](https://getcomposer.org/)  [external link] to install the GOV.UK Notify PHP client. Run the following in the command line:

    ```sh
    composer require php-http/curl-client php-http/message alphagov/notifications-php-client
    ```

You can now use the [autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading) [external link] to download the GOV.UK Notify PHP client.

1. Add the following code to your application to create a new instance of the client:

    ```
    $notifyClient = new \Alphagov\Notifications\Client([
        'apiKey'        => '{your api key}',
        'httpClient'    => new \Http\Client\Curl\Client(
            new \Http\Message\MessageFactory\GuzzleMessageFactory,
            new \Http\Message\StreamFactory\GuzzleStreamFactory
        ),
    ]);
    ```

1. Run `$notifyClient` to access the GOV.UK Notify API.

To get an API key, [sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and go to the __API integration__ page. You can find more information in the [API keys](#api-keys) section of this documentation.

# Send a message

You can use GOV.UK Notify to send text messages, emails and letters.

## Send a text message

### Method

```php
sendSms( $phoneNumber, $templateId, array $personalisation = array(), $reference = '', $smsSenderId = NULL  )
```

For example:

```php
try {
    $response = $notifyClient->sendSms(
        '+447777111222',
        'df10a23e-2c6d-4ea5-87fb-82e520cbf93a', [
            'name' => 'Betty Smith',
            'dob'  => '12 July 1968'
        ],
        'unique_ref123',
        '862bfaaf-9f89-43dd-aafa-2868ce2926a9'
    );

} catch (NotifyException $e){}
```

### Arguments

#### phoneNumber (required)

The phone number of the recipient of the text message. This can be a UK or international number.

#### templateId (required)

To find the template ID:

1. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in).
1. Go to the __Templates__ page and select the relevant template.
1. Select __Copy template ID to clipboard__.

#### personalisation (optional)

If a template has placeholder fields for personalised information such as name or application date, you must provide their values in a dictionary with key value pairs. For example:

```php
$personalisation = [
    'name' => 'Amala',
    'application_date'  => '2018-01-01'
];
```

You can leave out this argument if a template does not have any placeholder fields for personalised information.

#### reference (optional)

A unique identifier you can create if necessary. This reference identifies a single unique notification or a batch of notifications. It must not contain any personal information such as name or postal address. For example:

```php
$reference = 'STRING';
```
You can leave out this argument if you do not have a reference.

#### smsSenderId (optional)

A unique identifier of the sender of the text message notification.

To find the text message sender:

1. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in).
1. Go to the __Settings__ page.
1. In the __Text Messages__ section, select __Manage__ on the __Text Message sender__ row.

You can then either:

- copy the sender ID that you want to use and paste it into the method
- select __Change__ to change the default sender that the service will use, and select __Save__

```php
$smsSenderId='8e222534-7f05-4972-86e3-17c5d9f894e2'
```

You can leave out this argument if your service only has one text message sender, or if you want to use the default sender.

### Response

If the request to the client is successful, the client returns an `array`:

```php
[
    "id" => "bfb50d92-100d-4b8b-b559-14fa3b091cda",
    "reference" => None,
    "content" => [
        "body" => "Some words",
        "from_number" => "40604"
    ],
    "uri" => "https =>//api.notifications.service.gov.uk/v2/notifications/ceb50d92-100d-4b8b-b559-14fa3b091cd",
    "template" => [
        "id" => "ceb50d92-100d-4b8b-b559-14fa3b091cda",
       "version" => 1,
       "uri" => "https://api.notifications.service.gov.uk/v2/templates/bfb50d92-100d-4b8b-b559-14fa3b091cda"
    ]
];
```

If you are using the [test API key](#test), all your messages will come back with a `delivered` status.

All messages sent using the [team and whitelist](#team-and-whitelist) or [live](#live) keys will appear on your dashboard.

### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code.

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can't send to this recipient using a team-only API key"`<br>`]}`|Use the correct type of [API key](#api-keys).|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can't send to this recipient when service is in trial mode - see https://www.notifications.service.gov.uk/trial-mode"`<br>`}]`|Your service cannot send this notification in [trial mode](https://www.notifications.service.gov.uk/features/using-notify#trial-mode).|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`429`|`[{`<br>`"error": "RateLimitError",`<br>`"message": "Exceeded rate limit for key type TEAM/TEST/LIVE of 3000 requests per 60 seconds"`<br>`}]`|Refer to [API rate limits](#api-rate-limits) for more information.|
|`429`|`[{`<br>`"error": "TooManyRequestsError",`<br>`"message": "Exceeded send limits (LIMIT NUMBER) for today"`<br>`}]`|Refer to [service limits](#service-limits) for the limit number.|
|`500`|`[{`<br>`"error": "Exception",`<br>`"message": "Internal server error"`<br>`}]`|Notify was unable to process the request, resend your notification.|

## Send an email

### Method

```php
sendEmail( $emailAddress, $templateId, array $personalisation = array(), $reference = '', $emailReplyToId = NULL )
```
For example:

```php
try {

    $response = $notifyClient->sendEmail(
        'betty@example.com',
        'df10a23e-2c0d-4ea5-87fb-82e520cbf93c', [
            'name' => 'Betty Smith',
            'dob'  => '12 July 1968'
        ],
        'unique_ref123',
        '862bfaaf-9f89-43dd-aafa-2868ce2926a9'
        );

} catch (NotifyException $e){}
```


### Arguments

#### emailAddress (required)

The email address of the recipient.

#### templateId (required)

To find the template ID:

1. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in).
1. Go to the __Templates__ page and select the relevant template.
1. Select __Copy template ID to clipboard__.

#### personalisation (optional)

If a template has placeholder fields for personalised information such as name or reference number, you need to provide their values in a dictionary with key value pairs. For example:

```php
$personalisation = [
    'name' => 'Amala',
    'application_date'  => '2018-01-01'
];
```

You can leave out this argument if a template does not have any placeholder fields for personalised information.

#### reference (optional)

A unique identifier you can create if necessary. This reference identifies a single unique notification or a batch of notifications. It must not contain any personal information such as name or postal address.

```php
$reference = 'STRING';
```

You can leave out this argument if you do not have a reference.

#### emailReplyToId (optional)

This is an email address specified by you to receive replies from your users. You must add at least one reply-to email address before your service can go live.

To add a reply-to email address:

1. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in).
1. Go to the __Settings__ page.
1. In the __Email__ section, select __Manage__ on the __Reply-to email addresses__ row.
1. Select __Add reply-to address__.
1. Enter the email address you want to use, and select __Add__.

For example:

```php
$emailReplyToId='8e222534-7f05-4972-86e3-17c5d9f894e2'
```

You can leave out this argument if your service only has one email reply-to address, or you want to use the default email address.

## Send a file by email

Send files without the need for email attachments.

This is an invitation-only feature. [Contact the GOV.UK Notify team](https://www.notifications.service.gov.uk/support/ask-question-give-feedback) to enable this function for your service.

To send a file by email, add a placeholder field to the template and then upload a file. The placeholder field will contain a secure link to download the file.

The links are unique and unguessable. GOV.UK Notify cannot access or decrypt your file.

### Add a placeholder field to the template

1. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in).
1. Go to the __Templates__ page and select the relevant email template.
1. Select __Edit__.
1. Add a placeholder field to the email template using double brackets. For example:

"Download your file at: ((link_to_file))"

### Upload your file

The file you upload must be a PDF, CSV, text file or Microsoft Word document smaller than 2MB.. [Contact the GOV.UK Notify team](https://www.notifications.service.gov.uk/support/ask-question-give-feedback) if you need to send other file types.

Pass the file object as a value into the personalisation argument. For example:

```php
try {
    $file_data = file_get_contents('/path/to/my/file.pdf');

    $response = $notifyClient->sendEmail(
        'betty@example.com',
        'df10a23e-2c0d-4ea5-87fb-82e520cbf93c',
        [
            'name' => 'Betty Smith',
            'dob'  => '12 July 1968',
            'link_to_file' => $notifyClient->prepareUpload( $file_data )
        ]
    );

} catch (NotifyException $e){}
```

### Response

If the request to the client is successful, the client returns an `array`:

```
[
    "id" => "bfb50d92-100d-4b8b-b559-14fa3b091cda",
    "reference" => None,
    "content" => [
        "subject" => "SUBJECT TEXT",
        "body" => "MESSAGE TEXT",
        "from_email" => "SENDER EMAIL
    ],
    "uri" => "https://api.notifications.service.gov.uk/v2/notifications/ceb50d92-100d-4b8b-b559-14fa3b091cd",
    "template" => [
        "id" => "ceb50d92-100d-4b8b-b559-14fa3b091cda",
        "version" => 1,
        "uri" => "https://api.notificaitons.service.gov.uk/service/your_service_id/templates/bfb50d92-100d-4b8b-b559-14fa3b091cda"
    ]
];
```


### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code.

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can't send to this recipient using a team-only API key"`<br>`]}`|Use the correct type of [API key](#api-keys).|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can't send to this recipient when service is in trial mode - see https://www.notifications.service.gov.uk/trial-mode"`<br>`}]`|Your service cannot send this notification in [trial mode](https://www.notifications.service.gov.uk/features/using-notify#trial-mode).|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Unsupported file type '(FILE TYPE)'. Supported types are: '(ALLOWED TYPES)'"`<br>`}]`|Wrong file type. You can only upload .pdf, .csv, .txt, .doc or .docx files.|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "File did not pass the virus scan"`<br>`}]`|The file contains a virus.|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Service is not allowed to send documents"`<br>`}]`|Contact the GOV.UK Notify team.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct type of [API key](#api-keys).|
|`429`|`[{`<br>`"error": "RateLimitError",`<br>`"message": "Exceeded rate limit for key type TEAM/TEST/LIVE of 3000 requests per 60 seconds"`<br>`}]`|Refer to [API rate limits](#api-rate-limits) for more information.|
|`429`|`[{`<br>`"error": "TooManyRequestsError",`<br>`"message": "Exceeded send limits (LIMIT NUMBER) for today"`<br>`}]`|Refer to [service limits](#service-limits) for the limit number.|
|`500`|`[{`<br>`"error": "Exception",`<br>`"message": "Internal server error"`<br>`}]`|Notify was unable to process the request, resend your notification.|
|`N/A`|`Exception\InvalidArgumentException( 'File is larger than 2MB.' )`|The file is too big. Files must be smaller than 2MB.|

## Send a letter

### Prerequisites

When you add a new service it will start in [trial mode](https://www.notifications.service.gov.uk/features/trial-mode). You can only send letters when your service is live.

To send Notify a request to go live:

1. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in).
1. Go to the __Settings__ page.
1. In the __Your service is in trial mode__ section, select __request to go live__.

### Method

```php
sendLetter( $templateId, array $personalisation = array(), $reference = '' )
```
For example:

```php
try {

    $response = $notifyClient->sendEmail(
        'df10a23e-2c0d-4ea5-87fb-82e520cbf93c',
        [
            'name'=>'Fred',
            'address_line_1' => 'Foo',
            'address_line_2' => 'Bar',
            'postcode' => 'Baz'
        ],
        'unique_ref123'
    );

} catch (NotifyException $e){}
```

### Arguments

#### templateId (required)

To find the template ID:

1. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in).
1. Go to the __Templates__ page and select the relevant template.
1. Select __Copy template ID to clipboard__.

#### personalisation (required)

The personalisation argument always contains the following required parameters for the letter recipient’s address:

- `address_line_1`
- `address_line_2`
- `postcode`

Any other placeholder fields included in the letter template also count as required parameters. You need to provide their values in a dictionary with key value pairs. For example:

```php
$personalisation =
          [
            'address_line_1' => '123 High Street',
            'address_line_2' => 'Richmond',
            'postcode' => 'W4 1FH',
            'name' => 'John Smith',
            'application_id' => '4134325'
          ];
```

#### reference (optional)

A unique identifier you can create if necessary. This reference identifies a single unique notification or a batch of notifications. It must not contain any personal information such as name or postal address. For example:

```php
$reference = 'STRING';
```

#### personalisation (optional)

The following parameters in the letter recipient’s address are optional:

```php
$personalisation =
    [
    'address_line_3' => '123 High Street',
    'address_line_4' => 'Richmond upon Thames',
    'address_line_5' => 'London',
    'address_line_6' => 'Middlesex',
    ];
```

### Response

If the request to the client is successful, the client returns an `array`:

```php
[
    "id" => "bfb50d92-100d-4b8b-b559-14fa3b091cda",
    "reference" => "unique_ref123",
    "content" => [
        "subject" => "Licence renewal",
        "body" => "Dear Bill, your licence is due for renewal on 3 January 2016.",
    ],
    "uri" => "https://api.notifications.service.gov.uk/v2/notifications/ceb50d92-100d-4b8b-b559-14fa3b091cd",
    "template" => [
        "id" => "ceb50d92-100d-4b8b-b559-14fa3b091cda",
        "version" => 1,
        "uri" => "https://api.notifications.service.gov.uk/service/your_service_id/templates/bfb50d92-100d-4b8b-b559-14fa3b091cda"
    ],
    "scheduled_for" => null
];
```

### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code.

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Cannot send letters with a team api key"`<br>`]}`|Use the correct type of [API key](#api-keys)|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Cannot send letters when service is in trial mode - see https://www.notifications.service.gov.uk/trial-mode"`<br>`}]`|Your service cannot send this notification in  [trial mode](https://www.notifications.service.gov.uk/features/using-notify#trial-mode)|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "personalisation address_line_1 is a required property"`<br>`}]`|Ensure that your template has a field for the first line of the address, check [personalisation](#send-a-letter-arguments-personalisation-optional) for more information.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information|
|`429`|`[{`<br>`"error": "RateLimitError",`<br>`"message": "Exceeded rate limit for key type TEAM/TEST/LIVE of 3000 requests per 60 seconds"`<br>`}]`|Refer to [API rate limits](#api-rate-limits) for more information|
|`429`|`[{`<br>`"error": "TooManyRequestsError",`<br>`"message": "Exceeded send limits (LIMIT NUMBER) for today"`<br>`}]`|Refer to [service limits](#service-limits) for the limit number|
|`500`|`[{`<br>`"error": "Exception",`<br>`"message": "Internal server error"`<br>`}]`|Notify was unable to process the request, resend your notification.|


## Send a precompiled letter

### Method

```php
$response = $notifyClient->sendPrecompiledLetter(
    $reference,
    $pdf_data,
    $postage,
);
```

### Arguments

#### reference (required)

A unique identifier you create if necessary. This reference identifies a single unique notification or a batch of notifications. It must not contain any personal information such as name or postal address.

```php
$reference = 'STRING';
```

#### pdf_data (required)

The precompiled letter must be a PDF file which meets [the GOV.UK Notify PDF letter specification](https://docs.notifications.service.gov.uk/documentation/images/notify-pdf-letter-spec-v2.4.pdf). The method sends the contents of the file to GOV.UK Notify.

```php
$pdf_data = file_get_contents("path/to/pdf_file");
try {

    $response = $notifyClient->sendPrecompiledLetter(
        'unique_ref123',
        $pdf_data,
        'first',
    );

} catch (NotifyException $e){}
```

#### postage (optional)

You can choose first or second class postage for your precompiled letter. Set the value to `first` for first class, or `second` for second class. If you do not pass in this argument, the postage will default to second class.

```php
$postage = 'first';
```


### Response

If the request to the client is successful, the client returns an `array`:

```php
[
  "id" => "740e5834-3a29-46b4-9a6f-16142fde533a",
  "reference" => "unique_ref123",
  "postage" => "first"
];
```

### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code.

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Cannot send letters with a team api key"`<br>`]}`|Use the correct type of [API key](#api-keys).|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Letter content is not a valid PDF"`<br>`]}`|PDF file format is required.|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Cannot send letters when service is in trial mode - see https://www.notifications.service.gov.uk/trial-mode"`<br>`}]`|Your service cannot send this notification in [trial mode](https://www.notifications.service.gov.uk/features/using-notify#trial-mode).|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "reference is a required property"`<br>`}]`|Add a `reference` argument to the method call.|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "postage invalid. It must be either first or second."`<br>`}]`|Change the value of `postage` argument in the method call to either 'first' or 'second'|
|`429`|`[{`<br>`"error": "RateLimitError",`<br>`"message": "Exceeded rate limit for key type live of 10 requests per 20 seconds"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`429`|`[{`<br>`"error": "TooManyRequestsError",`<br>`"message": "Exceeded send limits (LIMIT NUMBER) for today"`<br>`}]`|Refer to [service limits](#service-limits) for the limit number.|

# Get message status

Message status depends on the type of message that you have sent.

You can only get the status of messages that are 7 days old or newer.

## Status - text and email

|Status|Information|
|:---|:---|
|Created|GOV.UK Notify has placed the message in a queue, ready to be sent to the provider. It should only remain in this state for a few seconds.|
|Sending|GOV.UK Notify has sent the message to the provider. The provider will try to deliver the message to the recipient for up to 72 hours. GOV.UK Notify is waiting for delivery information.|
|Delivered|The message was successfully delivered.|
|Failed|This covers all failure statuses:<br>- `permanent-failure` - "The provider could not deliver the message because the email address or phone number was wrong. You should remove these email addresses or phone numbers from your database. You’ll still be charged for text messages to numbers that do not exist."<br>- `temporary-failure` - "The provider could not deliver the message. This can happen when the recipient’s inbox is full or their phone is off. You can try to send the message again. You’ll still be charged for text messages to phones that are not accepting messages."<br>- `technical-failure` - "Your message was not sent because there was a problem between Notify and the provider.<br>You’ll have to try sending your messages again. You will not be charged for text messages that are affected by a technical failure."|

## Status - text only

|Status|Information|
|:---|:---|
|Pending|GOV.UK Notify is waiting for more delivery information.<br>GOV.UK Notify received a callback from the provider but the recipient's device has not yet responded. Another callback from the provider determines the final status of the notification.|
|Sent / Sent internationally|The message was sent to an international number. The mobile networks in some countries do not provide any more delivery information. The GOV.UK Notify client API returns this status as `sent`. The GOV.UK Notify client app returns this status as `Sent internationally`.|

## Status - letter

|Status|information|
|:---|:---|
|Failed|The only failure status that applies to letters is `technical-failure`. GOV.UK Notify had an unexpected error while sending to our printing provider.|
|Accepted|GOV.UK Notify has sent the letter to the provider to be printed.|
|Received|The provider has printed and dispatched the letter.|

## Status - precompiled letter

|Status|information|
|:---|:---|
|Pending virus check|GOV.UK Notify has not completed a virus scan of the precompiled letter file.|
|Virus scan failed|GOV.UK Notify found a potential virus in the precompiled letter file.|
|Validation failed|Content in the precompiled letter file is outside the printable area. See the [GOV.UK Notify PDF letter specification](https://docs.notifications.service.gov.uk/documentation/images/notify-pdf-letter-spec-v2.3.pdf) for more information.|

## Get the status of one message

You can only get the status of messages that are 7 days old or newer.

### Method

```php
getNotification( $notificationId )
```

For example:

```php
try {

    $response = $notifyClient->getNotification( 'c32e9c89-a423-42d2-85b7-a21cd4486a2a' );

} catch (NotifyException $e){}
```

### Arguments

#### notificationId (required)

The ID of the notification. You can find the notification ID in the response to the [original notification method call](#get-the-status-of-one-message-response).

You can also find it by [signing in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and going to the __API integration__ page.

### Response

If the request to the client is successful, the client returns an `array`:

```php
[
    "id" => "notify_id",
    "body" => "Hello Foo",
    "subject" => "null|email_subject",
    "reference" => "client reference",
    "email_address" => "email address",
    "phone_number" => "phone number",
    "line_1" => "full name of a person or company",
    "line_2" => "123 The Street",
    "line_3" => "Some Area",
    "line_4" => "Some Town",
    "line_5" => "Some county",
    "line_6" => "Something else",
    "postcode" => "postcode",
    "postage" => "null|first|second",
    "type" => "sms|letter|email",
    "status" => "current status",
    "template" => [
        "version" => 1,
        "id" => 1,
        "uri" => "/template/{id}/{version}"
     ],
    "created_at" => "created at",
    "sent_at" => "sent to provider at",
];
```

### Error codes

If the request is not successful, the client will return an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code:

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "id is not a valid UUID"`<br>`}]`|Check the notification ID.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`404`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No result found"`<br>`}]`|Check the notification ID.|


## Get the status of multiple messages

This API call returns one page of up to 250 messages and statuses. You can get either the most recent messages, or get older messages by specifying a particular notification ID in the `older_than` argument.

You can only get the status of messages that are 7 days old or newer.

### Method

```php
listNotifications( array $filters = array() )
```

```php
    $response = $notifyClient->listNotifications([
        'older_than' => 'c32e9c89-a423-42d2-85b7-a21cd4486a2a',
        'reference' => 'weekly-reminders',
        'status' => 'delivered',
        'template_type' => 'sms'
    ]);
```

You can leave out the `older_than` argument to get the 250 most recent messages.

To get older messages, pass the ID of an older notification into the `older_than` argument. This returns the next 250 oldest messages from the specified notification ID.

### Arguments

You can leave out any of these arguments to ignore these filters.

#### template_type (optional)

You can filter by:

* `email`
* `sms`
* `letter`

You can leave out this argument to ignore this filter.

#### status (optional)

| status | description | text | email | letter |Precompiled letter|
|:--- |:--- |:--- |:--- |:--- |:--- |
|created|GOV.UK Notify has placed the message in a queue, ready to be sent to the provider. It should only remain in this state for a few seconds.|Yes|Yes|||
|sending|GOV.UK Notify has sent the message to the provider. The provider will try to deliver the message to the recipient for up to 72 hours. GOV.UK Notify is waiting for delivery information.|Yes|Yes|||
|delivered|The message was successfully delivered|Yes|Yes|||
|sent / sent internationally|The message was sent to an international number. The mobile networks in some countries do not provide any more delivery information.|Yes||||
|pending|GOV.UK Notify is waiting for more delivery information.<br>GOV.UK Notify received a callback from the provider but the recipient's device has not yet responded. Another callback from the provider determines the final status of the notification.|Yes||||
|failed|This returns all failure statuses:<br>- permanent-failure<br>- temporary-failure<br>- technical-failure|Yes|Yes|||
|permanent-failure|The provider could not deliver the message because the email address or phone number was wrong. You should remove these email addresses or phone numbers from your database. You’ll still be charged for text messages to numbers that do not exist.|Yes|Yes|||
|temporary-failure|The provider could not deliver the message. This can happen when the recipient’s inbox is full or their phone is off. You can try to send the message again. You’ll still be charged for text messages to phones that are not accepting messages.|Yes|Yes|||
|technical-failure|Email / Text: Your message was not sent because there was a problem between Notify and the provider.<br>You’ll have to try sending your messages again. You will not be charged for text messages that are affected by a technical failure. <br><br>Letter: Notify had an unexpected error while sending to our printing provider. <br><br>You can leave out this argument to ignore this filter.|Yes|Yes|||
|accepted|GOV.UK Notify has sent the letter to the provider to be printed.|||Yes||
|received|The provider has printed and dispatched the letter.|||Yes||
|pending-virus-check|GOV.UK Notify is scanning the precompiled letter file for viruses.||||Yes|
|virus-scan-failed|GOV.UK Notify found a potential virus in the precompiled letter file.||||Yes|
|validation-failed|Content in the precompiled letter file is outside the printable area.||||Yes|

#### reference (optional)

A unique identifier you can create if necessary. This reference identifies a single unique notification or a batch of notifications. It must not contain any personal information such as name or postal address.

```php
$reference = 'STRING';
```

You can leave out this argument to ignore this filter.

#### older_than (optional)

Input the ID of a notification into this argument. If you use this argument, the method returns the next 250 received notifications older than the specified ID.

```php
older_than='740e5834-3a29-46b4-9a6f-16142fde533a'
```

If you leave out this argument, the method returns the most recent 250 notifications.

The client only returns notifications that are 7 days old or newer. If the notification specified in this argument is older than 7 days, the client returns an empty response.

### Response

If the request to the client is successful, the client returns an `array`.

```php
[
    "notifications" => [
            "id" => "notify_id",
            "reference" => "client reference",
            "email_address" => "email address",
            "phone_number" => "phone number",
            "line_1" => "full name of a person or company",
            "line_2" => "123 The Street",
            "line_3" => "Some Area",
            "line_4" => "Some Town",
            "line_5" => "Some county",
            "line_6" => "Something else",
            "postcode" => "postcode",
            "postage" => "null|first|second",
            "type" => "sms | letter | email",
            "status" => sending | delivered | permanent-failure | temporary-failure | technical-failure
            "template" => [
            "version" => 1,
            "id" => 1,
            "uri" => "/template/{id}/{version}"
        ],
        "created_at" => "created at",
        "sent_at" => "sent to provider at",
        ],
        …
  ],
  "links" => [
     "current" => "/notifications?template_type=sms&status=delivered",
     "next" => "/notifications?older_than=last_id_in_list&template_type=sms&status=delivered"
  ]
];
```

### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code:

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "bad status is not one of [created, sending, delivered, pending, failed, technical-failure, temporary-failure, permanent-failure]"`<br>`}]`|Contact the GOV.UK Notify team.|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "Apple is not one of [sms, email, letter]"`<br>`}]`|Contact the GOV>UK Notify team.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|



## Get a PDF for a letter notification

### Method

This returns the PDF contents of a letter notification.

```php
$pdf_file = $notifyClient->getPdfForLetter(
  'f33517ff-2a88-4f6e-b855-c550268ce08a' // required string - notification ID
)
```

### Arguments

#### notificationId (required)

The ID of the notification. You can find the notification ID in the response to the [original notification method call](#get-the-status-of-one-message-response).

You can also find it by [signing in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and going to the __API integration__ page.

### Response

If the request to the client is successful, the client will return a `string` containing the raw PDF data.

### Error codes

If the request is not successful, the client throws an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code:

|error.status_code|error.message|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "id is not a valid UUID"`<br>`}]`|Check the notification ID|
|`400`|`[{`<br>`"error": "PDFNotReadyError",`<br>`"message": "PDF not available yet, try again later"`<br>`}]`|Wait for the notification to finish processing. This usually takes a few seconds|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "File did not pass the virus scan"`<br>`}]`|You cannot retrieve the contents of a letter notification that contains a virus|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "PDF not available for letters in technical-failure"`<br>`}]`|You cannot retrieve the contents of a letter notification in technical-failure|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "Notification is not a letter"`<br>`}]`|Check that you are looking up the correct notification|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information|
|`404`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No result found"`<br>`}]`|Check the notification ID|


# Get a template

## Get a template by ID

### Method

This returns the latest version of the template.

```php
$response = $notifyClient->getTemplate( 'templateId' );
```

### Arguments

#### templateId (required)

The ID of the template. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and go to the __Templates__ page to find it.

### Response

If the request to the client is successful, the client returns an `array`.

```php
[
    "id" => "template_id",
    "type" => "sms|email|letter",
    "created_at" => "created at",
    "updated_at" => "updated at",
    "version" => "version",
    "created_by" => "someone@example.com",
    "body" => "body",
    "subject" => "null|email_subject"
];
```

### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code:

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`404`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No Result Found"`<br>`}]`|Check your [template ID](#get-a-template-by-id-arguments-template-id-required).|


## Get a template by ID and version

### Method

```php
$response = $notifyClient->getTemplateVersion( 'templateId', 1 );
```

### Arguments

#### templateId (required)

The ID of the template. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and go to the __Templates__ page to find it.

#### version (required)

The version number of the template.

### Response

If the request to the client is successful, the client returns an `array`.

```php
[
    "id" => "template_id",
    "type" => "sms|email|letter",
    "created_at" => "created at",
    "updated_at" => "updated at",
    "version" => "version",
    "created_by" => "someone@example.com",
    "body" => "body",
    "subject" => "null|email_subject"
];
```

### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code:

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`404`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No Result Found"`<br>`}]`|Check your [template ID](#get-a-template-by-id-and-version-arguments-template-id-required) and [version](#version-required).|


## Get all templates

### Method

This returns the latest version of all templates.

```php
    $this->getAllTemplates(
      $template_type  // optional
    );
```

### Arguments

#### template_type (optional)

If omitted, the method returns all templates. Otherwise you can filter by:

- `email`
- `sms`
- `letter`

### Response

If the request to the client is successful, the client returns an `array`.

```php
[
    "templates"  => [
        [
            "id" => "template_id",
            "type" => "sms|email|letter",
            "created_at" => "created at",
            "updated_at" => "updated at",
            "version" => "version",
            "created_by" => "someone@example.com",
            "body" => "body",
            "subject" => "null|email_subject"
        ],
        [
            ... another template
        ]
    ]
];
```

If no templates exist for a template type or there no templates for a service, the client returns a `dict` with an empty `templates` list element:

```php
[
    "templates"  => []
];
```

## Generate a preview template

### Method

This generates a preview version of a template.

```php
    $personalisation = [ "foo" => "bar" ];
    $this->previewTemplate( $templateId, $personalisation );
```

The parameters in the personalisation argument must match the placeholder fields in the actual template. The API notification client will ignore any extra fields in the method.

### Arguments

#### templateId (required)

The ID of the template. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and go to the __Templates__ page to find it.

#### personalisation (required)

If a template has placeholder fields for personalised information such as name or reference number, you need to provide their values in a dictionary with key value pairs. For example:

```php
$personalisation = [
    'first_name' => 'Amala',
    'reference_number' => '300241',
];
```

### Response

If the request to the client is successful, the client returns an `array`.

```php
[
    "id" => "notify_id",
    "type" => "sms|email|letter",
    "version" => "version",
    "body" => "Hello bar" // with substitution values,
    "subject" => "null|email_subject"
];
```

### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code:

|exc->getCode()|exc->getErrors()|Notes|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Missing personalisation: [PERSONALISATION FIELD]"`<br>`}]`|Check that the personalisation arguments in the method match the placeholder fields in the template.|
|`400`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No result found"`<br>`}]`|Check the [template ID](#generate-a-preview-template-arguments-template-id-required).|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|


# Get received text messages

This API call returns one page of up to 250 received text messages. You can get either the most recent messages, or get older messages by specifying a particular notification ID in the `older_than` argument.

You can only get the status of messages that are 7 days old or newer.

You can also set up [callbacks](#callbacks) for received text messages.

## Enable received text messages

Contact the GOV.UK Notify team using the [support page](https://www.notifications.service.gov.uk/support) or [chat to us on Slack](https://ukgovernmentdigital.slack.com/messages/C0E1ADVPC) to request a unique number for text message replies.

## Get a page of received text messages

### Method

```php
    $this->listReceivedTexts(
      $older_than  // optional
    );
```

To get older messages, pass the ID of an older notification into the `older_than` argument. This returns the next 250 oldest messages from the specified notification ID.

If you leave out the `older_than` argument, the client returns the most recent 250 notifications.

### Arguments

#### older_than (optional)

Input the ID of a received text message into this argument. If you use this argument, the client returns the next 250 received text messages older than the given ID. For example:

```php
$older_than = '8e222534-7f05-4972-86e3-17c5d9f894e2'
```

If you leave out the `older_than` argument, the client returns the most recent 250 notifications.

The client only returns notifications that are 7 days old or newer. If the notification specified in this argument is older than 7 days, the client returns an empty collection response.

### Response

If the request to the client is successful, the client returns an `array`.

```php
[
    "received_text_messages" => [
        [
            "id" => "notify_id",
            "user_number" => "user number",
            "notify_number" => "notify number",
            "created_at" => "created at",
            "service_id" => "service id",
            "content" => "text content"
        ],
        [
            ... another received text message
        ]
    ]
  ],
  "links" => [
     "current" => "/received-text-messages",
     "next" => "/received-text-messages?older_than=last_id_in_list"
  ]
];
```
### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\NotifyException` object containing the relevant error code:

|exc->getCode()|exc->getErrors()|Notes|
|:---|:---|:---|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: signature, api token not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
