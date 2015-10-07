<?php return [

    "oauth/authorization" => [
        "post" => [
            "controller" => "Phidias\Oauth\Controller->authorization({request})"
        ]
    ],

    "oauth/token" => [
        "post" => [
            "controller" => "Phidias\Oauth\Controller->token({request})"
        ]
    ]

];
