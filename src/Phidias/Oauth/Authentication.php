<?php
namespace Phidias\Oauth;

use JWT;

class Authentication
{
    private static $secret;
    private static $payload;
    private static $credentialValidators;

    public static function addCredentialsValidator(Callable $callback)
    {
        if (self::$credentialValidators === null) {
            self::$credentialValidators = [];
        }

        self::$credentialValidators[] = $callback;
    }

    public static function setSecret($secret)
    {
        self::$secret = $secret;
    }

    public static function authenticate($request)
    {
        if ($request->hasHeader("authorization")) {

            list($authorizationMethod, $authorizationCredentials) = explode(" ", $request->getHeader("authorization")[0]);

            switch (strtolower($authorizationMethod)) {

                case "basic":
                    self::validateClientCredentials( trim($authorizationCredentials) );
                break;

                case "bearer":
                    self::validateToken( trim($authorizationCredentials) );
                break;

            }

        }

        return true;
    }

    public static function getToken()
    {
        $token                  = new Token;
        $token->token_type      = "bearer";
        $token->access_token    = JWT::encode(self::$payload, self::$secret);
        //$token->expires_in    = "???";
        //$token->scope         = "???";
        //$token->refresh_token = "???";

        return $token;
    }

    public static function validateToken($token)
    {
        try {
            self::$payload = JWT::decode($token, self::$secret);
        } catch (\Exception $e) {
            throw new Exception\InvalidToken;
        }
    }

    public static function validateClientCredentials($incomingCredentials)
    {
        $parts = explode(":", base64_decode($incomingCredentials));

        if (count($parts) != 2) {
            throw new Exception\InvalidCredentials($incomingCredentials);
        }

        list($username, $password) = $parts;

        if (is_array(self::$credentialValidators)) {
            foreach (self::$credentialValidators as $validator) {
                $payload = call_user_func($validator, $username, $password);

                if ($payload !== false) {
                    self::$payload = $payload;
                    return;
                }
            }
        }

        throw new Exception\InvalidCredentials($incomingCredentials);

    }

}
