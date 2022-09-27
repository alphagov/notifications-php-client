<?php
namespace spec\unit\Alphagov\Notifications;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Alphagov\Notifications\Authentication\JWTAuthenticationInterface;
use Alphagov\Notifications\Client;
use Alphagov\Notifications\Exception as NotifyException;

use GuzzleHttp\Psr7\Uri;
use Http\Client\HttpClient as HttpClientInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Tests for the PHP Notify Client.
 *
 * Note: The Client never make assumptions about the response of successful queries to the Notify API.
 *          Therefore the response we test against here *may* differ from that the real API returns.
 *          That's okay, as testing response schema/data is beyond our scope.
 *
 * Class ClientSpec
 * @package spec\Alphagov\Notifications
 */
class ClientSpec extends ObjectBehavior
{
    const BASE_URL = 'https://api-test';
    const TEST_JWT_TOKEN = 'jwt-token';
    const SAMPLE_ID = '35836a9e-5a97-4d99-8309-0c5a2c3dbc72';

    private $httpClient;

    function getConstructorOptions( HttpClientInterface $httpClient, JWTAuthenticationInterface $authenticator ){

        $this->httpClient = $httpClient;

        $authenticator->createToken()->willReturn( self::TEST_JWT_TOKEN );

        $options = [
            'baseUrl' => self::BASE_URL,
            'authenticator' => $authenticator,
            'httpClient' => $this->httpClient,
        ];

        return $options;
    }


    function let( HttpClientInterface $httpClient, JWTAuthenticationInterface $authenticator ){

        $this->beConstructedWith( $this->getConstructorOptions( $httpClient, $authenticator ) );

    }

    function it_is_initializable(){
        $this->shouldHaveType('Alphagov\Notifications\Client');
    }

    //----------------------------------------------------------------------------------------------------------
    // Test constructor variations

    function it_exceptions_without_a_http_client( HttpClientInterface $httpClient, JWTAuthenticationInterface $authenticator ){

        //---------------------------------
        // Test Setup

        $options = $this->getConstructorOptions( $httpClient, $authenticator );
        unset( $options['httpClient'] );

        //---------------------------------
        // Perform action & check result

        $this->beConstructedWith( $options );
        $this->shouldThrow('Alphagov\Notifications\Exception\InvalidArgumentException')->duringInstantiation();

    }

    function it_exceptions_without_an_authenticator( HttpClientInterface $httpClient, JWTAuthenticationInterface $authenticator ){

        //---------------------------------
        // Test Setup

        $options = $this->getConstructorOptions( $httpClient, $authenticator );
        unset( $options['authenticator'] );

        //---------------------------------
        // Perform action & check result

        $this->beConstructedWith( $options );
        $this->shouldThrow('Alphagov\Notifications\Exception\InvalidArgumentException')->duringInstantiation();

    }

