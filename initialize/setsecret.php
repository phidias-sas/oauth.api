<?php

use Phidias\Utilities\Configuration;
use Phidias\Oauth\Authentication;

Authentication::setSecret(Configuration::get("phidias.oauth.secret"));