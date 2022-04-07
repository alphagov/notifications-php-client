<?php
namespace Alphagov\Notifications\Authentication;

use Firebase\JWT\JWT;

use InvalidArgumentException;

/**
 * Class for generating JSON Web Token compatible with GOV.UK Notify.
 *
 * Makes use of PHP-JWT: https://github.com/firebase/php-jwt
 *
 * Class JsonWebToken
 * @package Alphagov\Notifications\Authentication
 */
class JsonWebToken implements JWTAuthenticationInterface {

    /**
     * @var string
     */
    protected $serviceId;

    /**
     * @var string
     */
    protected $key;


    /**
     * Instantiates a new JSON Web Token object.
     *
     * @param string $serviceId
     * @param string $key
     */
    public function __construct( $serviceId, $key ){

        if( !$this->isValidUUIDv4( $serviceId ) ){
            throw new InvalidArgumentException( 'Invalid serviceId passed: ' . var_export($serviceId, true) );
        }

        if( !$this->isValidUUIDv4( $key ) ){
            throw new InvalidArgumentException( 'Invalid apiKey passed: ' . var_export($key, true) );
        }

        $this->serviceId = $serviceId;
        $this->key = $key;

    }

    /**
     * Checks the passed string is a valid Version 4 UUID.
     *
     * See: https://en.wikipedia.org/wiki/Universally_unique_identifier#Version_4_.28random.29
     *
     * @param string $string
     * @return bool TRUE iff a valid Version 4 UUID.
     */
    private function isValidUUIDv4( $string ){

        return is_string($string) && 1 === preg_match(
            '/[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}/i',
            $string
        );

    }

    /**
     * Generate a JSON Web Token.
     *
     * @return string The generated token
     */
    public function createToken(){

        $claims = $this->generateClaims();

        return JWT::encode( $claims, $this->key, 'HS256' );

    }

    /**
     * Prepare the required Notify claims.
     *
     * @return array
     */
    protected function generateClaims(){

        $claims = array(
            "iss" => $this->serviceId,
            "iat" => time(),
        );

        return $claims;

    }

}
