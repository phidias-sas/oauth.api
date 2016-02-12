<?php
namespace Phidias\Oauth;

class Controller
{
    /**
     * Authentication function
     * Validates and parses incoming token, and returns its payload
     *
     * To be used as a dispatcher authentication, like this:
     *
     * // Authenticate token in every request
     * Server::resource("*", [
     *     "any" => [
     *         "authentication" => "Phidias\Oauth\Controller::authenticate({request})"
     *     ]
     * ]);
     *
     */
    public static function authenticate($request)
    {
        if ($request->hasHeader("authorization")) {
            list($authorizationMethod, $authorizationCredentials) = explode(" ", $request->getHeader("authorization")[0], 2);
            switch (strtolower($authorizationMethod)) {
                case "bearer":
                    Token::load( trim($authorizationCredentials) );
                    return Token::getPayload();
                break;
            }
        }
    }

    /**
     * POST requests to /oauth/token require the user to provide
     * username and password.  Custom validation functions may be defined
     * to validate this credentials, like this:
     *
     * Phidias\Oauth\Controller::addCredentialsValidator(function($username, $password) {
     *      //voo doo
     *      return $payload;
     * })
     *
     * It must return the data to be used as the token payload, or throw and exception on failure
     */

    private static $credentialValidators;

    public static function addCredentialsValidator(Callable $callback)
    {
        if (self::$credentialValidators === null) {
            self::$credentialValidators = [];
        }

        self::$credentialValidators[] = $callback;
    }


    private static $emailValidators;

    public static function addEmailValidator(Callable $callback)
    {
        if (self::$emailValidators === null) {
            self::$emailValidators = [];
        }

        self::$emailValidators[] = $callback;
    }



    /**
     * Dispatch POST /oauth/token
     */
    public function token($request, $input)
    {
        $postdata = (array)$input;

        if (!isset($postdata["grant_type"])) {
            throw new Exception\InvalidRequest("no grant_type specified");
        }

        switch ($postdata["grant_type"]) {

            case "client_credentials":
                $token = self::getTokenFromClientCredentials($request);
            break;

            // 2DO: implement (!!!)
            //case "authorization_code":
            //    $token = self::getTokenFromAuthorizationCode($request);
            //break;

            // Temporary, since this does NOT follow the OAuth standard
            case "google":
                $token = self::getTokenFromGoogleToken($postdata);
            break;

            default:
                throw new Exception\InvalidRequest("unknown grant type");
            break;

        }

        return $token;
    }

    /**
     * Dispatch POST /oauth/authorization
     */
    public function authorization($incoming)
    {
        //See http://tools.ietf.org/html/rfc6749#section-4.1.1
        $responseType = $incoming["response_type"]; //REQUIRED
        $clientId     = $incoming["client_id"];     //REQUIRED
        $redirectUri  = $incoming["redirect_uri"];  //OPTIONAL
        $scope        = $incoming["scope"];         //OPTIONAL
        $state        = $incoming["state"];         //OPTIONAL


        // 2DO: implement (!!!)
    }


    private static function getTokenFromGoogleToken($postdata)
    {
        if (!isset($postdata["token"])) {
            throw new Exception\InvalidRequest("no token specified");
        }

        $googleToken = $postdata["token"];
        $userInfoUrl = "https://www.googleapis.com/oauth2/v1/userinfo?access_token=".$googleToken;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        $tokenData = json_decode($result);

        if (!isset($tokenData->email)) {
            throw new Exception\InvalidRequest("could not obtain email from google token");
        }

        $payload = self::validateEmail($tokenData->email);

        return new Token("bearer", $payload);

    }


    private static function getTokenFromClientCredentials($request)
    {
        if (!$request->hasHeader("authorization")) {
            throw new Exception\InvalidRequest("no authorization header set");
        }

        list($authorizationMethod, $authorizationCredentials) = explode(" ", $request->getHeader("authorization")[0]);

        if (strtolower($authorizationMethod) != "basic") {
            throw new Exception\InvalidRequest("authorization header must contain basic header credentials");
        }

        $parts = explode(":", base64_decode(trim($authorizationCredentials)));

        if (count($parts) != 2) {
            throw new Exception\InvalidRequest("malformed credentials header");
        }

        list($username, $password) = $parts;

        $payload = self::validateClientCredentials($username, $password);

        return new Token("bearer", $payload);
    }


    private static function validateClientCredentials($username, $password)
    {
        if (is_array(self::$credentialValidators)) {
            foreach (self::$credentialValidators as $validator) {

                $payload = call_user_func($validator, $username, $password);

                if ($payload !== false) {
                    return $payload;
                }
            }
        }

        throw new Exception\InvalidCredentials;
    }

    private static function validateEmail($email)
    {
        if (is_array(self::$emailValidators)) {
            foreach (self::$emailValidators as $validator) {

                $payload = call_user_func($validator, $email);

                if ($payload !== false) {
                    return $payload;
                }
            }
        }

        throw new Exception\InvalidCredentials;
    }

}