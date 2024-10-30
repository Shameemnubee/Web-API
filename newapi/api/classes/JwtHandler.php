<?php
// Include the Composer autoload file to use the JWT library
require './vendor/autoload.php';

// Import the JWT class from the Firebase\JWT namespace
use Firebase\JWT\JWT;

// Create a class called JwtHandler to handle JWT encoding and decoding
class JwtHandler
{
    protected $jwt_secrect; // Secret key for JWT
    protected $token;       // Generated JWT
    protected $issuedAt;    // Timestamp when the object is created
    protected $expire;      // Token expiration time
    protected $jwt;         // JWT token

    public function __construct()
    {
        // Set the default time-zone to Asia/Kolkata
        date_default_timezone_set('Asia/Kolkata');
        
        // Get the current timestamp (issuedAt) when the object is created
        $this->issuedAt = time();

        // Token Validity (3600 seconds = 1 hour) from the issued time
        $this->expire = $this->issuedAt + 3600;

        // Set your secret or signature for JWT
        $this->jwt_secrect = "this_is_my_secret";
    }

    // Encode data and create a JWT token
    public function jwtEncodeData($iss, $data)
    {
        // Define the token payload
        $this->token = array(
            // Issuer (who issued the token)
            "iss" => $iss,
            // Audience (usually the same as issuer)
            "aud" => $iss,
            // Issued at timestamp (when the token was issued)
            "iat" => $this->issuedAt,
            // Token expiration time
            "exp" => $this->expire,
            // Payload data (e.g., user information)
            "data" => $data
        );

        // Encode the payload and create the JWT using HS256 algorithm
        $this->jwt = JWT::encode($this->token, $this->jwt_secrect, 'HS256');

        // Return the generated JWT
        return $this->jwt;
    }

    // Decode a JWT token and return the decoded data
    public function jwtDecodeData($jwt_token)
    {
        try {
            // Decode the JWT using the same secret and algorithm (HS256)
            $decode = JWT::decode($jwt_token, $this->jwt_secrect, array('HS256'));
            
            // Return the decoded data as an associative array
            return [
                "data" => $decode->data
            ];
        } catch (Exception $e) {
            // If decoding fails, return an error message
            return [
                "message" => $e->getMessage()
            ];
        }
    }
}
?>
