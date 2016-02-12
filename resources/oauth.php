<?php return [

    "oauth/authorization" => [
        "post" => [
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
            "controller" => "Phidias\Oauth\Controller->token({request}, {input})",

            "catch" => [
                "Exception" => function($request, $response) {
                    $response->status(422);
                }
            ]
        ]
    ]

];
