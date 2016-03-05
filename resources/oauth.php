<?php return [

    "oauth/authorization" => [
        "post" => [

            "validation" => function($input) {

                //See http://tools.ietf.org/html/rfc6749#section-4.1.1
                $requiredKeys = [
                    "response_type",
                    "client_id",
                    "redirect_uri",
                    "scope",
                    "state"
                ];

                $errors = [];

                foreach ($requiredKeys as $key) {
                    if (!isset($input->$key)) {
                        $errors[$key] = "$key is required";
                    }
                }

                return $errors;

            },

            "controller" => "Phidias\Oauth\Controller->authorization({request})",

            "catch" => [
                "Exception" => function($request, $response) {
                    $response->status(422);
                }
            ]
        ]
    ],

    "oauth/token" => [
        "post" => [

            "validation" => function($input) {
                if (!isset($input->grant_type)) {
                    return "no grant_type specified";
                }
            },

            "controller" => "Phidias\Oauth\Controller->token({request}, {input})",

            "handler" => [
                "Exception" => function($request, $response, $exception) {
                    $response->status(422);
                    return $exception->getMessage();
                }
            ]
        ]
    ]

];