    function it_works_with_a_service_id_and_api_key( HttpClientInterface $httpClient, JWTAuthenticationInterface $authenticator ){

        //---------------------------------
        // Test Setup

        $options = $this->getConstructorOptions( $httpClient, $authenticator );
        unset( $options['authenticator'] );

        $options += [
            'serviceId' => '1546058f-5a25-4334-85ae-e68f2a44bbaf',
            'apiKey'    => '522ec739-ca63-4ec5-b082-08ce08ad65e2',
        ];

        //---------------------------------
        // Perform action

        /*
         * The below will throw an exception if a valid authenticator was not created.
         */

        $this->beConstructedWith( $options );

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode([])
            )
        );

        $this->listNotifications();

    }

    function it_works_with_an_api_key_only( HttpClientInterface $httpClient, JWTAuthenticationInterface $authenticator ){

        //---------------------------------
        // Test Setup

        $options = $this->getConstructorOptions( $httpClient, $authenticator );
        unset( $options['authenticator'] );

        $options += [
            'apiKey' => 'key_name-1546058f-5a25-4334-85ae-e68f2a44bbaf-522ec739-ca63-4ec5-b082-08ce08ad65e2',
        ];

        //---------------------------------
        // Perform action

        /*
         * The below will throw an exception if a valid authenticator was not created.
         */

        $this->beConstructedWith( $options );

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode([])
            )
        );

        $this->listNotifications();

    }

    function it_works_with_a_new_api_key_and_service_id( HttpClientInterface $httpClient, JWTAuthenticationInterface $authenticator ){

        //---------------------------------
        // Test Setup

        $options = $this->getConstructorOptions( $httpClient, $authenticator );
        unset( $options['authenticator'] );

        $options += [
            'serviceId' => '1546058f-5a25-4334-85ae-e68f2a44bbaf',
            'apiKey' => 'key_name-1546058f-5a25-4334-85ae-e68f2a44bbaf-522ec739-ca63-4ec5-b082-08ce08ad65e2',
        ];

        //---------------------------------
        // Perform action

        /*
         * The below will throw an exception if a valid authenticator was not created.
         */

        $this->beConstructedWith( $options );

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode([])
            )
        );

        $this->listNotifications();

    }

    function it_fails_with_an_invalid_api_key( HttpClientInterface $httpClient, JWTAuthenticationInterface $authenticator ){

        $options = $this->getConstructorOptions( $httpClient, $authenticator );
        unset( $options['authenticator'] );

        $options += [
            'apiKey' => 'key_name-1546058f-5a25-4334-85ae-e68f2a44bbaf-522ec739-ca63-cec5-b082-08ce08ad65e2',
        ];

        $this->beConstructedWith( $options );

        $this->shouldThrow(
            '\InvalidArgumentException'
        )->duringInstantiation();

    }

    //----------------------------------------------------------------------------------------------------------
    // Lookups (GETs) with expected success

    function it_generates_the_expected_request_when_looking_up_notification(){

        //---------------------------------
        // Test Setup

        $id = self::SAMPLE_ID;

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode(['notification_id' => $id])
            )
        );

        //---------------------------------
        // Perform action

        $this->getNotification( $id );

        //---------------------------------
        // Check result

        // Check the expected Request was sent.
        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($id) {

            // Check a request was sent.
            if( !( $v instanceof RequestInterface ) ){
                return false;
            }

            // With the correct URL
            if( $v->getUri() != self::BASE_URL . sprintf( Client::PATH_NOTIFICATION_LOOKUP, $id ) ){
                return false;
            }

            // Include the correct token header
            if( $v->getHeader('Authorization') != [ 'Bearer '.self::TEST_JWT_TOKEN ] ){
                return false;
            }

            // And correct Content-type
            if( $v->getHeader('Content-type') != [ 'application/json' ] ){
                return false;
            }

            return true;

        }))->shouldHaveBeenCalled();

    }

    function it_receives_the_expected_response_when_looking_up_notification(){

        //---------------------------------
        // Test Setup

        $id = self::SAMPLE_ID;

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode(['notification_id' => $id])
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->getNotification( $id );

        //---------------------------------
        // Check result

        $response->shouldHaveKeyWithValue('notification_id', $id);

    }

    function it_generates_the_expected_request_when_listing_notifications(){

        //---------------------------------
        // Test Setup

        $filters = [
            'older_than'=>'uuid',
            'template_type'=> 'sms',
            'reference'=> 'client-ref',
            'status'=>'delivered'
        ];

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode(['notifications' => array()])
            )
        );

        //---------------------------------
        // Perform action

        $this->listNotifications( $filters );

        //---------------------------------
        // Check result

        // Check the expected Request was sent.
        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($filters) {

            // Check a request was sent.
            if( !( $v instanceof RequestInterface ) ){
                return false;
            }

            //---

            $url = new Uri( self::BASE_URL . Client::PATH_NOTIFICATION_LIST );

            foreach( $filters as $name => $value ){
                $url = URI::withQueryValue($url, $name, $value );
            }

            // With the correct URL
            if( $v->getUri() != $url ){
                return false;
            }

            //---

            // Include the correct token header
            if( $v->getHeader('Authorization') != [ 'Bearer '.self::TEST_JWT_TOKEN ] ){
                return false;
            }

            // And correct Content-type
            if( $v->getHeader('Content-type') != [ 'application/json' ] ){
                return false;
            }

            return true;

        }))->shouldHaveBeenCalled();

    }

    function it_receives_the_expected_response_when_listing_notifications(){

        //---------------------------------
        // Test Setup

        $data = [[
            'created_at'=> '2016-04-06T11:06:10.260722+00:00',
            'id' => '217ce465-d16a-4179-928d-c1a73eb3f377'
        ]];

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode(['notifications' => $data])
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->listNotifications();

        //---------------------------------
        // Check result

        $response->shouldHaveKeyWithValue('notifications', $data);

    }

    function it_generates_the_expected_request_when_listing_received_texts(){

        //---------------------------------
        // Test Setup

        $filters = [
            'older_than'=>'uuid'
        ];

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode(['received_text_messages' => array()])
            )
        );

        //---------------------------------
        // Perform action

        $this->listReceivedTexts( $filters );

        //---------------------------------
        // Check result

        // Check the expected Request was sent.
        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($filters) {

            // Check a request was sent.
            if( !( $v instanceof RequestInterface ) ){
                return false;
            }

            //---

            $url = new Uri( self::BASE_URL . Client::PATH_RECEIVED_TEXT_LIST );

            foreach( $filters as $name => $value ){
                $url = URI::withQueryValue($url, $name, $value );
            }

            // With the correct URL
            if( $v->getUri() != $url ){
                return false;
            }

            //---

            // Include the correct token header
            if( $v->getHeader('Authorization') != [ 'Bearer '.self::TEST_JWT_TOKEN ] ){
                return false;
            }

            // And correct Content-type
            if( $v->getHeader('Content-type') != [ 'application/json' ] ){
                return false;
            }

            return true;

        }))->shouldHaveBeenCalled();

    }

    function it_receives_the_expected_response_when_listing_received_texts(){

        //---------------------------------
        // Test Setup

        $data = [[
            'created_at'=> '2016-04-06T11:06:10.260722+00:00',
            'id' => '217ce465-d16a-4179-928d-c1a73eb3f377'
        ]];

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                json_encode(['received_text_messages' => $data])
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->listReceivedTexts();

        //---------------------------------
        // Check result

        $response->shouldHaveKeyWithValue('received_text_messages', $data);

    }

    //----------------------------------------------------------------------------------------------------------
    // Sending (POSTs) with expected success

    function it_generates_the_expected_request_when_sending_sms(){

        //---------------------------------
        // Test Setup

        $payload = [
            'phone_number' => '+447834000000',
            'template_id'=> 118,
            'personalisation' => [
                'name'=>'Fred'
            ]
        ];

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                [ 'Content-type'  => 'application/json' ],
                json_encode([ 'notification_id' => 'xxx' ])
            )
        );

        //---------------------------------
        // Perform action

        $this->sendSms( $payload['phone_number'], $payload['template_id'], $payload['personalisation'] );

        //---------------------------------
        // Check result

        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($payload) {

            // Check a request was sent.
            if( !( $v instanceof RequestInterface ) ){
                return false;
            }

            // With the correct URL
            if( $v->getUri() != self::BASE_URL . Client::PATH_NOTIFICATION_SEND_SMS ){
                return false;
            }

            // Include the correct token header
            if( $v->getHeader('Authorization') != [ 'Bearer '.self::TEST_JWT_TOKEN ] ){
                return false;
            }

            // And correct Content-type
            if( $v->getHeader('Content-type') != [ 'application/json' ] ){
                return false;
            }

            // With the expected body.
            if( json_decode( $v->getBody(), true ) != $payload ){
                return false;
            }

            return true;

        }))->shouldHaveBeenCalled();

    }

    function it_receives_the_expected_response_when_sending_sms(){

        //---------------------------------
        // Test Setup

        $id = self::SAMPLE_ID;

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                ['Content-type'  => 'application/json'],
                json_encode(['notification_id' => $id])
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->sendSms( '+447834000000', 118, [ 'name'=>'Fred' ] );

        //---------------------------------
        // Check result

        $response->shouldHaveKeyWithValue('notification_id', $id);

    }

    function it_receives_the_expected_response_when_sending_sms_with_sms_sender_id(){

        //---------------------------------
        // Test Setup

        $id = self::SAMPLE_ID;

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                ['Content-type'  => 'application/json'],
                json_encode(['notification_id' => $id])
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->sendSms( '+447834000000', 118, [ 'name'=>'Fred' ], 'ref123', '1234567' );

        //---------------------------------
        // Check result

        $response->shouldHaveKeyWithValue('notification_id', $id);

    }

    function it_generates_the_expected_request_when_sending_email(){

        //---------------------------------
        // Test Setup

        $payload = [
            'email_address' => 'text@example.com',
            'template_id'=> 118,
            'personalisation' => [
                'name'=>'Fred'
            ],
            'reference'=>'client-ref'
        ];

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                [ 'Content-type'  => 'application/json' ],
                json_encode([ 'notification_id' => 'xxx' ])
            )
        );

        //---------------------------------
        // Perform action

        $this->sendEmail( $payload['email_address'], $payload['template_id'], $payload['personalisation'], $payload['reference'] );

        //---------------------------------
        // Check result

        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($payload) {

            // Check a request was sent.
            if( !( $v instanceof RequestInterface ) ){
                return false;
            }

            // With the correct URL
            if( $v->getUri() != self::BASE_URL . Client::PATH_NOTIFICATION_SEND_EMAIL ){
                return false;
            }

            // Include the correct token header
            if( $v->getHeader('Authorization') != [ 'Bearer '.self::TEST_JWT_TOKEN ] ){
                return false;
            }

            // And correct Content-type
            if( $v->getHeader('Content-type') != [ 'application/json' ] ){
                return false;
            }

            // With the expected body.
            if( json_decode( $v->getBody(), true ) != $payload ){
                return false;
            }

            return true;

        }))->shouldHaveBeenCalled();

    }

    function it_receives_the_expected_response_when_sending_email(){

        //---------------------------------
        // Test Setup

        $id = self::SAMPLE_ID;

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                ['Content-type'  => 'application/json'],
                json_encode(['notification_id' => $id])
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->sendEmail( 'text@example.com', 118, [ 'name'=>'Fred' ] );

        //---------------------------------
        // Check result

        $response->shouldHaveKeyWithValue('notification_id', $id);

    }

    function it_receives_the_expected_response_when_sending_email_with_valid_emailReplyToId(){

                //---------------------------------
                // Test Setup

                $id = self::SAMPLE_ID;

                $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
                    new Response(
                        201,
                        ['Content-type'  => 'application/json'],
                        json_encode(['notification_id' => $id])
                    )
                );

                //---------------------------------
                // Perform action

                $response = $this->sendEmail( 'text@example.com', 118, [ 'name'=>'Fred' ], '',  uniqid() );

                //---------------------------------
                // Check result

                $response->shouldHaveKeyWithValue('notification_id', $id);

            }


    function it_generates_the_expected_request_when_preparing_file_for_upload(){
        $this->prepareUpload('%PDF-1.5 testpdf')->shouldReturn( [ 'file' => 'JVBERi0xLjUgdGVzdHBkZg==', 'is_csv' => false, 'confirm_email_before_download' => NULL, 'retention_period' => NULL ] );
    }


    function it_generates_the_expected_request_when_preparing_a_csv_file_for_upload(){
        $this->prepareUpload('%PDF-1.5 testpdf', true)->shouldReturn( [ 'file' => 'JVBERi0xLjUgdGVzdHBkZg==', 'is_csv' => true, 'confirm_email_before_download' => NULL, 'retention_period' => NULL ] );
    }


    function it_generates_the_expected_request_when_configuring_email_confirmation_and_retention(){
        $this->prepareUpload('%PDF-1.5 testpdf', true, true, "1 weeks")->shouldReturn( [ 'file' => 'JVBERi0xLjUgdGVzdHBkZg==', 'is_csv' => true, 'confirm_email_before_download' => true, 'retention_period' => '1 weeks' ] );
    }


    function it_raises_an_exception_when_preparing_file_for_upload_if_file_too_big(){
        $this->shouldThrow(NotifyException\InvalidArgumentException::class)->duringPrepareUpload(str_repeat( "a", 3 * 1024 * 1024 ));
    }


    //----------------------------------------------------------------------------------------------------------
    // Actions with expected errors

    function it_receives_null_when_the_api_returns_404(){

        //---------------------------------
        // Test Setup

        $code = 404;

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                $code,
                ['Content-type'  => 'application/json']
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->getNotification( '35836a9e-5a97-4d99-8309-0c5a2c3dbc72' );

        //---------------------------------
        // Check result

        $response->shouldBeNull();

    }

    function it_receives_an_exception_when_the_api_returns_500(){

        //---------------------------------
        // Test Setup

        $code = 500;
        $body = [
          'status' => $code,
          'errors' => [
            [
              'error' => 'SomeErrorType',
              'message' => 'Some error message'
            ]
          ]
        ];
        $response = new Response(
            $code,
            ['Content-type'  => 'application/json'],
            json_encode($body)
        );

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            $response
        );

        //---------------------------------
        // Perform action & check result

        $this->shouldThrow(
            new NotifyException\ApiException( "HTTP:{$code}", $code, $body, $response )
        )->duringSendSms( '+447834000000', 118, [ 'name'=>'Fred' ] );

    }

    //----------------------------------------------------------------------------------------------------------
    // Template lookups (GETs) with expected success

    function it_generates_the_expected_request_when_looking_up_a_template(){

        //---------------------------------
        // Test Setup

        $id = self::SAMPLE_ID;

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                '{}'
            )
        );

        //---------------------------------
        // Perform action

        $this->getTemplate( $id );

        //---------------------------------
        // Check result

        // Check the expected Request was sent.
        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($id) {
            return $v->getUri() == self::BASE_URL . sprintf( Client::PATH_TEMPLATE_LOOKUP, $id );
        }))->shouldHaveBeenCalled();

    }

    function it_generates_the_expected_request_when_looking_up_a_template_by_version(){

        //---------------------------------
        // Test Setup

        $id = self::SAMPLE_ID;
        $version = 1;

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                '{}'
            )
        );

        //---------------------------------
        // Perform action

        $this->getTemplateVersion( $id, $version );

        //---------------------------------
        // Check result

        // Check the expected Request was sent.
        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($id, $version) {
            // With the correct URL
            return $v->getUri() == self::BASE_URL . sprintf( Client::PATH_TEMPLATE_VERSION_LOOKUP, $id, $version );

        }))->shouldHaveBeenCalled();

    }

    function it_generates_the_expected_request_when_listing_templates(){

        //---------------------------------
        // Test Setup

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                '{}'
            )
        );

        //---------------------------------
        // Perform action

        $this->listTemplates();

        //---------------------------------
        // Check result

        // Check the expected Request was sent.
        $this->httpClient->sendRequest( Argument::that(function( $v ) {
            // With the correct URL
            $url = new Uri( self::BASE_URL . Client::PATH_TEMPLATE_LIST );

            return $v->getUri() == $url;
        }))->shouldHaveBeenCalled();

    }


    function it_generates_the_expected_request_when_listing_templates_of_a_certain_type(){

        //---------------------------------
        // Test Setup

        $templateType = 'foo';
        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/json'],
                '{}'
            )
        );

        //---------------------------------
        // Perform action

        $this->listTemplates( $templateType );

        //---------------------------------
        // Check result

        // Check the expected Request was sent.
        $this->httpClient->sendRequest( Argument::that(function( $v ) use ( $templateType ){
            // With the correct URL
            $url = new Uri( self::BASE_URL . Client::PATH_TEMPLATE_LIST );
            $url = URI::withQueryValue( $url, 'type', $templateType );

            return $v->getUri() == $url;
        }))->shouldHaveBeenCalled();

    }


    //----------------------------------------------------------------------------------------------------------
    // Sending (POSTs) with expected success

    function it_generates_the_expected_request_when_previewing_a_template(){

        //---------------------------------
        // Test Setup

        $id = self::SAMPLE_ID;
        $body = [
            'personalisation' => [
                'name'=>'Fred'
            ]
        ];

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                [ 'Content-type'  => 'application/json' ],
                '{}'
            )
        );

        //---------------------------------
        // Perform action

        $this->previewTemplate( $id, $body['personalisation']);

        //---------------------------------
        // Check result

        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($id, $body) {
            // With the correct URL
            if( $v->getUri() != self::BASE_URL . sprintf( Client::PATH_TEMPLATE_PREVIEW, $id ) ){
                return false;
            }

            // With the expected body.
            if( json_decode( $v->getBody(), true ) != $body ){
                return false;
            }

            return true;

        }))->shouldHaveBeenCalled();

    }

    function it_receives_the_expected_response_when_sending_letter(){

        //---------------------------------
        // Test Setup

        $id = self::SAMPLE_ID;

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                ['Content-type'  => 'application/json'],
                json_encode(['notification_id' => $id])
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->sendLetter( 118, [
                'name'=>'Fred',
                'address_line_1' => 'Foo',
                'address_line_2' => 'Bar',
                'postcode' => 'SW1 1AA'
            ]
        );

        //---------------------------------
        // Check result

        $response->shouldHaveKeyWithValue('notification_id', $id);

    }

    function it_generates_the_expected_request_when_sending_letter(){

        //---------------------------------
        // Test Setup

        $payload = [
            'template_id'=> 118,
            'personalisation' => [
                'name'=>'Fred',
                'address_line_1' => 'Foo',
                'address_line_2' => 'Bar',
                'postcode' => 'SW1 1AA'
            ],
            'reference'=>'client-ref'
        ];

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                [ 'Content-type'  => 'application/json' ],
                json_encode([ 'notification_id' => 'xxx' ])
            )
        );

        //---------------------------------
        // Perform action

        $this->sendLetter( $payload['template_id'], $payload['personalisation'], $payload['reference']);

        //---------------------------------
        // Check result

        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($payload) {

            // Check a request was sent.
            if( !( $v instanceof RequestInterface ) ){
                return false;
            }

            // With the correct URL
            if( $v->getUri() != self::BASE_URL . Client::PATH_NOTIFICATION_SEND_LETTER ){
                return false;
            }

            // Include the correct token header
            if( $v->getHeader('Authorization') != [ 'Bearer '.self::TEST_JWT_TOKEN ] ){
                return false;
            }

            // And correct Content-type
            if( $v->getHeader('Content-type') != [ 'application/json' ] ){
                return false;
            }

            // With the expected body.
            if( json_decode( $v->getBody(), true ) != $payload ){
                return false;
            }

            return true;

        }))->shouldHaveBeenCalled();

    }

    function it_receives_the_expected_response_when_sending_a_precompiled_letter(){

        //---------------------------------
        // Test Setup

        $id = self::SAMPLE_ID;
        $ref = 'my_ref_1234';

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                ['Content-type'  => 'application/json'],
                json_encode(['notification_id' => $id, 'reference' => $ref])
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->sendPrecompiledLetter( $ref, '%PDF-1.5 testpdf' );

        //---------------------------------
        // Check result

        $response->shouldHaveKeyWithValue('notification_id', $id);
        $response->shouldHaveKeyWithValue('reference', $ref);

    }

    function it_generates_the_expected_request_when_sending_a_precompiled_letter(){

        //---------------------------------
        // Test Setup

        $payload = [
            'reference' => 'my_ref_1234',
            'content' => 'JVBERi0xLjUgdGVzdHBkZg=='
        ];

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                201,
                [ 'Content-type'  => 'application/json' ],
                json_encode([ 'notification_id' => 'xxx' ])
            )
        );

        //---------------------------------
        // Perform action

        $this->sendPrecompiledLetter( $payload['reference'], '%PDF-1.5 testpdf' );

        //---------------------------------
        // Check result

        $this->httpClient->sendRequest( Argument::that(function( $v ) use ($payload) {

            // Check a request was sent.
            if( !( $v instanceof RequestInterface ) ){
                return false;
            }

            // With the correct URL
            if( $v->getUri() != self::BASE_URL . Client::PATH_NOTIFICATION_SEND_LETTER ){
                return false;
            }

            // Include the correct token header
            if( $v->getHeader('Authorization') != [ 'Bearer '.self::TEST_JWT_TOKEN ] ){
                return false;
            }

            // And correct Content-type
            if( $v->getHeader('Content-type') != [ 'application/json' ] ){
                return false;
            }

            // With the expected body.
            if( json_decode( $v->getBody(), true ) != $payload ){
                return false;
            }

            return true;

        }))->shouldHaveBeenCalled();

    }


    //----------------------------------------------------------------------------------------------------------
    // Getting a PDF for a letter


    function it_generates_the_expected_request_when_getting_a_pdf_for_a_letter(){

        //---------------------------------
        // Test Setup

        $id = self::SAMPLE_ID;
        $expected_url = self::BASE_URL . sprintf( Client::PATH_NOTIFICATION_PDF, $id );

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/pdf'],
                'foobar'
            )
        );

        //---------------------------------
        // Perform action

        $this->getPdfForLetter( $id );

        //---------------------------------
        // Check result

        // Check the expected Request was sent.
        $this->httpClient->sendRequest( Argument::that(function( $v ) use ( $expected_url ) {

            // Check a request was sent.
            if( !( $v instanceof RequestInterface ) ){
                return false;
            }

            // With the correct URL
            if( $v->getUri() != $expected_url ){
                return false;
            }

            // Include the correct token header
            if( $v->getHeader('Authorization') != [ 'Bearer '.self::TEST_JWT_TOKEN ] ){
                return false;
            }

            return true;

        }))->shouldHaveBeenCalled();

    }

    function it_receives_the_expected_response_when_getting_a_pdf_for_a_letter(){

        //---------------------------------
        // Test Setup

        $id = self::SAMPLE_ID;

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            new Response(
                200,
                ['Content-type'  => 'application/pdf'],
                'foobar'
            )
        );

        //---------------------------------
        // Perform action

        $response = $this->getPdfForLetter( $id );

        //---------------------------------
        // Check result

        $response->shouldBeEqualTo('foobar');

    }

    function it_receives_an_exception_when_getPdfForLetter_returns_400(){

        //---------------------------------
        // Test Setup

        $code = 400;
        $body = [
          'status' => $code,
          'errors' => [
            [
              'error' => 'SomeErrorType',
              'message' => 'Some error message'
            ]
          ]
        ];
        $response = new Response(
            $code,
            ['Content-type'  => 'application/json'],
            json_encode($body)
        );

        $this->httpClient->sendRequest( Argument::type('Psr\Http\Message\RequestInterface') )->willReturn(
            $response
        );

        //---------------------------------
        // Perform action & check result

        $this->shouldThrow(
            new NotifyException\ApiException( "HTTP:{$code}", $code, $body, $response )
        )->duringGetPdfForLetter( self::SAMPLE_ID );

    }
}
