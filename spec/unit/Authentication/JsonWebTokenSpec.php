<?php
namespace spec\unit\Alphagov\Notifications\Authentication;

use Firebase\JWT\Key;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PhpSpec\Exception\Example\FailureException;

use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

/**
 * Tests for our JsonWebToken authenticator.
 *
 * Class JsonWebTokenSpec
 * @package spec\Alphagov\Notifications\Authentication
 */
class JsonWebTokenSpec extends ObjectBehavior
{

    const SERVICE_ID    = '00000000-0000-4000-a000-000000000000';
    const API_KEY       = 'cccccccc-cccc-4ccc-9ccc-cccccccccccc';


    function let(){
        $this->beConstructedWith( self::SERVICE_ID, self::API_KEY );
    }


    function it_is_initializable(){

        $this->shouldHaveType('Alphagov\Notifications\Authentication\JsonWebToken');

    }

    function it_generates_a_token_valid_string(){

        $this->createToken()->shouldBeValidJWSToken();

    }

    function it_fails_with_an_invalid_service_id(){

        $this->beConstructedWith( 'invalid-service-id', self::API_KEY );

        $this->shouldThrow(
            '\InvalidArgumentException'
        )->duringInstantiation();

    }

    function it_fails_with_an_invalid_api_key(){

        $this->beConstructedWith( self::SERVICE_ID, 'invalid-api-key' );

        $this->shouldThrow(
           '\InvalidArgumentException'
        )->duringInstantiation();

    }

    function it_fails_when_the_api_keys_do_not_match(){

        // Create using a different API key. When checked, we use self::API_KEY, thus the token should be invalid.

        $this->beConstructedWith( self::SERVICE_ID, 'f47ac10b-58cc-4372-a567-0e02b2c3d479' );

        $this->createToken()->shouldBeInvalidJWSToken();

    }

    public function getMatchers() : array
    {
        return [
            'beValidJWSToken' => function ($token) {

                /*
                 * Ensure the token is:
                 *  - a string
                 *  - that can be decoded as a JWT,
                 *  - validates against the API key
                 *  - and contains the expected claims.
                 */

                // Token must be a string.
                if( !is_string($token) ){
                    throw new FailureException(sprintf(
                        'Token must be a string. '. gettype($token) . ' found.'
                    ));
                }

                //---

                try {

                    JWT::$leeway = 5; // $leeway in seconds
                    $key = new Key(self::API_KEY, 'HS256');
                    $decoded = JWT::decode($token, $key);

                } catch( SignatureInvalidException $e ){

                    throw new FailureException($e->getMessage());

                }

                //---

                // iss must match our service id.
                if( $decoded->iss !== self::SERVICE_ID ){
                    throw new FailureException(sprintf(
                        sprintf("Unable to validate iss claim. '%s' expected, but '%s' found.", $decoded->iss, self::SERVICE_ID)
                    ));
                }

                //---

                $time = time();

                // iat must be a recent timestamp.
                if( $decoded->iat < ( $time - 10 ) || $decoded->iat > ( $time + 10 ) ){
                    throw new FailureException(sprintf(
                        sprintf("Unable to validate iat claim. %d expected to be within ten seconds of %d.", $decoded->iat, $time)
                    ));
                }

                return true;

            },
            'beInvalidJWSToken' => function ($token) {

                /**
                 * Returns true when the JWT throws a SignatureInvalidException.
                 */

                try {

                    JWT::$leeway = 5; // $leeway in seconds
                    $key = new Key(self::API_KEY, 'HS256');
                    JWT::decode($token, $key);

                } catch( SignatureInvalidException $e ){

                    return true;

                }

                throw new FailureException('Invalid token expected, but the one passed appears valid.');

            }
        ];
    }

}
