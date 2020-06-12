# Contributing

Pull requests are welcome.

## Tests

There are unit and integration tests that can be run to test functionality of the client.

## Unit Tests

To run the unit tests:

```sh
vendor/bin/phpspec run spec/unit/ --format=pretty --verbose
```

## Integration Tests

Before running the integration tests, please ensure that the environment variables are set up.

```
export NOTIFY_API_URL="https://example.notify-api.url"
export API_KEY="example_API_test_key"
export FUNCTIONAL_TEST_NUMBER="valid mobile number"
export FUNCTIONAL_TEST_EMAIL="valid email address"
export EMAIL_TEMPLATE_ID="valid email_template_id"
export SMS_TEMPLATE_ID="valid sms_template_id"
export LETTER_TEMPLATE_ID="valid letter_template_id"
export EMAIL_REPLY_TO_ID="valid email reply to id"
export SMS_SENDER_ID="valid sms_sender_id - to test sending to a receiving number, so needs to be a valid number"
export API_SENDING_KEY="API_team_key for sending a SMS to a receiving number"
export INBOUND_SMS_QUERY_KEY="API_test_key to get received text messages"
```

To run the integration tests:

```sh
vendor/bin/phpspec run spec/integration/ --format=pretty --verbose
```

To run both sets of tests:

```sh
vendor/bin/phpspec run --format=pretty
```
