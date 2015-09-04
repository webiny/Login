Webiny Login
================

This is an application that provides additional control layer to the [Webiny Framework Security](https://github.com/Webiny/Security) component.
The application standardizes the login process and user stateless token storage, making it ideal for RESTful and mobile applications.

Some of the built-in features:
- 2 factor authentication
- sessions are stored in database and can be revoked at any point
- authorized devices are also stored in database and can be revoked at any point
- login whitelist and blacklist based on client IP
- rate limit control
- stateless login validation for RESTful application

## Sample config

```yaml
Login:
    SecurityFirewall: Admin
    2FactorAuth: true
    BlockThreshold: 6
    BlockTimelimit: 10
    SessionTtl: 30
    DeviceTtl: 90
    RateLimitBlacklist:
        - 123.123.123.123
    RateLimitWhitelist:
        - 127.0.0.1
        - 192.168.1.1
        - 10.0.2.2
```

- **SecurityFirewall**: defines which `Security.Firewall` to use for user authentication
- **2FactorAuth**: should the 2 factor auth be used or not
- **BlockThreshold**: after how many bad login attempts should the client be blocked from submitting any new login requests (client is identified as username+ip combination)
- **BlockTimelimit**: for how many minutes should the client be blocked from submitting any additional login attempts
- **SessionTtl**: once session has been issued, for how long should it be considered valid
- **DeviceTtl**: how long should the device session be valid (used only if 2FactorAuth is turned on)
- **RateLimitBlacklist**: list of IPs that are permanently blocked from submitting login requests
- **RateLimitWhitelist**: list of IPs that are excluded from the rate limit control

## Setup

The Login app requires following Webiny Framework components:
- [Entity](https://github.com/Webiny/Entit)
- [Http](https://github.com/Webiny/Http)
- [Mongo](https://github.com/Webiny/Mongo)
- [Security](https://github.com/Webiny/Security)
- [Rest](https://github.com/Webiny/Rest) (optional - only if login RESTful service is used)

#### Example setup:

```php
\Webiny\Component\Security\Security::setConfig('./securityConfig.yaml');
\Webiny\Component\Mongo\Mongo::setConfig('./mongoConfig.yaml');
\Webiny\Component\Entity\Entity::setConfig('./entityConfig.yaml');

$security = \Webiny\Component\Security\Security::getInstance();
$loginConfig = \Webiny\Component\Config\Config::getInstance()->yaml('./loginConfig.yaml');

$login = new \Webiny\Login\Login($security, $loginConfig);
```

Once you have the login instance, you can access the methods inside the class directly:

```php
// check if we have the auth cookie and device cookie
$authCookie = \Webiny\Component\Http\Cookie::getInstance()->get('auth-token');
$deviceToken = \Webiny\Component\Http\Cookie::getInstance()->get('device-token');

if ($authCookie && $deviceToken) {
    try {
        $user = $login->getUser($authCookie, $deviceToken);
    } catch (\Webiny\Login\LoginException $le) {
        
    } catch (\Exception $e) {
        
    }
}else{
    // process login
    try {
        $login->processLogin($username, $deviceToken, $authProvider);
    
        // if login is successful, return device and auth tokens
        $authToken = $login->getAuthToken();
        return [
            'authToken'   => $authToken,
            'deviceToken' => $deviceToken
        ];
    } catch (LoginException $le) {
        $errorMsg = $le->getMessage();
    } catch (\Exception $e) {
        return $e;
    }
}
```

#### Security setup

Note that the Security component needs to implement `Stateless` token storage:

```yaml
Security:
    Tokens:
        Stateless:
            StorageDriver: \Webiny\Component\Security\Token\Storage\Stateless # storage driver needs to be set to stateless
            SecurityKey: SecretKey
    Firewall:
        Admin:
            Token: Stateless
```

## Login services

You can use the Login app as a RESTful service by extending the `\Webiny\Login\LoginServices` abstract class and implementing 
it into `Webiny Framework Rest` component. (view the `app/services.php` folder for sample implementation)

### POST `processLogin`

This method processes the login request and returns either a login error, or in case of a success, `authToken` and `deviceToken`.

The method takes the following parameters via POST:
- username
- password
- authProvider (optional - defines the name of auth provider inside `Security.Firewall` that should be used to process the request)
- deviceToken (optional - required only if 2FactorAuth is turned on)

Login error codes:

- 1. Rate limit reached.
- 2. User account is blocked.
- 3. Invalid credentials.
- 4. User hasn't confirmed his account.
- 5. The current device is not on the allowed list.
- 99. Either username or password is missing.


### POST `getDeviceValidationToken`

For the provided username, returns `deviceValidationToken`.

The device validation token is something that can be emailed or sent to the user via SMS or some other form of communication.

The method takes the following parameters via POST:
- username


### POST `validateDeviceValidationToken`

Validates the provided `deviceValidationToken` for the given username. If the token matches, `deviceToken` is returned.
This device token needs to be provided to the `processLogin` method in order to pass the 2FactorAuth.

The method takes the following parameters via POST:
- username
- deviceValidationToken


### POST `getAccountActivationToken`

In case users account is not activated, you need to request an activation token.
Usually this token is then emailed to the user via an activation link.

The method takes the following parameters via POST:
- username


### POST `validateAccountActivationToken`

Method that validates the provided activation token and either returns a success message, or an error that the token in not valid.

The method takes the following parameters via POST:
- username
- accountActivationToken


### POST `logout`

Invalidates the provided auth token for the given user.

The method takes the following parameters via POST:
- username
- authToken (the auth token returned by processLogin)


### POST `generateForgotPasswordResetToken`

Generates a forgot password link for the given username.

The method takes the following parameters via POST:
- username


## What doesn't it do

The Login app doesn't: 
- store any cookies or sessions, so all `remember me` features need to be done on your end
- it doesn't need to know about your users passwords, this is done via the `Security` class
- doesn't email any links like forgot password, activate account, 2FA tokens - login only generates the tokens, the delivery is up to you
- doesn't do any authorization, only authentication
- doesn't provide any visuals, only a class and a RESTful service