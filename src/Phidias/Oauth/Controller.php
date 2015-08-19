<?php
namespace Phidias\Oauth;

class Controller
{
    public static function validate($request)
    {
        $data = (array)$request->getParsedBody();

        if (!isset($data["grant_type"])) {
            return ["grant_type" => "no grant_type specified"];
        }

        if ( !in_array($data["grant_type"], ["client_credentials", "authorization_code"]) ) {
            return ["grant_type" => "unknown grant type"];
        }
    }

    public function token($incoming)
    {
        switch ($incoming["grant_type"]) {

            case "client_credentials":

                return Authentication::getToken();

            break;


            case "authorization_code":

                $clientId     = "huh";  //$incoming["client_id"]  or basic auth username
                $clientSecret = "huh";  //$incoming["client_secret"] or basic auth password
                $code         = $incoming["code"];

                // 2DO: implement (!!!)

            break;


        }

    }

    public function authorization($incoming)
    {
        //See http://tools.ietf.org/html/rfc6749#section-4.1.1
        $responseType = $incoming["response_type"]; //REQUIRED
        $clientId     = $incoming["client_id"];     //REQUIRED
        $redirectUri  = $incoming["redirect_uri"];  //OPTIONAL
        $scope        = $incoming["scope"];         //OPTIONAL
        $state        = $incoming["state"];         //OPTIONAL


        switch ($responseType) {

            case "code":
                //$code  = Authentication::generateAuthorizationCode($clientId);
                //$state = "huh";
                //redirect to $redirectUri?code=$code&state=$state

            break;

            case "token":
            break;

        }

    }

}