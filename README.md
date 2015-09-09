# PassBy[ME] two factor authentication client

This library provides you with functionality to handle PassBy[ME] authentications.

For further information on PassBy[ME] please visit: [www.passbyme.com](https://www.passbyme.com) and sign up for a free account. You can download our API documentation after login.

## Requirements
- PHP 5.3.2+
- libcurl
- OpenSSL (required for configuration only)

## Installation
Passby[ME]2FA client is available via Composer/Packagist.

In your `composer.json`:

``` json
{
    "require": {
        "microsec/passbyme2fa-client-php": "^1.0"
    }
}
```
From the Command Line:
```
composer require microsec/passbyme2fa-client-php
```

Alternatively, copy the contents of the PassByME folder into somewhere that's in your PHP `include_path` setting or just click the 'zip' button at the top of the page in GitHub to download the archive.

## Basic Usage

To use the PassBy[ME] authentication SDK first you have to acquire an account authentication PEM and its password. 

You can get these after registering at [www.passbyme.com](https://www.passbyme.com), by hitting the "Sign up for free" button. To complete the registration you will need an Android or iOS device with the PassBy[ME] application installed.

If you login after registration you can download the PEM from the "Application" menu. You can add new applications to your registration by hitting the "New application". The application (along with its Application Id) will appear in the table below.

To use the PEM file you have to export the private key from it.

To export the private key run the OpenSSL command below in a terminal window:
```
openssl rsa -in your.pem -out private.key
```

In the configuration section you can read how to setup the PEM and private key files properly.

We suggest you to read the available User Guides and API documentation before you continue with the integration. You can download these from the "Documentation" section of the administartion website after login.

### Require passbyme2fa-client in your code
If you use composer just load the composer autoloader:
- `require_once 'vendor/autoload.php';`

Without composer use the built in autoloader:
- `require '/path/to/PassByME/Autoloader.php';` 

### Configuration

To start communicate with the PassBy[ME] API first you have to configure the client. There is a Config.php class for that purpose. You can use directly that class to store your configuration or set your configuration settings in your own code like below.
```
<?php
use PassByME\TwoFactor\Config;

Config::set('aut_api_url', 'https://auth-sp.passbyme.com/frontend');
Config::set('curl_debug', true);
...
```
The most important configuration settings you have to set properly are the following:
- **auth_api_url :** The address of the PassBy[ME] service to use. By default the SDK will connect to the https://auth-sp.passbyme.com/frontend url.
- **ca_cert:** The CA certificate path to create the connection with the API. The CA certificate can be found in the ca_bundle folder.
- **cert_file:** Authentication certificate path to your .pem file.
- **cert_key:** Authentication certificate path to your .key file. (Exported from PEM.)

Other optional configuration parameters:
- **curl_timeout:** The maximum number of seconds to allow cURL functions to execute. Default value is 30 seconds
- **curl_maxredirs:** The maximum amount of HTTP redirections to follow. Default value is 10.
- **curl_connecttimeout:** The number of seconds to wait while trying to connect. Default value is 120 seconds.
- **curl_useragent:** The contents of the "User-Agent:" header to be used in a HTTP request. Default value is empty.
- **curl_debug:** Writes cURL output information to log files. Optional values are true or false. Default value is false.
- **curl_proxytype:** cURL Proxy type. Default value is HTTP.
- **curl_proxy:** The HTTP proxy to tunnel requests through.
- **curl_proxyport:** The port number of the proxy to connect to.
- **curl_proxyuserpwd:** A username and password formatted as "[username]:[password]" to use for the connection to the proxy.

### Logging 
By default logging is disabled. To get logging enabled you have to create your own logger class which implements the ILogger interface.
The interface offers you the following log levels:
- info
- warning
- error
- debug

#### Example
```php
<?php
namespace YourLogger;

class Logger implements PassByME\Authentication\ILogger
{
    public function __construct()
    {
        openlog('PassByME', LOG_PERROR, LOG_SYSLOG);
    }
    
    public function info($message)
    {
        syslog(LOG_INFO, $message);
    }
    ...
```

Then you can simply add your logging class to the PassBy[ME] client like this:
```php
<?php
$logger = new YourLogger\Logger();
$pbm = new PassByME\Authentication\Frontend($logger);
```

## Start the authentication
``` php
$pbm->authenticationRequest([userId], [message], [timeout]);
```
The function parameters are the following:
- **userId** (required): The PassBy[ME] Id of the user to authenticate.
- **message** (optional): An optional message to sent to a PassBy[ME] ready device. The default value is an empty string.
- **timeout** (optional): The length of the second factor authentication session in seconds.

The function throws an **exception** if any error occured or return a specific PassBy[ME] session identifier called **PBMSession**.

### Example PBMSession:
```
"node1-1.3.6.1.4.1.21528.3.3.3.148;PCq1m8"
```
The PBMSession contains the users unique identifier and a session identifier.

## Check the status of an authentication
```php
$pbm->authenticationProgress([pbmSession]);
```
This returns the status of an authentication, identified by the given PBMSession. The function throws an **exception** if any error occured or return the status of an authentication. On success the possible values of the authentication transaction can be the following:

| Status       |                                             |
|:-------------|:--------------------------------------------|
| PENDING      | The authentication process is still pending.|
| APPROVED     | The user approved the authentication.       |
| DENIED       | The user denied the authentication.         |
| TIMEOUT      | The authentication timed out.               |

The function requires a **PBMSession** parameter.
The **PBMSession** is obtained by calling the authenticationRequest method.

## Example
```php
$pbmSession = ...;

try {
    $pbm = new PassByME\Authentication\Frontend($logger);
    //Check request progression by PBMSession
    if ($pbmSession) {
        $pbm->authenticationProgress($pbmSession);
    } else {
        //Send request
        $pbm->authenticationRequest('development@passbyme.com', 'This is a test message.');
    }
} catch (Exception $exc) {
    ...
}
```

## Cancel the authentication
```php
$pbm->authenticationCancel([pbmSession]);
```
This function cancels an existing authentication session, identified by the given PBMSession. The function throws an **exception** if any error occured or takes no effect. The possible values of the error codes can be the following:

| Status                  |                                                   |
|:------------------------|:--------------------------------------------------|
| FORBIDDEN               | Your account is not authorized for this operation.|
| CERTIFICATE_REVOKED     | Client certificate has been revoked.              |
| MALFORMED_INPUT         | Input is not well formed.                         |

The function requires a **PBMSession** parameter.
The **PBMSession** is obtained by calling the authenticationRequest method.

## License

The MIT License

Copyright (c) 2015 Microsec Ltd. <development@passbyme.com>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.