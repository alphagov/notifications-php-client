<?php
namespace Alphagov\Notifications;

use GuzzleHttp\Psr7\Uri;                                    // Concrete PSR-7 URL representation.
use GuzzleHttp\Psr7\Request;                                // Concrete PSR-7 HTTP Request
use Psr\Http\Message\ResponseInterface;                     // PSR-7 HTTP Response Interface
use Psr\Http\Client\ClientInterface as HttpClientInterface; // Interface for a PSR-7 compatible HTTP Client.

use Alphagov\Notifications\Authentication\JWTAuthenticationInterface;

/**
 * Client for accessing GOV.UK Notify.
 *
 * Before using this client you must have:
 *  - created an account with GOV.UK Notify
 *  - found your Service ID and generated an API Key.
 *  - created at least one template and know its ID.
 *
 * Class Client
 * @package Alphagov\Notifications
 */
class Client {

    /**
     * @const string Current version of this client.
     * This follows Semantic Versioning (http://semver.org/)
     */
    const VERSION = '6.0.0';

    /**
     * @const string The API endpoint for Notify production.
     */
    const BASE_URL_PRODUCTION = 'https://api.notifications.service.gov.uk';

    /**
     * Paths for API endpoints.
     */
    const PATH_NOTIFICATION_LIST        = '/v2/notifications';
    const PATH_NOTIFICATION_LOOKUP      = '/v2/notifications/%s';
    const PATH_NOTIFICATION_PDF         = '/v2/notifications/%s/pdf';
    const PATH_NOTIFICATION_SEND_SMS    = '/v2/notifications/sms';
    const PATH_NOTIFICATION_SEND_EMAIL  = '/v2/notifications/email';
    const PATH_NOTIFICATION_SEND_LETTER = '/v2/notifications/letter';
    const PATH_RECEIVED_TEXT_LIST       = '/v2/received-text-messages';
    const PATH_TEMPLATE_LIST            = '/v2/templates';
    const PATH_TEMPLATE_LOOKUP          = '/v2/template/%s';
    const PATH_TEMPLATE_VERSION_LOOKUP  = '/v2/template/%s/version/%s';
    const PATH_TEMPLATE_PREVIEW         = '/v2/template/%s/preview';


    /**
     * @var string base scheme and hostname
     */
    protected $baseUrl;

    /**
     * @var HttpClientInterface PSR-7 compatible HTTP Client
     */
    private $httpClient;

    /**
     * @var JWTAuthenticationInterface
     */
    private $authenticator;


    /**
     * Instantiates a new GOV.UK Notify Client
     *
     * The client constructor accepts the following options:
     *  - httpClient: (HttpClientInterface)
     *      Required.
     *  - authenticator: (JWTAuthenticationInterface)
     *      Required if 'serviceId' and 'apiKey' are not set.
     *  - serviceId: (string)
     *      Optional. Deprecated, use apiKey instead.
     *  - apiKey: (string)
     *      Required if 'authenticator' not set.
     *  - baseUrl: (string)
     *      Optional. The Notify base URL to make API calls to.
     *      If not set, this defaults to the production API.
     *
     * @param array $config
     */
    public function __construct( array $config ){

        $config = array_merge([
            'httpClient'    => null,
            'authenticator' => null,
            'serviceId'     => null,
            'apiKey'        => null,
            'baseUrl'       => null,
        ], $config);

        //--------------------------
        // Set base URL

        if( !isset( $config['baseUrl'] ) ){

            // If not set, we default to production
            $this->baseUrl = self::BASE_URL_PRODUCTION;

        } elseif ( filter_var($config['baseUrl'], FILTER_VALIDATE_URL) !== false ) {

            // Else we allow an arbitrary URL to be set.
            $this->baseUrl = $config['baseUrl'];

        } else {

            throw new Exception\InvalidArgumentException(
                "Invalid 'baseUrl' set. This must be either a valid URL, or null."
            );

        }

        //--------------------------
        // Set HTTP Client

        if( $config['httpClient'] instanceof HttpClientInterface ){

            $this->setHttpClient( $config['httpClient'] );

        } else {

            throw new Exception\InvalidArgumentException(
                "An instance of HttpClientInterface must be set under 'httpClient'"
            );

        }

        //--------------------------
        // Set/create authenticator

        if( $config['authenticator'] instanceof JWTAuthenticationInterface ){

            $this->setAuthenticator( $config['authenticator'] );

        } elseif( isset($config['apiKey']) ){

            // If we're missing the serviceId, assume it's contained within the apiKey string.
            if( !isset($config['serviceId']) ) {
              $config['serviceId'] = substr($config['apiKey'], -73, 36);
            }

            $this->setAuthenticator(new Authentication\JsonWebToken(
                $config['serviceId'],
                substr($config['apiKey'], -36, 36)
            ));

        } else {

            throw new Exception\InvalidArgumentException(
                "Either an instance of JWTAuthenticationInterface must be set under 'authenticator', ".
                "or 'serviceId' and 'apiKey' must be set."
            );

        }

    }

