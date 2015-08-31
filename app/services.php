<?php
require_once './bootstrap.php';

// cors
header("Access-Control-Allow-Origin: *");


// define the login service class
class Login extends \Webiny\Login\LoginServices
{

    private static $login;

    static public function setLoginInstance(\Webiny\Login\Login $login)
    {
        self::$login = $login;
    }

    /**
     * @return \Webiny\Login\Login
     */
    protected function getLoginInstance()
    {
        return self::$login;
    }
}

Login::setLoginInstance($login);

// load rest
\Webiny\Component\Rest\Rest::setConfig('./restConfig.yaml');

try {
    $rest = \Webiny\Component\Rest\Rest::initRest('LoginApi');
    if ($rest) {
        $rest->processRequest()->sendOutput();
    }
} catch (RestException $e) {
    // handle the exception
    die(print_r($e));
}