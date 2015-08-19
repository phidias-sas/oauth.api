<?php return [

    "oauth/authorization" => [
        "post" => [
            "controller" => "Phidias\Oauth\Controller->authorization({request.data})"
        ]
    ],

    "oauth/token" => [
        "post" => [
            "validation" => "Phidias\Oauth\Controller::validate({request})",
            "controller" => "Phidias\Oauth\Controller->token({request.data})"
        ]
    ]

];