    //------------------------------------------------------------------------------------
    // Public API access methods

    /**
     * Send an SMS message.
     *
     * @param string    $phoneNumber
     * @param string    $templateId
     * @param array     $personalisation
     * @param string    $reference
     *
     * @return array
     */
    public function sendSms( $phoneNumber, $templateId, array $personalisation = array(), $reference = '', $smsSenderId = NULL ){

        return $this->httpPost(
            self::PATH_NOTIFICATION_SEND_SMS,
            $this->buildSmsPayload( 'sms', $phoneNumber, $templateId, $personalisation, $reference, $smsSenderId)
        );

    }

    /**
     * Send an Email message.
     *
     * @param string    $emailAddress
     * @param string    $templateId
     * @param array     $personalisation
     * @param string    $reference
     *
     * @return array
     */
    public function sendEmail( $emailAddress, $templateId, array $personalisation = array(), $reference = '', $emailReplyToId = NULL ){

        return $this->httpPost(
            self::PATH_NOTIFICATION_SEND_EMAIL,
            $this->buildEmailPayload( 'email', $emailAddress, $templateId, $personalisation, $reference, $emailReplyToId )
        );

    }

    /**
     * Send a Letter
     *
     * @param string    $templateId
     * @param array     $personalisation
     * @param string    $reference
     *
     * @return array
     */
    public function sendLetter( $templateId, array $personalisation = array(), $reference = '' ){

        $payload = $this->buildPayload( 'letter', '', $templateId, $personalisation, $reference );

        return $this->httpPost(
            self::PATH_NOTIFICATION_SEND_LETTER,
            $payload
        );

    }

    /**
     * Send a precompiled letter.
     * Example usage: sendPrecompiledLetter($templateId, $ref, file_get_contents(<PATH TO FILE>)))
     *
     * @param string    $templateId
     * @param string    $reference
     * @param string    $pdf_data
     *
     * @return array
     */
    public function sendPrecompiledLetter( $reference, $pdf_data, $postage = NULL ){
        $payload = [
          'reference' => $reference,
          'content' => base64_encode($pdf_data)
        ];

        if ($postage != NULL) {
          $payload['postage'] = $postage;
        }

        return $this->httpPost(
            self::PATH_NOTIFICATION_SEND_LETTER,
            $payload
        );

    }

    /**
     * Returns details about the passed notification ID.
     *
     * NULL is returned if no notification is found for the ID.
     *
     * @param string $notificationId
     *
     * @return array|null
     */
    public function getNotification( $notificationId ){

        $path = sprintf( self::PATH_NOTIFICATION_LOOKUP, $notificationId );

        return $this->httpGet( $path );
    }

    /**
     * This returns the pdf contents of a letter notification.
     *
     * @param string $notificationId
     *
     * @return string
     */
    public function getPdfForLetter( $notificationId ){

        $path = sprintf( self::PATH_NOTIFICATION_PDF, $notificationId );

        $url = $this->createURL( $path, [] );
        $response = $this->makeGetRequest( $url );

        switch( $response->getStatusCode() ){
            case 200:
                // can't use httpGet as it expects json, we just need to return the entire response body as a string
                return $response->getBody()->getContents();
            default:
                return $this->handleErrorResponse( $response );
        }
    }

    /**
     * Returns a list of all notifications for the current Service ID.
     *
     * Filter supports:
     *  - older_than
     *  - reference
     *  - status
     *  - template_type
     *
     * @param array $filters
     *
     * @return mixed|null
     */
    public function listNotifications( array $filters = array() ){

        // Only allow the following filter keys.
        $filters = array_intersect_key( $filters, array_flip([
            'older_than',
            'reference',
            'status',
            'template_type',
        ]));

        return $this->httpGet( self::PATH_NOTIFICATION_LIST, $filters );

    }

    /**
     * Returns a list of all received texts for the current Service ID.
     *
     * Filter supports:
     *  - older_than
     *
     * @param array $filters
     *
     * @return mixed|null
     */
    public function listReceivedTexts( array $filters = array() ){

        // Only allow the following filter keys.
        $filters = array_intersect_key( $filters, array_flip([
            'older_than'
        ]));

        return $this->httpGet( self::PATH_RECEIVED_TEXT_LIST, $filters );

    }

