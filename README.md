## Phidias OAuth endpoints

### Installation:
`composer require phidias/oauth.api`

### Configuration:

```
use Phidias\Oauth\Authentication;

Authentication::addCredentialsValidator(function ($username, $password) {

    // do your voodoo

    return $boolCredentialsAreValid;

});

```

### Usage:


```
use Phidias\Api\Server;

// Add OAuth endpoints to your API
Server::import("vendor/phidias/oauth.api");


```
