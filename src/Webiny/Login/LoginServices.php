<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Login;

use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Rest\RestErrorException;

/**
 * LoginServices - optional implementation of RESTful services that provide the most essential login methods.
 *
 * @package Webiny\Login
 */
abstract class LoginServices
{
    use HttpTrait;

    /**
     * Returns the Login instance.
     *
     * @return Login
     */
    protected abstract function getLoginInstance();

    /**
     * Process login takes:
     * - username
     * - password
     * - authProvider (optional)
     * - deviceToken (optional - required only if 2ValidateDevice is turned on)
     *
     * Login returns authToken (and deviceToken) on successful login, otherwise RestError on error or invalid login.
     *
     * @return array
     * @throws RestErrorException
     * @rest.method post
     */
    public function processLogin()
    {
        $username = $this->httpRequest()->post('username');
        $password = $this->httpRequest()->post('password');
        $authProvider = $this->httpRequest()->post('authProvider', '');
        $deviceToken = $this->httpRequest()->post('deviceToken', '');

        if (!$username) {
            throw new RestErrorException('Login error', 'Username is required.', 99);
        }

        if (!$password) {
            throw new RestErrorException('Login error', 'Password is required.', 99);
        }

        // check if we should validate the device
        if ($this->getLoginInstance()->getConfig()->get('Login.ValidateDevice', false) == true && $deviceToken == '') {
            // we need to have device token
            throw new RestErrorException('Login error', 'The current device is not on the allowed list.', 5);
        }

        try {
            $this->getLoginInstance()->processLogin($username, $deviceToken, $authProvider);

            // if login is successful, return device and auth tokens
            $authToken = $this->getLoginInstance()->getAuthToken();
            return [
                'authToken'   => $authToken,
                'deviceToken' => $deviceToken
            ];

        } catch (LoginException $le) {
            throw new RestErrorException('Login error', $le->getMessage(), $le->getCode());
        } catch (\Exception $e) {
            throw new RestErrorException('Login error', $e->getMessage());
        }
    }

    /**
     * For the provided username, returns `deviceValidationToken`.
     *
     * @return array
     * @rest.method post
     */
    public function getDeviceValidationToken()
    {
        $username = $this->httpRequest()->post('username');
        $token = $this->getLoginInstance()->generateDeviceValidationToken($username);

        return ['deviceValidationToken' => $token];
    }

    /**
     * Validates if the provided `deviceValidationToken` for the given username.
     * Returns `deviceToken` if validation is successful, otherwise false.
     *
     * @return array
     * @throws LoginException
     * @throws RestErrorException
     * @rest.method post
     */
    public function validateDeviceValidationToken()
    {
        $username = $this->httpRequest()->post('username');
        $deviceValidationToken = $this->httpRequest()->post('deviceValidationToken');

        $result = $this->getLoginInstance()->validateDeviceConfirmationToken($username, $deviceValidationToken);
        if ($result) {
            return ['deviceToken' => $result];
        } else {
            throw new RestErrorException('Device validation error.',
                'The provided device validation token is invalid.');
        }
    }

    /**
     * Returns `accountActivationToken` for the provided username.
     *
     * @return array
     * @rest.method post
     */
    public function getAccountActivationToken()
    {
        $username = $this->httpRequest()->post('username');
        $token = $this->getLoginInstance()->getAccountConfirmationToken($username);

        return ['accountActivationToken' => $token];
    }

    /**
     * Sets the activated flag to true if the `accountActivationToken` is valid for the provided username.
     *
     * @return array
     * @throws RestErrorException
     * @rest.method post
     */
    public function validateAccountActivationToken()
    {
        $username = $this->httpRequest()->post('username');
        $accountActivationToken = $this->httpRequest()->post('accountActivationToken');

        $result = $this->getLoginInstance()->validateAccountConfirmationToken($username, $accountActivationToken);
        if ($result) {
            return ['result' => 'success'];
        } else {
            throw new RestErrorException('Account activation error.', 'The provided token is not valid.');
        }
    }

    /**
     * Revokes the given `session` for the provided username.
     *
     * @rest.method post
     */
    public function logout()
    {
        $username = $this->httpRequest()->post('username');
        $session = $this->httpRequest()->post('authToken');

        $this->getLoginInstance()->revokeSessions($username, $session);

        return ['result' => 'success'];
    }

    /**
     * Generates a password reset token for the provided username.
     *
     * @return array
     */
    public function generateForgotPasswordResetToken()
    {
        $username = $this->httpRequest()->post('username');

        $token = $this->getLoginInstance()->generateDeviceValidationToken($username);
        return ['passwordResetToken' => $token];
    }

}