    /**
     * Get a template by ID.
     *
     * @param string    $templateId
     *
     * @return array
     */
    public function getTemplate( $templateId ){
        $path = sprintf( self::PATH_TEMPLATE_LOOKUP, $templateId );

        return $this->httpGet( $path );

    }

    /**
     * Get a template by ID and version.
     *
     * @param string    $templateId
     * @param int       $version
     *
     * @return array
     */
    public function getTemplateVersion( $templateId, $version ){
        $path = sprintf( self::PATH_TEMPLATE_VERSION_LOOKUP, $templateId, $version );

        return $this->httpGet( $path );
    }

    /**
     * Get all templates
     *
     * Can pass in template_type to filter templates.
     *
     * @param string  $template_type
     *
     * @return array
     */
    public function listTemplates( $templateType = null ){
        $queryParams = is_null( $templateType ) ? [] : [ 'type' => $templateType ];
        return $this->httpGet( self::PATH_TEMPLATE_LIST, $queryParams );
    }

    /**
     * Get a preview of a template
     *
     * @return array
     */
    public function previewTemplate( $templateId, $personalisation){
        $path = sprintf( self::PATH_TEMPLATE_PREVIEW, $templateId );
        $payload = [
          'personalisation'=>$personalisation
        ];
        return $this->httpPost( $path, $payload );
    }

    /**
     * Prepare a file before adding it to the $personalisation array for the sendEmail function
     *
     * @param string $file_contents
     * @param string $filename
     * @param bool $confirm_email_before_download
     * @param string $retention_period
     *
     * @return array
     */
    public function prepareUpload( $file_contents, $filename = NULL, $confirm_email_before_download = NULL, $retention_period = NULL ){
        if ( strlen($file_contents) > ( 2 * 1024 * 1024 )) {
            throw new Exception\InvalidArgumentException( 'File is larger than 2MB.' );
        }
        $data = [
            "file" => base64_encode($file_contents),
        ];

        $data['filename'] = $filename;
        $data['confirm_email_before_download'] = $confirm_email_before_download;
        $data['retention_period'] = $retention_period;

        return $data;
    }

    //------------------------------------------------------------------------------------
    // Internal API access methods


    //-------------------------------------------
    // Build request

    /**
     * Generates the payload expected by the API.
     *
     * @param string    $type
     * @param string    $to
     * @param string    $templateId
     * @param array     $personalisation
     * @param string    $reference
     *
     * @return array
     */
    private function buildPayload( $type, $to, $templateId, array $personalisation, $reference ){

        $payload = [
            'template_id'=> $templateId
        ];

        if ( $type == 'sms' ) {
            $payload['phone_number'] = $to;
        } else if ( $type == 'email' ) {
            $payload['email_address'] = $to;
        }

        if ( count($personalisation) > 0 ) {
            $payload['personalisation'] = $personalisation;
        }

        if ( isset($reference) && $reference != '' ) {
            $payload['reference'] = $reference;
        }

        return $payload;

    }

    /**
     * Generates the payload expected by the API for email adding the optional items.
     *
     * @param string    $type
     * @param string    $to
     * @param string    $templateId
     * @param array     $personalisation
     * @param string    $reference
     * @param string    $emailReplyToId
     *
     * @return array
     */
    private function buildEmailPayload( $type, $to, $templateId, array $personalisation, $reference, $emailReplyToId = NULL ) {

        $payload = $this->buildPayload( $type, $to, $templateId, $personalisation, $reference );

        if ( isset($emailReplyToId) && $emailReplyToId != '' ) {
            $payload['email_reply_to_id'] = $emailReplyToId;
        }

        return $payload;

    }

    /**
     * Generates the payload expected by the API for sms adding the optional items.
     *
     * @param string    $type
     * @param string    $to
     * @param string    $templateId
     * @param array     $personalisation
     * @param string    $reference
     * @param string    $smsSenderId
     *
     * @return array
     */
    private function buildSmsPayload( $type, $to, $templateId, array $personalisation, $reference, $smsSenderId = NULL ){

        $payload = $this->buildPayload( $type, $to, $templateId, $personalisation, $reference );

        if ( isset($smsSenderId) && $smsSenderId != '' ) {
            $payload['sms_sender_id'] = $smsSenderId;
        }

        return $payload;

    }

