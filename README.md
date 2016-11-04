# PassBy[ME] Mobile ID client

This library provides you with functionality to handle PassBy[ME] REST API methods.

For further information on PassBy[ME] please visit: [www.passbyme.com](https://www.passbyme.com) and sign up for a free account. 

## Requirements
- PHP 5.5.12+
- libcurl

## Installation
PassBy[ME] client is available via Composer/Packagist.

In your `composer.json`:

``` json
{
    "require": {
        "microsec/passbyme2fa-client-php": "[version number]"
    }
}
```
From the Command Line:
```
composer require microsec/passbyme2fa-client-php
```

Alternatively, copy the contents of the "PassByME" folder into somewhere that's in your PHP `include_path` settings or just click the 'zip' button at the top of the page in GitHub to download the archive.

## Require passbyme-client in your code
If you use composer just load the composer autoloader:
- `require_once 'vendor/autoload.php';`

Without composer use the built in autoloader:
- `require '/path/to/PassByME/Autoloader.php';` 

## Get Started

You'll need a **PassBy[ME] account**, and a **certificate** to allow access to the PassBy[ME] API.

### 1. Login to Your Account
If you have not registered before [register](https://admin.passbyme.com/register/registration) a new organisation or [log in](https://admin.passbyme.com/register/login) to your administration interface.
Note that the registration process requires an Android, Windows Phone or iOS device with a PassBy[ME] application installed.

### 2. Download Certificates
Log in to your PassBy[ME] administration interface to download the required certificate files.

##### Download application certificate: 
Create a new application by hitting the "New application" button under "Applications" menu.
The created applications (along with its Application Id) will appear in a table view. Click on the "lock" icon to download your application certificate file in PEM format. This certificate is required to **access API messaging** functions.

##### Download organisation certificate:
Hit the "Key Details" button under "Account Settings" menu and download the organisation certificate in PEM format. This certificate is required to **access API management** functions.

The PEM file is protected with a passphrase, which is printed on the
administration website. Note that you will need the passphrase when you set the certificate file.

In the **configuration library** section you can read how to setup the PEM file properly to communicate with the PassBy[ME] system.
We suggest you to read the available [Management API User Guides](https://www.passbyme.com/static/documentation/PassByME_Management_API_Documentation.pdf) before you continue with the integration.

### 3. Configure the client
To start to communicate with the PassBy[ME] API first you have to configure the client. There is a Config.php class for that purpose. You can use directly that class to store your configuration or set your configuration settings in your own code like below.
```
<?php
use PassByME\TwoFactor\Config;

Config::set('aut_api_url', 'https://auth-sp.passbyme.com/frontend');
Config::set('curl_debug', true);
...
```
The most important configuration settings you have to set properly are the following:
- **auth_cert :** Path to application certificate file. *(Required for messaging.)*
- **auth_pwd :** Application certificate file password. 
- **mng_cert :** Path to organisation certificate file. *(Required for management.)*
- **mng_pwd :** Management certificate file password.
- **auth_url :** The address of the PassBy[ME] authentication service to use. By default, the SDK will connect to the https://auth-sp.passbyme.com/frontend URL.
- **mng_url :** The address of the PassBy[ME] management service to use. By default, the SDK will connect to the https://api.passbyme.com/register URL.
- **ca_cert:** The CA certificate path to create the connection with the API. The CA certificate can be found in the ca_bundle folder.

Other optional configuration parameters:
- **curl_timeout:** The maximum number of seconds to allow cURL functions to execute. The default value is 30 seconds.
- **curl_maxredirs:** The maximum amount of HTTP redirection to follow. The default value is 10.
- **curl_connecttimeout:** The number of seconds to wait while trying to connect. The default value is 120 seconds.
- **curl_useragent:** The contents of the "User-Agent:" header to be used in an HTTP request. The default value is empty.
- **curl_debug:** Writes cURL output information to log files. Optional values are true or false. The default value is false.
- **curl_proxytype:** cURL Proxy type. The default value is HTTP.
- **curl_proxy:** The HTTP proxy to tunnel requests through.
- **curl_proxyport:** The port number of the proxy to connect to.
- **curl_proxyuserpwd:** A username and password formatted as "[username]:[password]" to use for the connection to the proxy.

## Logging as you like
By default, logging is disabled. You can enable logging by creating your own logger class, which implements the ILogger interface.
The interface offers you the following log levels:
- info
- warning
- error
- debug

#### Example
```php
<?php
namespace YourLogger;

class Logger implements PassByME\Log\ILogger
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
$pbm = new PassByME\Methods\Messaging($logger);
```
## Catching Errors
On failure the application throws a **PBMErrorException**.

## Functions
The following section contains all the available functions description in details.

### Messaging
PassBy[ME] can send three different type of message to registered users devices.
These message types are the following:
#### *authorizationMessage*
``` php
$pbm->authorizationMessage([userIdentifier], [subject], [body], [availability]);
```

#### *generalMessage* 
``` php
$pbm->generalMessage([userIdentifier], [subject], [body], [availability]);
```


#### *eSignMessage*
``` php
$pbm->eSignMessage([userIdentifier], [subject], [body], [availability]);
``` 

All three functions has the following parameters:

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| userIdentifier | Required | Array | The PassBy[ME] identifiers of the users to send message to. |
| subject | Required | String | The message title to sent to a PassBy[ME] ready device. |
| body | Required | String | The message content to sent to a PassBy[ME] ready device. |
| availability | Required | Integer | The length of the second factor authentication session in seconds. Default value is 300 seconds. |
       
On success, a JSON object is returned, which contains various information about the ongoing process.
##### Example JSON object
```json
{
    "messageId":"@pbmcore1-1.3.6.1.4.1.21528.3.3.2.9045.2.10111-lUzWHbKe",
    "expirationDate":"2016-08-15T09:11:32.585Z",
    "recipients":[
        {
            "userId":"john.doe@passbyme.com",
            "status":"DOWNLOADED"
        }
    ],
    "secureId":"5cRFBm"
}
```

All messaging responses contains a specific identifier called **messageId**.

##### Example messageId: 
```
@pbmcore1-1.3.6.1.4.1.21528.3.3.2.9045.2.10111-vaPiky4b
```

#### *trackMessage*
```php
$pbm->trackMessage([messageId]);
```
The function requires a **messageId** parameter. The **messageId** can be obtained by calling one of the three messaging methods mentioned above.
   
#### *cancelMessage*
```php
$pbm->cancelMessage([messageId]);
```
This function cancels an existing authentication session, identified by the given messageId.

### Management

PassBy[ME] administration methods are available through the Management class.
```php
<?php
$logger = new YourLogger\Logger();
$pbm = new PassByME\Methods\Management($logger);
```

#### *createUser*
```php
$pbm->createUser([userId], [email], [fullName], [phoneNumber]);
```

Creates a new PassBy[ME] user.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| userId | Required | String | The PassBy[ME] ID of a user. |
| email | Required | String | Email address of the user. |
| fullName | Optional | String | Full name of the user. |
| phoneNumber | Optional | String | The phone number of the user. |
#### *getListOfUsers*
```php
$pbm->getListOfUsers();
```
Returns the list of PassBy[ME] users.
#### *getUsersNumber*
```php
$pbm->getUsersNumber();
```
Get the number of users in the account.
#### *getUser*
```php
$pbm->getUser([oid]);
```

Find the user with the given OID.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| oid | Required | String | The OID of the user. |

#### *deleteUser*
```php
$pbm->deleteUser([oid]);
```
Deletes the user with the given OID.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| oid | Required | String | The OID of the user to be deleted. |

#### *modifyUser*
```php
$pbm->modifyUser([oid], [modifiedUserObj]);
```
Modify the user with the given OID.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| oid | Required | String | The OID of the selected user. |
| modifiedUserObj | Required | Object | A "UserData" object obtained from getUser function. |

#### *createEnrollment*
```php
$pbm->createEnrollment([oid]);
```
Create a new enrollment for the given user.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| oid | Required | String | The OID of the user. |

#### *getListOfEnrollments*
```php
$pbm->getListOfEnrollments([oid]);
```
Returns the active enrollments of the given user.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| oid | Required | String | The OID of the user. |

#### *downloadEnrollmentPdf*
```php
$pbm->downloadEnrollmentPdf([oid], [enrollmentId]);
```
Downloads the enrollment in pdf format.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| oid | Required | String | The OID of the user. |
| enrollmentId | Required | String | The ID of the enrollment. |

#### *sendEnrollmentInEmail*
```php
$pbm->sendEnrollmentInEmail([oid], [enrollmentId]);
```
Send the specified enrollment pdf document to the user via e-mail.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| oid | Required | String | The OID of the user. |
| enrollmentId | Required | String | The ID of the enrollment. |

#### *deleteEnrollment*
```php
$pbm->deleteEnrollment([oid], [enrollmentId]);
```
Deletes the enrollment specified by the enrollmentId of the user specified by the OID.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| oid | Required | String | The OID of the owner of the enrollment. |
| enrollmentId | Required | String | The enrollmentId of the enrollment to be deleted. |

#### *createAlias*
```php
$pbm->createAlias([oid], [alias]);
```
Adds a new userId (alias) for the specified PassBy[ME] user.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| oid | Required | String | The OID of the chosen user, who receives the alias. |
| alias | Required | String | The PassBy[ME] ID of a user. |

#### *getListOfAliases*
```php
$pbm->getListOfAliases([oid]);
```
Returns the list of userIds (aliases) of the specified PassBy[ME] user.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| oid | Required | String | The OID of the user. |

#### *getUserByAlias*
```php
$pbm->getUserByAlias([userId]);
```
Find the user with the given userId.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| userId | Required | String | One of the aliases of the user. |

#### *deleteAlias*
```php
$pbm->deleteAlias([oid], [userId]);
```
Deletes the specified userId (alias) of the specified PassBy[ME] user.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| oid | Required | String | The userId to be deleted. |
| userId | Required | String | The oid of the PassBy[ME] user who owns the userId to be deleted. |

#### *getListOfAdministrators*
```php
$pbm->getListOfAdministrators();
```
Returns the list of administrators.

#### *createUserFromAdmin*
```php
$pbm->createUserFromAdmin([userId], [adminId]);
```
Add an existing administrator as a new PassBy[ME] user.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| userId | Required | String | The user will be created with this PassBy[ME] ID. |
| adminId | Required | String | Same as the login name (email address) |

#### *createInvitation*
```php
$pbm->createInvitation();
```
Create a new invitation. Finishing the invitation process a new administrator will be created.

#### *getListOfInvitations*
```php
$pbm->getListOfInvitations();
```
Returns the list of active invitations.

#### *createAdminEnrollment*
```php
$pbm->createAdminEnrollment([userId]);
```
Create a new enrollment for the given administrator.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| userId | Required | String | The userId of the administrator. |

#### *getAdminEnrollments*
```php
$pbm->getAdminEnrollments([userId]);
```
Returns the active enrollments of the given administrator.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| userId | Required | String | The userId of the administrator. |

#### *downloadAdminEnrollmentPdf*
```php
$pbm->downloadAdminEnrollmentPdf([userId], [enrollmentId]);
```
Downloads the enrollment in pdf format.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| userId | Required | String | The userId of the administrator. |
| enrollmentId | Required | String | The id of the enrollment. |

#### *sendAdminEnrollmentInEmail*
```php
$pbm->sendAdminEnrollmentInEmail([userId], [enrollmentId]);
```
Send the specified enrollment pdf document to the owner administrator via e-mail.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| userId | Required | String | The userId of the administrator. |
| enrollmentId | Required | String | The id of the enrollment. |

#### *deleteAdminEnrollment*
```php
$pbm->deleteAdminEnrollment([userId], [enrollmentId]);
```
Deletes the enrollment specified by the enrollmentId of the administrator specified by the userId.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| userId | Required | String | The userId of the owner of the enrollment. |
| enrollmentId | Required | String | The enrollmentId of the enrollment to be deleted. |

#### *createApplication*
```php
$pbm->createApplication([name]);
```
Creates a new Application registration.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| name | Required | String | Name of the application. |

#### *getListOfApplication*
```php
$pbm->getListOfApplication();
```
Returns the list of applications registered in the PassBy[ME] system.

#### *getApplication*
```php
$pbm->getApplication([appId]);
```
Find the application with the given application identifier.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| appId | Required | String | The id of the application in the PassBy[ME] system. |

#### *deleteApplication*
```php
$pbm->deleteApplication([appId]);
```
Deletes the application from the PassBy[ME] system with the given application identifier.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| appId | Required | String | The application identifier of the application to be deleted. |

#### *modifyApplication*
```php
$pbm->modifyApplication([appId], [name]);
```
Modify the application with the given application identifier.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| appId | Required | String | The application identifier of the application to be modified. |
| name | Required | String | The new name of the application. |

#### *getListOfUsersDevices*
```php
$pbm->getListOfUsersDevices([appId], [name]);
```
Returns all devices.

#### *getUserDevices*
```php
$pbm->getUserDevices([oid]);
```
Returns the devices of the given user.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| oid | Required | String | The OID of the user. |

#### *getListOfAdminDevices*
```php
$pbm->getListOfAdminDevices([adminId]);
```
Returns the devices of the given administrator.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| adminId | Required | String | The id of the administrator. |

#### *sendDeactivationPassword*
```php
$pbm->sendDeactivationPassword([vendorId]);
```
Re-sends deactivation password via email.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| vendorId | Required | String | The vendorId of the device, which belongs to the deactivation password to resend. |

#### *deleteDevice*
```php
$pbm->deleteDevice([deactivationPassword]);
```
Delete user device.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| deactivationPassword | Required | String | The suspension password of the owner of the device. |

#### *getOrganization*
```php
$pbm->getOrganization();
```
Returns organization details.

#### *updateOrganization*
```php
$pbm->updateOrganization([modifiedOrgObj]);
```
Updates organization details.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| modifiedOrgObj | Required | String | Mocked object from getOrganization function request. |

#### *getAccountLimitations*
```php
$pbm->getAccountLimitations();
```
Get the current account limitations (pricing) of the organization.

#### *activityLog*
```php
$pbm->activityLog([search], [start], [length]);
```
Returns list of second factor authentication log entries.

| Parameter | Mandatory | Type | Description |
| --------- | --------- | ---- | ----------- |
| search | Required | String | The value to be used for filtering records. |
| start | Required | Integer | The number of records to skip from the result. |
| length | Required | Integer | The maximum number of records to be returned. |

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