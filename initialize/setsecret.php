<?php

use Phidias\Oauth\Token;
use Phidias\Utilities\Configuration;

Token::setSecret(Configuration::get("phidias.oauth.secret"));