    /**
     * Generates the standard set of HTTP headers expected by the API.
     *
     * @return array
     */
    private function buildHeaders(){

        return [
            'Authorization' => 'Bearer '.$this->getAuthenticator()->createToken(),
            'Accept'        => 'application/json',
            'Content-type'  => 'application/json',
            'User-agent'    => 'NOTIFY-API-PHP-CLIENT/'.self::VERSION
        ];

    }

    //-------------------------------------------
    // GET & POST requests

    /**
     * Performs a GET against the Notify API.
     *
     * @param string $path
     * @param array  $query
     *
     * @return array|null
     * @throw Exception\NotifyException | Exception\ApiException | Exception\UnexpectedValueException
     */
    private function httpGet( $path, array $query = array() ){
        $url = $this->createURL( $path, $query );
        $response = $this->makeGetRequest( $url );
        return $this->handleJsonResponse( $response );
    }

    private function createURL( $path, $query ) {
        $url = new Uri( $this->baseUrl . $path );

        foreach( $query as $name => $value ){
            $url = URI::withQueryValue( $url, $name, $value );
        }

        return $url;
    }

    private function makeGetRequest( $url ) {
        $request = new Request(
            'GET',
            $url,
            $this->buildHeaders()
        );

        try {

            return $this->getHttpClient()->sendRequest( $request );

        } catch (\RuntimeException $e){
            throw new Exception\NotifyException( $e->getMessage(), $e->getCode(), $e );
        }
    }

    private function handleJsonResponse( $response ) {
        switch( $response->getStatusCode() ){
            case 200:
                return $this->handleResponse( $response );
            case 404:
                return null;
            default:
                return $this->handleErrorResponse( $response );
        }
    }


    /**
     * Performs a POST against the Notify API.
     *
     * @param string $path
     * @param array  $payload
     *
     * @return array
     * @throw Exception\NotifyException | Exception\ApiException | Exception\UnexpectedValueException
     */
    private function httpPost( $path, Array $payload ){

        $url = new Uri( $this->baseUrl . $path );

        $request = new Request(
            'POST',
            $url,
            $this->buildHeaders(),
            json_encode( $payload )
        );

        try {

            $response = $this->getHttpClient()->sendRequest( $request );

        } catch (\RuntimeException $e){
            throw new Exception\NotifyException( $e->getMessage(), $e->getCode(), $e );
        }

        //---

        switch( $response->getStatusCode() ){
            case 200:
            case 201:
                return $this->handleResponse( $response );
            default:
                return $this->handleErrorResponse( $response );
        }

    }

    //-------------------------------------------
    // Response Handling

    /**
     * Called with a response from the API when the response code was successful. i.e. 20X.
     *
     * @param ResponseInterface $response
     *
     * @return array
     * @throw Exception\ApiException
     */
    protected function handleResponse( ResponseInterface $response ){

        $body = json_decode($response->getBody(), true);

        // The expected response should always be JSON, thus now an array.
        if( !is_array($body) ){
            throw new Exception\ApiException( 'Malformed JSON response from server', $response->getStatusCode(), $body, $response );
        }

        return $body;

    }

    /**
     * Called with a response from the API when the response code was unsuccessful. i.e. not 20X.
     *
     * @param ResponseInterface $response
     *
     * @return null
     * @throw Exception\ApiException
     */
    protected function handleErrorResponse( ResponseInterface $response ){

        $body = json_decode($response->getBody(), true);

        $message = "HTTP:{$response->getStatusCode()}";

        throw new Exception\ApiException( $message, $response->getStatusCode(), $body, $response );

    }


    //------------------------------------------------------------------------------------
    // Getters and setters

    /**
     * @return HttpClientInterface
     * @throws Exception\UnexpectedValueException
     */
    final protected function getHttpClient(){

        if( !( $this->httpClient instanceof HttpClientInterface ) ){
            throw new Exception\UnexpectedValueException('Invalid HttpClient set');
        }

        return $this->httpClient;

    }

    /**
     * @param HttpClientInterface $client
     */
    final protected function setHttpClient( HttpClientInterface $client ){

        $this->httpClient = $client;

    }

    /**
     * @return JWTAuthenticationInterface
     * @throws Exception\UnexpectedValueException
     */
    final protected function getAuthenticator(){

        if( !( $this->authenticator instanceof JWTAuthenticationInterface ) ){
            throw new Exception\UnexpectedValueException('Invalid JWTAuthenticationInterface set');
        }

        return $this->authenticator;

    }

    /**
     * @param JWTAuthenticationInterface $authenticator
     */
    final protected function setAuthenticator( JWTAuthenticationInterface $authenticator ){

        $this->authenticator = $authenticator;

    }
}
