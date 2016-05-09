<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Login;

/**
 * Class LoginSessionEntity
 * @package Webiny\Login
 */
class LoginMetaEntity extends \Webiny\Component\Entity\EntityAbstract
{
    protected static $entityCollection = 'LoginMeta';

    function __construct()
    {
        parent::__construct();
        $this->attr('username')->char();
        $this->attr('loginAttempts')->arr();
        $this->attr('allowedDevices')->arr();
        $this->attr('sessions')->arr();
        $this->attr('blocked')->boolean()->setDefaultValue(false);
        $this->attr('confirmed')->boolean()->setDefaultValue(false);
        $this->attr('confirmationToken')->char();
        $this->attr('lastLogin')->integer();
        $this->attr('forgotPasswordToken')->char();
    }
}