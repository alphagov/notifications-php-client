# PHP client documentation

This documentation is for developers interested in using the GOV.UK Notify PHP client to send emails, text messages or letters.

## Set up the client

The Notify PHP Client is based on a [PSR-7 HTTP model](https://www.php-fig.org/psr/psr-7/) [external link]. To install it, you must select your preferred PSR-7 compatible HTTP client. You can follow these instructions to use [Guzzle v7](https://docs.guzzlephp.org/en/7.0/)[external link].

### Guzzle v7

1. Use [Composer](https://getcomposer.org/)  [external link] to install the GOV.UK Notify PHP client. Run the following in the command line:

    ```sh
    composer require php-http/guzzle7-adapter alphagov/notifications-php-client
    ```

    You can now use the [autoloader](https://getcomposer.org/doc/01-basic-usage.md#autoloading) [external link] to download the GOV.UK Notify PHP client.

1. Add the following code to your application to create a new instance of the client:

    ```
    $notifyClient = new \Alphagov\Notifications\Client([
        'apiKey' => '{your api key}',
        'httpClient' => new \Http\Adapter\Guzzle7\Client
    ]);
    ```

To get an API key, [sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and go to the __API integration__ page. You can find more information in the [API keys](#api-keys) section of this documentation.

## Send a message

You can use GOV.UK Notify to send text messages, emails and letters.

### Send a text message

#### Method

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

} catch (ApiException $e){}
```

#### Arguments

##### phoneNumber (required)

The phone number of the recipient of the text message. This can be a UK or international number.

##### templateId (required)

To find the template ID:

1. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in).
1. Go to the __Templates__ page and select the relevant template.
1. Select __Copy template ID to clipboard__.

##### personalisation (optional)

If a template has placeholder fields for personalised information such as name or application date, you must provide their values in a dictionary with key value pairs. For example:

```php
$personalisation = [
    'name' => 'Amala',
    'application_date'  => '2018-01-01'
];
```

You can leave out this argument if a template does not have any placeholder fields for personalised information.

##### reference (optional)

A unique identifier you can create if necessary. This reference identifies a single unique notification or a batch of notifications. It must not contain any personal information such as name or postal address. For example:

```php
$reference = 'STRING';
```
You can leave out this argument if you do not have a reference.

##### smsSenderId (optional)

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

#### Response

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

All messages sent using the [team and guest list](#team-and-guest-list) or [live](#live) keys will appear on your dashboard.

#### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\ApiException` object containing the relevant error code.

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can't send to this recipient using a team-only API key"`<br>`}]`|Use the correct type of [API key](#api-keys).|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can't send to this recipient when service is in trial mode - see https://www.notifications.service.gov.uk/trial-mode"`<br>`}]`|Your service cannot send this notification in [trial mode](https://www.notifications.service.gov.uk/features/using-notify#trial-mode).|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: API key not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`429`|`[{`<br>`"error": "RateLimitError",`<br>`"message": "Exceeded rate limit for key type TEAM/TEST/LIVE of 3000 requests per 60 seconds"`<br>`}]`|Refer to [API rate limits](#rate-limits) for more information.|
|`429`|`[{`<br>`"error": "TooManyRequestsError",`<br>`"message": "Exceeded send limits (LIMIT NUMBER) for today"`<br>`}]`|Refer to [service limits](#daily-limits) for the limit number.|
|`500`|`[{`<br>`"error": "Exception",`<br>`"message": "Internal server error"`<br>`}]`|Notify was unable to process the request, resend your notification.|

### Send an email

#### Method

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

} catch (ApiException $e){}
```


#### Arguments

##### emailAddress (required)

The email address of the recipient.

##### templateId (required)

To find the template ID:

1. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in).
1. Go to the __Templates__ page and select the relevant template.
1. Select __Copy template ID to clipboard__.

##### personalisation (optional)

If a template has placeholder fields for personalised information such as name or reference number, you need to provide their values in a dictionary with key value pairs. For example:

```php
$personalisation = [
    'name' => 'Amala',
    'application_date'  => '2018-01-01'
];
```

You can personalise the bullet points in your template. For example:

```php
Introduce bullet points with a lead-in line ending in a colon:
((mybullets))
Leave one empty line space before the next paragraph.

```

Make an API call pass in a list of strings for the value of the  mybullets placeholder instead of just a single string. This will automatically format the list as bullet points:

```php
"personalisation": {
  "mybullets": ["item 1", "item 2", "item 3"],
}

```

You can leave out this argument if a template does not have any placeholder fields for personalised information.

##### reference (optional)

A unique identifier you can create if necessary. This reference identifies a single unique notification or a batch of notifications. It must not contain any personal information such as name or postal address.

```php
$reference = 'STRING';
```

You can leave out this argument if you do not have a reference.

##### emailReplyToId (optional)

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

### Send a file by email

To send a file by email, add a placeholder to the template then upload a file. The placeholder will contain a secure link to download the file.

The links are unique and unguessable. GOV.UK Notify cannot access or decrypt your file.

Your file will be available to download for a default period of 26 weeks (6 months).

To help protect your files you can also:

* ask recipients to confirm their email address before downloading
* choose the length of time that a file is available to download

#### Add contact details to the file download page

1. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in).
1. Go to the __Settings__ page.
1. In the __Email__ section, select __Manage__ on the __Send files by email__ row.
1. Enter the contact details you want to use, and select __Save__.

#### Add a placeholder to the template

1. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in).
1. Go to the __Templates__ page and select the relevant email template.
1. Select __Edit__.
1. Add a placeholder to the email template using double brackets. For example: "Download your file at: ((link_to_file))"

Your email should also tell recipients how long the file will be available to download.

#### Upload your file

You can upload PDF, CSV, JSON, .odt, .txt, .rtf, .xlsx and MS Word Document files. Your file must be smaller than 2MB.. [Contact the GOV.UK Notify team](https://www.notifications.service.gov.uk/support/ask-question-give-feedback) if you need to send other file types.

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
}
catch (ApiException $e){}
catch (InvalidArgumentException $e){}
```

##### CSV Files

Uploads for CSV files should use the `is_csv` parameter on the `prepareUpload()` helper method.  For example:

```php
try {
    $file_data = file_get_contents('/path/to/my/file.csv');

    $response = $notifyClient->sendEmail(
        'betty@example.com',
        'df10a23e-2c0d-4ea5-87fb-82e520cbf93c',
        [
            'name' => 'Betty Smith',
            'dob'  => '12 July 1968',
            'link_to_file' => $notifyClient->prepareUpload( $file_data, true )
        ]
    );
}
catch (ApiException $e){}
catch (InvalidArgumentException $e){}
```

#### Ask recipients to confirm their email address before they can download the file

When a recipient clicks the link in the email you’ve sent them, they have to enter their email address. Only someone who knows the recipient’s email address can download the file.

This security feature is turned on by default.

##### Turn off email address check (not recommended)

If you do not want to use this feature, you can turn it off on a file-by-file basis.

To do this you will need version 4.2.0 of the PHP client library, or a more recent version.

You should not turn this feature off if you send files that contain:

* personally identifiable information
* commercially sensitive information
* information classified as ‘OFFICIAL’ or ‘OFFICIAL-SENSITIVE’ under the [Government Security Classifications](https://www.gov.uk/government/publications/government-security-classifications) policy

To let the recipient download the file without confirming their email address, set the `confirm_email_before_download` flag to `false`.

```php
try {
    $file_data = file_get_contents('/path/to/my/file.pdf');

    $response = $notifyClient->sendEmail(
        'betty@example.com',
        'df10a23e-2c0d-4ea5-87fb-82e520cbf93c',
        [
            'name' => 'Betty Smith',
            'dob'  => '12 July 1968',
            'link_to_file' => $notifyClient->prepareUpload( $file_data, false, false )
        ]
    );
}
catch (ApiException $e){}
catch (InvalidArgumentException $e){}
```

#### Choose the length of time that a file is available to download

Set the number of weeks you want the file to be available using the `retentionPeriod` key.

To use this feature will need version 4.2.0 of the PHP client library, or a more recent version.

You can choose any value between 1 week and 78 weeks. When deciding this, you should consider:

* the need to protect the recipient’s personal information
* whether the recipient will need to download the file again later

If you do not choose a value, the file will be available for the default period of 26 weeks (6 months).

Files sent before 12 April 2023 had a longer default period of 78 weeks (18 months).

```php
try {
    $file_data = file_get_contents('/path/to/my/file.pdf');

    $response = $notifyClient->sendEmail(
        'betty@example.com',
        'df10a23e-2c0d-4ea5-87fb-82e520cbf93c',
        [
            'name' => 'Betty Smith',
            'dob'  => '12 July 1968',
            'link_to_file' => $notifyClient->prepareUpload( $file_data, false, null, "52 weeks" )
        ]
    );
}
catch (ApiException $e){}
catch (InvalidArgumentException $e){}
```

#### Response

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


#### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\ApiException` object containing the relevant error code.

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can't send to this recipient using a team-only API key"`<br>`}]`|Use the correct type of [API key](#api-keys).|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can't send to this recipient when service is in trial mode - see https://www.notifications.service.gov.uk/trial-mode"`<br>`}]`|Your service cannot send this notification in [trial mode](https://www.notifications.service.gov.uk/features/using-notify#trial-mode).|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Unsupported file type '(FILE TYPE)'. Supported types are: '(ALLOWED TYPES)'"`<br>`}]`|Wrong file type. You can only upload .pdf, .csv, .txt, .doc, .docx, .xlsx, .rtf or .odt files.|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Unsupported value for retention_period '(PERIOD)'. Supported periods are from 1 to 78 weeks."`<br>`}]`|Choose a period between 1 and 78 weeks|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Unsupported value for confirm_email_before_download: '(VALUE)'. Use a boolean true or false value."`<br>`}]`|Use either true or false|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "File did not pass the virus scan"`<br>`}]`|The file contains a virus.|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Send files by email has not been set up - add contact details for your service at https://www.notifications.service.gov.uk/services/(SERVICE ID)/service-settings/send-files-by-email"`<br>`}]`|See how to [add contact details to the file download page](#add-contact-details-to-the-file-download-page)|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Can only send a file by email"`<br>`}]`|Make sure you are using an email template|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: API key not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`429`|`[{`<br>`"error": "RateLimitError",`<br>`"message": "Exceeded rate limit for key type TEAM/TEST/LIVE of 3000 requests per 60 seconds"`<br>`}]`|Refer to [API rate limits](#rate-limits) for more information.|
|`429`|`[{`<br>`"error": "TooManyRequestsError",`<br>`"message": "Exceeded send limits (LIMIT NUMBER) for today"`<br>`}]`|Refer to [service limits](#daily-limits) for the limit number.|
|`500`|`[{`<br>`"error": "Exception",`<br>`"message": "Internal server error"`<br>`}]`|Notify was unable to process the request, resend your notification.|
|`N/A`|`Exception\InvalidArgumentException( 'File is larger than 2MB.' )`|The file is too big. Files must be smaller than 2MB.|

### Send a letter

#### Prerequisites

When you add a new service it will start in [trial mode](https://www.notifications.service.gov.uk/features/trial-mode). You can only send letters when your service is live.

To send Notify a request to go live:

1. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in).
1. Go to the __Settings__ page.
1. In the __Your service is in trial mode__ section, select __request to go live__.

#### Method

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
            'address_line_3' => 'SW1 1AA'
        ],
        'unique_ref123'
    );

} catch (ApiException $e){}
```

#### Arguments

##### templateId (required)

To find the template ID:

1. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in).
1. Go to the __Templates__ page and select the relevant template.
1. Select __Copy template ID to clipboard__.

##### personalisation (required)

The personalisation argument always contains the following parameters for the letter recipient’s address:

- `address_line_1`
- `address_line_2`
- `address_line_3`
- `address_line_4`
- `address_line_5`
- `address_line_6`
- `address_line_7`

The address must have at least 3 lines.

The last line needs to be a real UK postcode or the name of a country outside the UK.

Notify checks for international addresses and will automatically charge you the correct postage.

The `postcode` personalisation argument has been replaced. If your template still uses `postcode`, Notify will treat it as the last line of the address.

Any other placeholder fields included in the letter template also count as required parameters. You need to provide their values in a dictionary with key value pairs. For example:

```php
$personalisation =
          [
            'address_line_1' => 'The Occupier',
            'address_line_2' => '123 High Street',
            'address_line_3' => 'Richmond upon Thames',
            'address_line_4' => 'Middlesex',
            'address_line_5' => 'SW14 6BF',
            'name' => 'John Smith',
            'application_id' => '4134325'
          ];
```

You can personalise the bullet points in your template. For example:

```php
Introduce bullet points with a lead-in line ending in a colon:
((mybullets))
Leave one empty line space before the next paragraph.

```

Make an API call pass in a list of strings for the value of the  mybullets placeholder instead of just a single string. This will automatically format the list as bullet points:

```php
"personalisation": {
  "mybullets": ["item 1", "item 2", "item 3"],
}

```

##### reference (optional)

A unique identifier you can create if necessary. This reference identifies a single unique notification or a batch of notifications. It must not contain any personal information such as name or postal address. For example:

```php
$reference = 'STRING';
```

#### Response

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

#### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\ApiException` object containing the relevant error code.

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Cannot send letters with a team api key"`<br>`}]`|Use the correct type of [API key](#api-keys).|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Cannot send letters when service is in trial mode - see https://www.notifications.service.gov.uk/trial-mode"`<br>`}]`|Your service cannot send this notification in  [trial mode](https://www.notifications.service.gov.uk/features/using-notify#trial-mode).|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "personalisation address_line_1 is a required property"`<br>`}]`|Ensure that your template has a field for the first line of the address, check [personalisation](#personalisation-required) for more information.|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "Must be a real UK postcode"`<br>`}]`|Ensure that the value for the last line of the address is a real UK postcode.|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "Last line of address must be a real UK postcode or another country"`<br>`}]`|Ensure that the value for the last line of the address is a real UK postcode or the name of a country outside the UK.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: API key not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`429`|`[{`<br>`"error": "RateLimitError",`<br>`"message": "Exceeded rate limit for key type TEAM/TEST/LIVE of 3000 requests per 60 seconds"`<br>`}]`|Refer to [API rate limits](#rate-limits) for more information.|
|`429`|`[{`<br>`"error": "TooManyRequestsError",`<br>`"message": "Exceeded send limits (LIMIT NUMBER) for today"`<br>`}]`|Refer to [service limits](#daily-limits) for the limit number.|
|`500`|`[{`<br>`"error": "Exception",`<br>`"message": "Internal server error"`<br>`}]`|Notify was unable to process the request, resend your notification.|


### Send a precompiled letter

#### Method

```php
$response = $notifyClient->sendPrecompiledLetter(
    $reference,
    $pdf_data,
    $postage,
);
```

#### Arguments

##### reference (required)

A unique identifier you create if necessary. This reference identifies a single unique notification or a batch of notifications. It must not contain any personal information such as name or postal address.

```php
$reference = 'STRING';
```

##### pdf_data (required)

The precompiled letter must be a PDF file which meets [the GOV.UK Notify letter specification](https://www.notifications.service.gov.uk/using-notify/guidance/letter-specification). The method sends the contents of the file to GOV.UK Notify.

```php
$pdf_data = file_get_contents("path/to/pdf_file");
try {

    $response = $notifyClient->sendPrecompiledLetter(
        'unique_ref123',
        $pdf_data,
        'first',
    );

} catch (ApiException $e){}
```

##### postage (optional)

You can choose first or second class postage for your precompiled letter. Set the value to `first` for first class, or `second` for second class. If you do not pass in this argument, the postage will default to second class.

```php
$postage = 'first';
```


#### Response

If the request to the client is successful, the client returns an `array`:

```php
[
  "id" => "740e5834-3a29-46b4-9a6f-16142fde533a",
  "reference" => "unique_ref123",
  "postage" => "first"
];
```

#### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\ApiException` object containing the relevant error code.

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Cannot send letters with a team api key"`<br>`}]`|Use the correct type of [API key](#api-keys).|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Letter content is not a valid PDF"`<br>`}]`|PDF file format is required.|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Cannot send letters when service is in trial mode - see https://www.notifications.service.gov.uk/trial-mode"`<br>`}]`|Your service cannot send this notification in [trial mode](https://www.notifications.service.gov.uk/features/using-notify#trial-mode).|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "reference is a required property"`<br>`}]`|Add a `reference` argument to the method call.|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "postage invalid. It must be either first or second."`<br>`}]`|Change the value of `postage` argument in the method call to either 'first' or 'second'|
|`429`|`[{`<br>`"error": "RateLimitError",`<br>`"message": "Exceeded rate limit for key type live of 10 requests per 20 seconds"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`429`|`[{`<br>`"error": "TooManyRequestsError",`<br>`"message": "Exceeded send limits (LIMIT NUMBER) for today"`<br>`}]`|Refer to [service limits](#daily-limits) for the limit number.|

## Get message status


### Get the status of one message

You can only get the status of messages sent within the retention period. The default retention period is 7 days.

#### Method

```php
getNotification( $notificationId )
```

For example:

```php
try {

    $response = $notifyClient->getNotification( 'c32e9c89-a423-42d2-85b7-a21cd4486a2a' );

} catch (ApiException $e){}
```

#### Arguments

##### notificationId (required)

The ID of the notification. To find the notification ID, you can either:

* check the response to the [original notification method call](#response)
* [sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and go to the __API integration__ page

#### Response

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
    "line_7" => "Postcode or country",
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

#### Error codes

If the request is not successful, the client will return an `Alphagov\Notifications\Exception\ApiException` object containing the relevant error code:

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "id is not a valid UUID"`<br>`}]`|Check the notification ID.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: API key not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`404`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No result found"`<br>`}]`|Check the notification ID.|


### Get the status of multiple messages

This API call returns one page of up to 250 messages and statuses. You can get either the most recent messages, or get older messages by specifying a particular notification ID in the `older_than` argument.

You can only get the status of messages that are 7 days old or newer.

#### Method

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

#### Arguments

You can leave out any of these arguments to ignore these filters.

##### template_type (optional)

You can filter by:

* `email`
* `sms`
* `letter`

You can leave out this argument to ignore this filter.

##### status (optional)

You can filter by each:

* [email status](#email-status-descriptions)
* [text message status](#text-message-status-descriptions)
* [letter status](#letter-status-descriptions)
* [precompiled letter status](#precompiled-letter-status-descriptions)

You can leave out this argument to ignore this filter.

##### reference (optional)

A unique identifier you can create if necessary. This reference identifies a single unique notification or a batch of notifications. It must not contain any personal information such as name or postal address.

```php
$reference = 'STRING';
```

You can leave out this argument to ignore this filter.

##### older_than (optional)

Input the ID of a notification into this argument. If you use this argument, the method returns the next 250 received notifications older than the specified ID.

```php
older_than='740e5834-3a29-46b4-9a6f-16142fde533a'
```

If you leave out this argument, the method returns the most recent 250 notifications.

The client only returns notifications that are 7 days old or newer. If the notification specified in this argument is older than 7 days, the client returns an empty response.

#### Response

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

#### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\ApiException` object containing the relevant error code:

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "bad status is not one of [created, sending, delivered, pending, failed, technical-failure, temporary-failure, permanent-failure]"`<br>`}]`|Contact the GOV.UK Notify team.|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "Apple is not one of [sms, email, letter]"`<br>`}]`|Contact the GOV>UK Notify team.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: API key not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|


### Email status descriptions

|Status|Description|
|:---|:---|
|#`created`|GOV.UK Notify has placed the message in a queue, ready to be sent to the provider. It should only remain in this state for a few seconds.|
|#`sending`|GOV.UK Notify has sent the message to the provider. The provider will try to deliver the message to the recipient for up to 72 hours. GOV.UK Notify is waiting for delivery information.|
|#`delivered`|The message was successfully delivered.|
|#`permanent-failure`|The provider could not deliver the message because the email address was wrong. You should remove these email addresses from your database.|
|#`temporary-failure`|The provider could not deliver the message. This can happen when the recipient’s inbox is full or their anti-spam filter rejects your email. [Check your content does not look like spam](https://www.gov.uk/service-manual/design/sending-emails-and-text-messages#protect-your-users-from-spam-and-phishing) before you try to send the message again.|
|#`technical-failure`|Your message was not sent because there was a problem between Notify and the provider.<br>You’ll have to try sending your messages again.|

### Text message status descriptions

|Status|Description|
|:---|:---|
|#`created`|GOV.UK Notify has placed the message in a queue, ready to be sent to the provider. It should only remain in this state for a few seconds.|
|#`sending`|GOV.UK Notify has sent the message to the provider. The provider will try to deliver the message to the recipient for up to 72 hours. GOV.UK Notify is waiting for delivery information.|
|#`pending`|GOV.UK Notify is waiting for more delivery information.<br>GOV.UK Notify received a callback from the provider but the recipient’s device has not yet responded. Another callback from the provider determines the final status of the text message.|
|#`sent`|The message was sent to an international number. The mobile networks in some countries do not provide any more delivery information. The GOV.UK Notify website displays this status as 'Sent to an international number'.|
|#`delivered`|The message was successfully delivered. If a recipient blocks your sender name or mobile number, your message will still show as delivered.|
|#`permanent-failure`|The provider could not deliver the message. This can happen if the phone number was wrong or if the network operator rejects the message. If you’re sure that these phone numbers are correct, you should [contact GOV.UK Notify support](https://www.notifications.service.gov.uk/support). If not, you should remove them from your database. You’ll still be charged for text messages that cannot be delivered.
|#`temporary-failure`|The provider could not deliver the message. This can happen when the recipient’s phone is off, has no signal, or their text message inbox is full. You can try to send the message again. You’ll still be charged for text messages to phones that are not accepting messages.|
|#`technical-failure`|Your message was not sent because there was a problem between Notify and the provider.<br>You’ll have to try sending your messages again. You will not be charged for text messages that are affected by a technical failure.|

### Letter status descriptions

|Status|Description|
|:---|:---|
|#`accepted`|GOV.UK Notify has sent the letter to the provider to be printed.|
|#`received`|The provider has printed and dispatched the letter.|
|#`cancelled`|Sending cancelled. The letter will not be printed or dispatched.|
|#`technical-failure`|GOV.UK Notify had an unexpected error while sending the letter to our printing provider.|
|#`permanent-failure`|The provider cannot print the letter. Your letter will not be dispatched.|

### Precompiled letter status descriptions

|Status|Description|
|:---|:---|
|#`accepted`|GOV.UK Notify has sent the letter to the provider to be printed.|
|#`received`|The provider has printed and dispatched the letter.|
|#`cancelled`|Sending cancelled. The letter will not be printed or dispatched.|
|#`pending-virus-check`|GOV.UK Notify has not completed a virus scan of the precompiled letter file.|
|#`virus-scan-failed`|GOV.UK Notify found a potential virus in the precompiled letter file.|
|#`validation-failed`|Content in the precompiled letter file is outside the printable area. See the [GOV.UK Notify letter specification](https://www.notifications.service.gov.uk/using-notify/guidance/letter-specification) for more information.|
|#`technical-failure`|GOV.UK Notify had an unexpected error while sending the letter to our printing provider.|
|#`permanent-failure`|The provider cannot print the letter. Your letter will not be dispatched.|

### Get a PDF for a letter notification

#### Method

This returns the PDF contents of a letter notification.

```php
$pdf_file = $notifyClient->getPdfForLetter(
  'f33517ff-2a88-4f6e-b855-c550268ce08a' // required string - notification ID
)
```

#### Arguments

##### notificationId (required)

The ID of the notification. To find the notification ID, you can either:

* check the response to the [original notification method call](#get-the-status-of-one-message-response)
* [sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and go to the __API integration__ page

#### Response

If the request to the client is successful, the client will return a `string` containing the raw PDF data.

#### Error codes

If the request is not successful, the client throws an `Alphagov\Notifications\Exception\ApiException` object containing the relevant error code:

|error.status_code|error.message|How to fix|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "id is not a valid UUID"`<br>`}]`|Check the notification ID|
|`400`|`[{`<br>`"error": "PDFNotReadyError",`<br>`"message": "PDF not available yet, try again later"`<br>`}]`|Wait for the notification to finish processing. This usually takes a few seconds|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "File did not pass the virus scan"`<br>`}]`|You cannot retrieve the contents of a letter notification that contains a virus|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "PDF not available for letters in technical-failure"`<br>`}]`|You cannot retrieve the contents of a letter notification in technical-failure|
|`400`|`[{`<br>`"error": "ValidationError",`<br>`"message": "Notification is not a letter"`<br>`}]`|Check that you are looking up the correct notification|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: API key not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information|
|`404`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No result found"`<br>`}]`|Check the notification ID|


## Get a template

### Get a template by ID

#### Method

This returns the latest version of the template.

```php
$response = $notifyClient->getTemplate( 'templateId' );
```

#### Arguments

##### templateId (required)

The ID of the template. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and go to the __Templates__ page to find it.

#### Response

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
    "subject" => "null|email or letter subject",
    "letter_contact_block" => "null|letter_contact_block"
];
```

#### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\ApiException` object containing the relevant error code:

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: API key not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`404`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No Result Found"`<br>`}]`|Check your [template ID](#get-a-template-by-id-arguments-templateid-required).|


### Get a template by ID and version

#### Method

```php
$response = $notifyClient->getTemplateVersion( 'templateId', 1 );
```

#### Arguments

##### templateId (required)

The ID of the template. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and go to the __Templates__ page to find it.

##### version (required)

The version number of the template.

#### Response

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
    "subject" => "null|email or letter subject",
    "letter_contact_block" => "null|letter_contact_block"
];
```

#### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\ApiException` object containing the relevant error code:

|exc->getCode()|exc->getErrors()|How to fix|
|:---|:---|:---|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: API key not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
|`404`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No Result Found"`<br>`}]`|Check your [template ID](#get-a-template-by-id-and-version-arguments-templateid-required) and [version](#version-required).|


### Get all templates

#### Method

This returns the latest version of all templates.

```php
    $this->listTemplates(
      $template_type  // optional
    );
```

#### Arguments

##### template_type (optional)

If omitted, the method returns all templates. Otherwise you can filter by:

- `email`
- `sms`
- `letter`

#### Response

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
            "subject" => "null|email or letter subject",
            "letter_contact_block" => "null|letter_contact_block"
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

### Generate a preview template

#### Method

This generates a preview version of a template.

```php
    $personalisation = [ "foo" => "bar" ];
    $this->previewTemplate( $templateId, $personalisation );
```

The parameters in the personalisation argument must match the placeholder fields in the actual template. The API notification client will ignore any extra fields in the method.

#### Arguments

##### templateId (required)

The ID of the template. [Sign in to GOV.UK Notify](https://www.notifications.service.gov.uk/sign-in) and go to the __Templates__ page to find it.

##### personalisation (required)

If a template has placeholder fields for personalised information such as name or reference number, you need to provide their values in a dictionary with key value pairs. For example:

```php
$personalisation = [
    'first_name' => 'Amala',
    'reference_number' => '300241',
];
```

#### Response

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

#### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\ApiException` object containing the relevant error code:

|exc->getCode()|exc->getErrors()|Notes|
|:---|:---|:---|
|`400`|`[{`<br>`"error": "BadRequestError",`<br>`"message": "Missing personalisation: [PERSONALISATION FIELD]"`<br>`}]`|Check that the personalisation arguments in the method match the placeholder fields in the template.|
|`400`|`[{`<br>`"error": "NoResultFound",`<br>`"message": "No result found"`<br>`}]`|Check the [template ID](#generate-a-preview-template-arguments-templateid-required).|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: API key not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|


## Get received text messages

This API call returns one page of up to 250 received text messages. You can get either the most recent messages, or get older messages by specifying a particular notification ID in the `older_than` argument.

You can only get the status of messages that are 7 days old or newer.

You can also set up [callbacks](#callbacks) for received text messages.

### Enable received text messages

To receive text messages:

1. Go to the **Text message settings** section of the **Settings** page.
1. Select **Change** on the **Receive text messages** row.

### Get a page of received text messages

#### Method

```php
    $this->listReceivedTexts(
      $older_than  // optional
    );
```

To get older messages, pass the ID of an older notification into the `older_than` argument. This returns the next 250 oldest messages from the specified notification ID.

If you leave out the `older_than` argument, the client returns the most recent 250 notifications.

#### Arguments

##### older_than (optional)

Input the ID of a received text message into this argument. If you use this argument, the client returns the next 250 received text messages older than the given ID. For example:

```php
$older_than = '8e222534-7f05-4972-86e3-17c5d9f894e2'
```

If you leave out the `older_than` argument, the client returns the most recent 250 notifications.

The client only returns notifications that are 7 days old or newer. If the notification specified in this argument is older than 7 days, the client returns an empty collection response.

#### Response

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
#### Error codes

If the request is not successful, the client returns an `Alphagov\Notifications\Exception\ApiException` object containing the relevant error code:

|exc->getCode()|exc->getErrors()|Notes|
|:---|:---|:---|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Error: Your system clock must be accurate to within 30 seconds"`<br>`}]`|Check your system clock.|
|`403`|`[{`<br>`"error": "AuthError",`<br>`"message": "Invalid token: API key not found"`<br>`}]`|Use the correct API key. Refer to [API keys](#api-keys) for more information.|
